<?php

define('BFF_UPLOADERROR_NOERROR',   0);  //Нет ошибок
define('BFF_UPLOADERROR_UPLOADERR', 1);  //Ошибка при загрузке файла
define('BFF_UPLOADERROR_WRONGSIZE', 2);  //Некорректный размер файла
define('BFF_UPLOADERROR_MAXSIZE',   3);  //Превышен максимально допустимый размер файла
define('BFF_UPLOADERROR_DISKQUOTA', 4);  //Превышена квота вложений
define('BFF_UPLOADERROR_WRONGTYPE', 5);  //Запрещенный тип файла
define('BFF_UPLOADERROR_WRONGNAME', 6);  //Некорректное имя файла
define('BFF_UPLOADERROR_EXISTS',    7);  //Файл с указанным именем уже существует 
define('BFF_UPLOADERROR_MAXDIM',    8);  //Превышен максимально допустимый масштаб файла    

define('BFF_DEFAULT_IMG', 'default.gif' ); 

//languages
define('LANG_VAR',       'lng');
define('LANG_DEFAULT',   'def'); 

//users                                                       
define('USERS_GROUPS_SUPERADMIN', 'x71');
define('USERS_GROUPS_MODERATOR',  'c60');
define('USERS_GROUPS_MEMBER',     'z24');

//pages
define('PAGES_PATH', PATH_BASE.'files/pages/');
define('PAGES_EXTENSION', '.html');
                       
require 'include.php'; 

class bffBase 
{   
    static public $isFrontend = false;
    static public $isPost = false;
    static public $isAjax = false; 
    static public $class = '';
    static public $event = '';
    static public $includesJS = array();
    static public $includesCSS = array();

    protected $_m = array();
    
    /**
     * Делает возможным только один экземпляр этого класса
     * @return bffBase объект
     */ 
    static protected $oInstance=null;
    static public function i() {
        if (isset(self::$oInstance) and (self::$oInstance instanceof self)) {
            return self::$oInstance;
        } else {
            self::$oInstance = new self();
            return self::$oInstance;
        }
    }

    /**
    * @return bffBase object
    */
    function init($isFrontend = true)
    {
        global $oDb, $oSm, $oSecurity;
        
        self::$isFrontend = $isFrontend;
        self::$isAjax     = func::isAjaxRequest();
        self::$isPost     = func::isPostMethod();
        
        if($isFrontend) {
            define('TPL_PATH', PATH_BASE.'tpl/main/');  
            $oSm->template_dir = TPL_PATH;             
        } else {
            define('TPL_PATH', PATH_BASE.'tpl/main/admin/');  
            define('THEME_URL', SITEURL.'/styles/default');  //default admin theme!
            $oSm->template_dir = TPL_PATH; 
        }
        
        spl_autoload_register(array('bffBase', 'autoload'));
        
        $oSecurity->checkExpired();
                      
        if(!defined('THEME_URL'))
            define('THEME_URL', SITEURL.'/styles/'.THEMES_DEFAULTTHEME_KEY);  
        $oSm->assign('theme_url', THEME_URL);  
       
        $oSm->assign('class', ( self::$class = substr( strtolower( func::GETPOST('s') ), 0, 30) ) );
        $oSm->assign('event', ( self::$event = substr( strtolower( func::GETPOST('ev') ), 0, 30) ) );
        $oSm->assign_by_ref('tplJSIncludes', self::$includesJS);
        $oSm->assign_by_ref('tplCSSIncludes', self::$includesCSS);

        bff::initApp($isFrontend);
        
        return $this;
    }
    
    /**
     * Блокируем копирование/клонирование объекта
     */
    protected function __clone() 
    {
        
    }
    
    function __call($sName, $aArgs) 
    {
        return $this->callModule($sName, $aArgs);
    }

