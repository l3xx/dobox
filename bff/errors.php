<?php

class Errors
{                                     
    protected $aErrors = array();
    protected $lang    = array();                
    protected $isSuccessfull = false;
    protected $sm      = null; 
    
    protected $ob_level;                   
    protected $_autohide = true;
    
    const SUCCESS       = 1;
    const SUCCESSFULL   = 1;
    const IMPOSSIBLE    = 4;
    const UNKNOWNRECORD = 402;
    const ACCESSDENIED  = 403;
    const WRONGCAPTCHA  = 999;
                                               
    function __construct()
    {
        global $oSm;
        
        $this->ob_level = ob_get_level();   
        if(isset($oSm)) {
            $this->sm = &$oSm;
        }
    }       

    static protected $oInstance=null;
    static public function i( &$lang = array() ) {
        if (isset(self::$oInstance) and (self::$oInstance instanceof self)) {
        } else {
            self::$oInstance = new self();
            set_error_handler( array(self::$oInstance, 'triggerErrorHandler'), E_ALL );
        }
        if(!empty($lang)) {
            self::$oInstance->setLang($lang);
        }
        return self::$oInstance;
    }
    
    function setLang(&$langAppend)
    {   
        $this->lang = array_merge($this->lang, $langAppend);
        static $assigned = false;
        if(!$assigned && !empty($this->sm)) {
            $this->sm->assign_by_ref('aLang', $this->lang);
            $assigned = true;
        }
    }
                
    function init()
    {
        $errno = strip_tags( Func::GETPOST('errno') );
        if($errno) {
            $this->set($errno);
            $this->sm->assign('errno', $errno);
        }
    }
    
    function no()
    {
        return ( sizeof($this->aErrors) == 0 );
    }
    
    function autohide($hide = null)
    {
        if(is_null($hide)) {
            return $this->_autohide;
        } else {
            $this->_autohide = $hide;
        }
    }
    
    /**
    * Сохраняем сообщение об ошибке
    * @return Errors объект
    */
    function set($errCodeName, $sErrorKey=null, $bSuccessfull=false, $sParam1=null, $sParam2=null, $sParam3=null )
    {
        if($errCodeName == 1) {
            $this->sm->assign('errno', 1);
        }
        if($bSuccessfull)
            $this->isSuccessfull = true;

        if(is_string($errCodeName) && mb_strpos($errCodeName, ':')!==FALSE)
        {
            list($type, $param) = explode(':', $errCodeName);
            if(isset($this->lang['err_'.$type])) {
                $sMessage = $this->lang['err_'.$type];
                $sParam1 = '<b>'.mb_strtolower( $this->getMessage($param) ).'</b>';
            } else {
                $sMessage = $this->getMessage($errCodeName);
            }
        } else {
            $sMessage = $this->getMessage($errCodeName);   
        }
        
        $sMessage = str_replace(array('##$1##', '##$2##', '##$3##'), array($sParam1, $sParam2, $sParam3), $sMessage);
        
        if(isset($sErrorKey))
            $this->aErrors[$sErrorKey] = array('successfull'=>$bSuccessfull, 'errno'=>$errCodeName, 'msg'=>$sMessage);
        else
            $this->aErrors[] = array('successfull'=>$bSuccessfull, 'errno'=>$errCodeName, 'msg'=>$sMessage);
        
        return $this;
    }
    
    function get($bOnlyMessages = false)
    {       
        if($bOnlyMessages) {
            if(empty($this->aErrors)) return array();
            $res = array();
            foreach($this->aErrors as $err) {
                $res[] = $err['msg'];
            }
            return $res;      
        }               
        return $this->aErrors;
    }
    
    function show($tpl = 'errors.tpl')
    {
        $this->sm->_tpl_vars['errno'] = '';
        if(sizeof($this->aErrors) == 0)
            $this->init();

        $_POST['errno'] = $_GET['errno'] = '';         
        $this->sm->template_dir = TPL_PATH;
        $this->sm->assign_by_ref('errAutohide',   $this->autohide());    
        $this->sm->assign_by_ref('aErrors',       $this->aErrors);
        $this->sm->assign_by_ref('isSuccessfull', $this->isSuccessfull);
        return $this->sm->fetch($tpl, '', '');
    }
                              
    function assign()
    {
        if(count($this->aErrors) == 0)
            $this->init();
            
        $this->sm->assign('errAutohide', $this->autohide());
        $this->sm->assign('aErrors', $this->aErrors);
    }
    
    function getMessage($nErrorCodeName)
    {
        switch($nErrorCodeName)
        {
            case Errors::SUCCESSFULL:   return $this->lang['successfull'];   break;
            case Errors::ACCESSDENIED:  return $this->lang['access_denied']; break;  
            case Errors::WRONGCAPTCHA:  return $this->lang['wrong_captcha']; break;
            case Errors::IMPOSSIBLE:    return $this->lang['impossible'];    break;
            case Errors::UNKNOWNRECORD: return $this->lang['impossible'];    break;
            default:
            {
                if(isset($this->lang[$nErrorCodeName]))
                    return $this->lang[$nErrorCodeName];
                else 
                {
                    if(is_numeric($nErrorCodeName))
                        return false;
                    else { 
                        return $nErrorCodeName;
                    }
                }
            }
        }
    }
    
    static function httpError($errno)
    {         
        include_once PATH_BASE.'errors.php';
    }
 
    function showHttpError($errno, $template = null)
    {   
        if(empty($template))
            $template = PATH_BASE.'tpl/error_http.php';
     
        switch( (integer)$errno )
        {
            case 401:
            {
                header('WWW-Authenticate: Basic realm="'.$_SERVER['HTTP_HOST'].'"');
                header("HTTP/1.1 401 Unauthorized");
                echo $this->showError("401-я ошибка", 
                                         "Вы должны ввести корректный логин и пароль для получения доступа к ресурсу.", 
                                         401,
                                         $template);
            } break;
            case 403:
            {   
                // пользователь не прошел аутентификацию, запрет на доступ (Forbidden). 
                header("HTTP/1.1 403 Forbidden");
                echo $this->showError("Доступ запрещен (403-я ошибка)", 
                                         "Доступ запрещен.",
                                         403, 
                                         $template);
            } break;
            case 404:
            {
                header("HTTP/1.1 404 Not Found");
                echo $this->showError("Страница не найдена", 
                                         'Вы ошиблись адресом.<br />Можете вернуться на <a href="/">главную</a>.<br /><br />
                                          Если вы уверены, что такой страницы нет по нашей вине, пожалуйста, сообщите нам об этом и не забудьте указать адрес, с которого вы сюда попали. 
                                          <br /><br />Наш email: <a href="mailto:'.BFF_EMAIL_SUPPORT.'">'.BFF_EMAIL_SUPPORT.'</a>', 
                                          404,
                                         $template);
            } break;
            case 500:
            {
                header("HTTP/1.1 500 Internal Server Error");
                echo $this->showError("Внутренняя ошибка сервера (500-я ошибка)", 
                                         "Произошла внутренняя ошибка сервера", 
                                         500,
                                         $template);
            } break;
        }
        exit;
    }

    function showSystemError($errno, $template = null, $aParams = array())
    {   
        if(empty($template))
            $template = PATH_BASE.'tpl/error_http.php';
     
        echo '';
        exit;
    }
    
    function show404()
    {   
        $this->showHttpError(404);
    }

    function showError($title, $message, $type, $template = '')
    {   
        $site_url = 'http://'.$_SERVER['HTTP_HOST'];  

        if(empty($template))
            $template = PATH_BASE.'tpl/error_http.php';
           
        if (ob_get_level() > $this->ob_level + 1)
            ob_end_flush();    

        ob_start();
        include($template);
        return ob_get_clean();
    }   

    function showSuccess($title, $message, $type = 0, $template = '')
    {   
        $site_url = 'http://'.$_SERVER['HTTP_HOST'];  

        if(empty($template))
            $template = PATH_BASE.'tpl/success.php';

        if (ob_get_level() > $this->ob_level + 1)
            ob_end_flush();    

        ob_start();
        config::set('title', $title);
        include($template);
        return ob_get_clean();
    }    
    
    function triggerErrorHandler($nErrorType, $sMessage, $sErrorFile, $sErrorLine)
    {
        switch ($nErrorType) 
        {
            case E_USER_ERROR: //256
            case E_STRICT: //2048
            case E_ERROR: //2048
                $this->aErrors[] = array('successfull'=>false, 'errno'=>'system', 'msg'=>$sMessage.'<br />'.$sErrorFile.' ['.$sErrorLine.']');
                //error_log("$sMessage > $sErrorFile [$sErrorLine]");
            break;
            case E_USER_WARNING: //512
            case E_USER_NOTICE:  //1024
            case E_NOTICE:  //8  
            case E_WARNING: //2
                //if(FORDEV)             
                    $this->aErrors[] = array('successfull'=>false, 'errno'=>'system', 'msg'=>$sMessage.'<br />'.$sErrorFile.' ['.$sErrorLine.']');
                //error_log("$sMessage > $sErrorFile [$sErrorLine]");
            break;
        }
    }
    
    function setUploadError( $nUploadErrorCode, $sErrorKey=null, $bSuccessfull=false, $sParam1=null, $sParam2=null, $sParam3=null )
    {
        return $this->set( $this->getUploadErrorMessage($nUploadErrorCode), $sErrorKey, $bSuccessfull, $sParam1, $sParam2, $sParam3 );
    }
    
    function getUploadErrorMessage($nErrCode)
    {
        $sMessage = '';
        switch($nErrCode)
        {   
            case BFF_UPLOADERROR_UPLOADERR:{ $sMessage = 'Ошибка при загрузке файла';              } break;
            case BFF_UPLOADERROR_WRONGSIZE:{ $sMessage = 'Некорректный размер файла';              } break;
            case BFF_UPLOADERROR_MAXSIZE:  { $sMessage = 'Превышен максимально допустимый размер файла'; } break;
            case BFF_UPLOADERROR_DISKQUOTA:{ $sMessage = 'Превышена квота вложений';               } break;
            case BFF_UPLOADERROR_WRONGTYPE:{ $sMessage = 'Запрещенный тип файла';                  } break;
            case BFF_UPLOADERROR_WRONGNAME:{ $sMessage = 'Некорректное имя файла';                 } break;
            case BFF_UPLOADERROR_EXISTS:   { $sMessage = 'Файл с указанным именем уже существует'; } break;  
            case BFF_UPLOADERROR_MAXDIM:   { $sMessage = 'Изображение слишком большое по ширине/высоте, загрузите изображение меньшего масштаба'; } break;
        }
        return $sMessage;
    }
    
}          