    /**
     * Вызывает метод нужного модуля
     * @param string имя модуля
     * @param array аргументы                                    
     * @return unknown
     */
    public function callModule($sName, $aArgs = array()) 
    {
        list($oModule,$sModuleName,$sMethod,$bFirstRun) = $this->GetModule($sName, false);

        $aArgsRef = array();
        foreach($aArgs as $key=>$v) {
            $aArgsRef[] = &$aArgs[$key];
        }
        
        if(!method_exists($oModule,$sMethod)) {
            $result = $oModule->$sMethod($aArgsRef); //пытаемся вызвать метод прикрепленного компонента
            if($result===null) {
                Errors::i()->set("Модуль не имеет требуемого метода <b>$sModuleName".'->'.$sMethod.'()</b>')->autohide(false);
                $result = '';
            }
        } else {         
            $result = call_user_func_array(array($oModule,$sMethod), $aArgsRef);
        }
        $oModule->shutdown(); 

        return $result;
    }
    
    /**
     * Возвращает объект модуля, имя модуля и имя вызванного метода
     * @param string название модуля
     * @param boolean возвращать только объект модуля, либо array(объект, название, ...)
     * @return array (object Module, string название модуля)
     */
    public function GetModule($sName, $bOnly = true) 
    {
        $aName = explode("_",$sName,2);
        if(sizeof($aName)==2) {
            $sModuleName = mb_strtolower($aName[0]);
            $sMethod = $aName[1];
        } else {
            $sModuleName = mb_strtolower($sName);
            $sMethod = '';
        }
        
        $bFirstRun = true;
        if(isset($this->_m[$sModuleName])) {
            $oModule = $this->_m[$sModuleName];
            $bFirstRun = false;
        } else {
//            if(isset($_GET['xx'])) {
//                echo print_r( array_keys($this->_m), true), '<br/>';
//            }
            $oModule = $this->LoadModule($sModuleName, true);
        }
        
        return ( $bOnly ? $oModule : array($oModule,$sModuleName,$sMethod,$bFirstRun) );
    }
    
    /**
     * Выполняет загрузку модуля по его названию
     * @param  string $sModuleName
     * @param  bool $bInit - инициализировать модуль или нет
     * @return Module
     */
    private function LoadModule($sModule, $bInit=false) 
    {
        $sPath = PATH_MODULES;

        if(!file_exists($sPath.$sModule.DIRECTORY_SEPARATOR.$sModule.(!bffBase::$isFrontend?'.adm':'').'.class.php')) 
        { 
            $sPath = PATH_CORE.'modules'.DIRECTORY_SEPARATOR;
            if(!file_exists($sPath.$sModule.DIRECTORY_SEPARATOR.$sModule.(!bffBase::$isFrontend?'.adm':'').'.class.php')) {
                throw new Exception("Не удалось найти требуемый модуль <b>$sModuleName</b>");
            }             
        }
                
        include $sPath.$sModule.DIRECTORY_SEPARATOR.$sModule.'.bl.class.php';
        require $sPath.$sModule.DIRECTORY_SEPARATOR.$sModule.(!bffBase::$isFrontend?'.adm':'').'.class.php';
        
        //Создаем объект модуля
        $oModule = new $sModule( );
        if ($bInit) {
            $oModule->module_dir = ($sPath.$sModule.'/');
            $oModule->init();
        }
        $this->_m[$sModule] = $oModule;

        return $oModule;
    }
    
    public function createComponent($component)
    {
        //
    }

    static function sessionStart($sKey = '')
    {   
        global $oSecurity;

        //make friendly session with subdomains
        $aReplace = array('http://www.', 'http://admin.', 'http://');
        $sDomainBase = str_replace($aReplace, '', SITEURL);
        if($sDomainBase!='localhost')
            ini_set("session.cookie_domain", ".".$sDomainBase);
        
        require (PATH_CORE.'security.php');
        $oSecurity = new Security($sKey);   
    }

    static function includeJS($mInclude, $nVersion = false, $fromCore = null)
    {                                                                                                       
        if(!empty($mInclude)) 
        {
            if(!isset($fromCore)) {
                $fromCore = !self::$isFrontend;
            }
                
            $root = ''; $ext = '.js';
            if($fromCore) 
            {
                $root .= SITEURL.'/bff/js/';
                if(!is_array($mInclude))
                    $mInclude = array($mInclude);
                
                $mIncludeCopy = $mInclude; $mInclude = array();
                foreach($mIncludeCopy as $j) 
                {
                    switch($j) {
                        case 'fancybox': { 
                            self::includeCSS($root.'fancybox/fancybox', false); 
                            $mInclude[] = 'fancybox/fancybox'; 
                        } break;
                        case 'datepicker': { 
                            self::includeCSS('jquery.datepicker'); 
                            $mInclude[] = 'jquery.datepicker'; 
                        } break;
                        case 'dynprops': {                                   
                            $mInclude[] = 'dynprops/dynprops'; 
                        } break;
                        case 'wysiwyg': { 
                            self::includeCSS($root.'wysiwyg/style', false); 
                            $mInclude[] = 'wysiwyg/wysiwyg'; 
                        } break;
                        case 'publicator': {     
                            self::includeCSS($root.'publicator/style', false);
                            $mInclude[] = 'publicator/publicator'; 
                            $mInclude[] = 'swfupload/swfupload';
                            $mInclude[] = 'tablednd'; 
                        } break;
                        default: {
                            $mInclude[] = $j;
                        } break;
                    }
                }                
            }
            
            if(is_array($mInclude)) {
                foreach($mInclude as $j) {
                    if(!$fromCore) { 
                        $root = '';
                       if(strpos($j, 'http://')!==false) { $ext = ''; }  
                       else { $root .= SITEURL.'/js/'; $ext = '.js'; } 
                    }
                    if(!isset(self::$includesJS[$j]))          
                        self::$includesJS[$j] = $root.$j.$ext;
                }    
            } else {
                if(!$fromCore) {
                   $root = '';
                   if(strpos($mInclude, 'http://')!==false) { $ext = ''; }  
                   else { $root .= SITEURL.'/js/'; $ext = '.js'; } 
                }                
                if(!isset(self::$includesJS[$mInclude]))          
                    self::$includesJS[$mInclude] = $root.$mInclude.$ext;
            }                     
        }
    } 

    static function includeCSS($mInclude, $bUseThemePath = true)
    {                                                                                                       
        if(!empty($mInclude)) {
            if(is_array($mInclude)) {
                foreach($mInclude as $c){
                    if(!isset(self::$includesCSS[$c]))          
                        self::$includesCSS[$c] = ($bUseThemePath? THEME_URL.'/css/'.$c.'.css' : $c.'.css');
                }    
            } else {
                if(!isset(self::$includesCSS[$mInclude]))          
                    self::$includesCSS[$mInclude] = ($bUseThemePath? THEME_URL.'/css/'.$mInclude.'.css' : $mInclude.'.css');
            }
                                  
        }
    }
    
    static function sendMailTemplate($tplVars, $tplName, $to, $subject=false, $from='', $fromName='')
    {
        try {
            //if(BFF_LOCALHOST)
            //    return true;    
            if(empty($fromName) && defined('BFF_EMAIL_FROM_NAME')) {
                $fromName = BFF_EMAIL_FROM_NAME;
            }
            if(empty($from)) {
                $from = config::get('mail_noreply', BFF_EMAIL_NOREPLY);
            }
                
            $aTplData = self::i()->Sendmail_getMailTemplate($tplName, $tplVars);
            return self::i()->Sendmail_sendMail($to, ($subject!==false?$subject:$aTplData['subject']), $aTplData['body'], $from, $fromName);
        } catch(Exception $e){
            Errors::i()->set($e->getMessage());
        }
    }
    
    static function sendMail($to, $subject, $body, $from='', $fromName='')
    {
        if(empty($fromName) && defined('BFF_EMAIL_FROM_NAME')) {
            $fromName = BFF_EMAIL_FROM_NAME;
        }
        if(empty($from)) {
            $from = config::get('mail_noreply', BFF_EMAIL_NOREPLY);
        }
            
        return self::i()->Sendmail_sendMail($to, $subject, $body, $from, $fromName);
    }
    
    static function getCurrentTheme($bReturnKey = true)
    {
        return ( $bReturnKey ? THEMES_DEFAULTTHEME_KEY : THEMES_DEFAULTTHEME_ID );
    }
    
    static function buildPath($sFolder, $sType = false)
    {
        $sep = DIRECTORY_SEPARATOR;  
        if($sType === false) {
            return PATH_BASE.'files'.$sep.$sFolder.$sep;
        }
        switch($sType)
        {
            case 'images': return PATH_BASE.'files'.$sep.'images'.$sep.$sFolder.$sep; break;
        }
    }
    
    static function buildUrl($sPart, $sType = false)
    {
        switch($sType)
        {
            //http://img.SITEHOST/part/ => http://SITEHOST/files/images/part/'
            case 'images': return 'http://'.SITEHOST.'/files/images/'.$sPart.'/'; break;
        }
        return '';
    }
    
    /**
     * Autoload
     */
    public static function autoload($className)
    {                        
//        if(strpos($className, 'BL_')!==false) {
//            $module = mb_strtolower( substr($className, 3) );   
//            include (PATH_MODULES."$module/$module.bl.class.php");
//            return class_exists($className, false) || interface_exists($className,false);
//        }
//        else 
        {
            if(isset(self::$_classes[$className])) {
                include (PATH_CORE.'/'.self::$_classes[$className]);
                return class_exists($className, false) || interface_exists($className,false);
            }  
        }
        return true;
    }
    
    private static $_classes = array(
        'tpl'          => 'tpl.php',
        'CDir'         => 'dir.php',
        'CLinksParser' => 'links.parser.php',
        'CTextParser'  => 'external/text.parser.php', 
        //img 
        'ImageConverter' => 'external/Image2.php',
        'thumbnail'    => 'img/thumbnail.php',
        'CThumbnail'   => 'img/thumbnail2.php',
        'CAvatar'      => 'img/avatar.php',
        'CPreview'     => 'img/preview.php',
        'CAttachment'  => 'attachment.php',  
        'Upload'       => 'upload.php',
        
        'CMail'        => 'external/class.phpmailer.ex.php',
        'CCaptchaProtection' => 'captcha/captcha.protection.php',
        //base modules
        'SendmailBase'     => 'modules/sendmail/sendmail.bl.class.php',
        'UsersBase'        => 'modules/users/users.bl.class.php',
        
        //database
        'dbDynprops'       => 'database/dynprops/dynprops.php',
        'Publicator'       => 'database/publicator/publicator.php',
        'dbNestedSetsTree' => 'database/nestedsets/nestedsets.php',
        'dbImagesField'    => 'database/imagesfield.php',
    );    
    
}


class config 
{
    
    static public $data = array();
    static public $cacheGroup = 'config';
    
    /**
     * Инициализация кешера настроек
     * @return Cache объект
     */
    static private function cache()
    {
        static $cache;
        if(empty($cache)) {
            //$cache = Cache::singleton('apc', true);
            $cache = Cache::singleton('lite', array('cacheDir'=>PATH_BASE.'files/cache/', 'lifeTime'=>null, 'automaticSerialization'=>true) );
            //$cache->setGroup( self::$cacheGroup );
        }                 
        return $cache;
    }
    
    /**
     * Получаем настройки
     * @return array массив с настроками ключ=>значение
     */
    static function load()
    {
        global $oDb;
        
        $cache = self::cache();
        
        if( ($config = $cache->get(self::$cacheGroup)) !== false )
        {   
            $result = $oDb->select('SELECT config_name as n, config_value as v
                    FROM '.TABLE_CONFIG.'
                    WHERE is_dynamic = 1');
            foreach($result as $v) {
                $config[ $v['n'] ] = $v['v'];
            }        
        }
        else
        {
            $config = $cached_config = array();
            $result = $oDb->select('SELECT config_name as n, config_value as v, is_dynamic as d FROM '.TABLE_CONFIG);
            foreach($result as $v)
            {
                if (!$v['d']) {
                    $cached_config[$v['n']] = $v['v'];
                }

                $config[$v['n']] = $v['v'];
            }              
            $cache->set(self::$cacheGroup, $cached_config);
        }   

        self::$data = $config;
    }
    
    /**
     * Сохраняем настройку                                
     * @param string ключ настройки
     * @param mixed значение
     * @param boolean динамическая настройка
     */
    static function save($sConfigName, $mConfigValue, $bIsDynamic = false)
    {
        global $oDb;

        $res = $oDb->execute( 'UPDATE '.TABLE_CONFIG.'
            SET config_value = '.$oDb->str2sql($mConfigValue).'
            WHERE config_name = '.$oDb->str2sql($sConfigName) );

        if (!$res && !isset(self::$data[$sConfigName])) {
            $oDb->execute('INSERT INTO '.TABLE_CONFIG.' (config_name, config_value, is_dynamic)
                VALUES('.$oDb->str2sql($sConfigName).', '.$oDb->str2sql($mConfigValue).', '.($bIsDynamic ? 1 : 0).')');
        }

        self::$data[$sConfigName] = $mConfigValue;

        if (!$bIsDynamic) {
            self::cache()->delete(self::$cacheGroup);
        }
    }
   
    /**
     * Обновляем счетчик, в настройках
     * @param string ключ настройки
     * @param integer инкремент/декремент значение счетчика
     * @param boolean динамическая настройка
     */
    static function saveCount($sConfigName, $nIncrement, $bIsDynamic = false)
    {
        global $oDb;

        switch ($oDb->getDriverName())
        {
            case 'pgsql':
                $sQueryUpdate = 'int4(config_value) + '.(int)$nIncrement;
            break;
            // mysql, mysqli
            default:
                $sQueryUpdate = 'config_value + '.(int)$nIncrement;
            break;
        }

        $res = $oDb->execute('UPDATE '.TABLE_CONFIG.' SET config_value = '.$sQueryUpdate.' WHERE config_name = '.$oDb->str2sql($sConfigName) );
        if(!$res) {
            $oDb->execute('INSERT INTO '.TABLE_CONFIG.' (config_name, config_value, is_dynamic)
                VALUES('.$oDb->str2sql($sConfigName).', '.$oDb->str2sql( ($nIncrement>0 ? $nIncrement : 0) ).', '.($bIsDynamic ? 1 : 0).')');
        }

        if(!$bIsDynamic) {
            self::cache()->delete(self::$cacheGroup);
        }
    }
    
    /**
     * Сохраняем настройку в текущий загруженный конфиг
     * @param string ключ настройки, array массив ключ=>значение
     * @param mixed значение                  
     */
    static function set($sConfigKey, $mConfigValue, $sConfigKeyPrefix = false)
    {
        $prefix = ($sConfigKeyPrefix!==false?$sConfigKeyPrefix:'');
        if(is_array($sConfigKey)) {
            foreach($sConfigKey as $key=>$value) {
                self::$data[$prefix.$key] = $value;
            }
        } else {
            self::$data[$prefix.$sConfigKey] = $mConfigValue;
        }
    }
    
    /**
     * Получаем настройку из текущего загруженного конфига
     * @param string ключ настройки
     * @param mixed значение
     * @param string префикс ключа
     */
    static function get($mConfigKey, $mDefaultValue = false, $sConfigKeyPrefix = false)
    {   
        $prefix = ($sConfigKeyPrefix!==false?$sConfigKeyPrefix:'');                      
        if(is_array($mConfigKey)) {
            $res = array();
            foreach($mConfigKey as $key) {
                if(isset(self::$data[$prefix.$key]))
                  $res[$key] = self::$data[$prefix.$key];
            }
            return $res;
        } else {     
            return (isset(self::$data[$prefix.$mConfigKey]) ? self::$data[$prefix.$mConfigKey] : $mDefaultValue);
        }
    } 

    /**
     * Получаем настройки из текущего загруженного конфига по префиксу ключа
     * @param string префикс ключа настройки
     */
    static function getWithPrefix($sKeyPrefix)
    {
        $res = array(); $prefixLen = strlen($sKeyPrefix);
        foreach(self::$data as $key=>$value) {
            if(strpos($key, $sKeyPrefix) === 0 )
                $res[ substr($key, $prefixLen) ] = $value;
        }
        return $res;
    } 
    
    /**
     * Получаем настройку из текущего загруженного конфига
     * @param string ключ настройки
     * @param mixed значение                  
     */
    static function getAll()
    {
        return self::$data;
    }   
}

function _t($sModule, $sText)
{
    return $sText;
}
