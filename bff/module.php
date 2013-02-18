<?php

abstract class Module extends Component
{
    var $module_dir     = '';
    var $module_dir_tpl = '';
    var $module_name     = '';  
    var $module_lang     = array();

    private $_c  = array();
    
    public function init()
    {
        parent::init();
                
        $this->module_name = mb_strtolower( get_class($this) );
        $this->module_dir_tpl = $this->module_dir.'tpl'.DIRECTORY_SEPARATOR.LANG_DEFAULT;  

        //include global language file
        include_once PATH_BASE.'lang'.DIRECTORY_SEPARATOR.LANG_DEFAULT.'.inc.php';     

        //include module language file
        if(file_exists($this->module_dir.'lang'.DIRECTORY_SEPARATOR.LANG_DEFAULT.'.inc.php'))  
        {
            include_once $this->module_dir.'lang'.DIRECTORY_SEPARATOR.LANG_DEFAULT.'.inc.php';
            if(isset($lang)) $this->module_lang = &$lang;
            
            $this->errors->setLang( $this->module_lang );    
        }
    }
        
    /**
     * Ставим хук на вызов неизвестного метода
     * @param string [component|module]_[method]
     * @param array $aArgs
     * @return unknown
     */
    public function __call($sName, $aArgs = array()) 
    {
        //Ищем среди прикрепленный компонентов
        if($this->_c!==null)
        {
            $aArgsRef = array();
            foreach($aArgs as $key=>$v) {
                $aArgsRef[] = &$aArgs[$key];
            }
            
            //$sName == [component name]_[method name]
            $aName = explode("_",$sName,2);
            if(sizeof($aName)==2) {
                $sComponent = mb_strtolower($aName[0]);
                $sMethod = $aName[1];
                if(!empty($sMethod) && isset($this->_c[$sComponent])) {
                    
                    if(method_exists($this->_c[$sComponent], $sMethod))
                    {
                        return call_user_func_array(array($this->_c[$sComponent] ,$sMethod), $aArgsRef);
                    }
                    Errors::i()->set("Компонент не имеет требуемого метода <b>$sComponent".'->'.$sMethod.'()</b>')->autohide(false);
                    return null;
                }
            }

            //$sName == [method name]
            foreach($this->_c as $name=>$component) {
                if( method_exists($component, $sName) )
                {
                    return call_user_func_array(array($component, $sName), $aArgsRef);
                }
            }                      
        }          
        return null;
    }
    
    /**
     * Блокируем копирование/клонирование объекта
     */
    protected function __clone() {           
    }

    /**
     * Возвращает объект компонента, 'asa' означает 'as a'.
     * @param string название компонента
     * @return объект Component, или null если компонент не был прикреплен
     */
    public function asa($sName)
    {
        return isset($this->_c[$sName]) ? $this->_c[$sName] : null;
    }

    /**
     * Прикрепляем список компонентов.
     * Структура передаваемых компонентов:
     * 'имя компонента' => array('настройки')
     * @param array массив, прикрепляемых к модулю компонентов
     */
    public function attachComponents($aComponents)
    {
        foreach($aComponents as $name=>$component)
            $this->attachComponent($name, $component);
    }

    /**
     * Открепляем все компоненты.
     */
    public function detachComponents()
    {
        if($this->_c!==null)
        {
            foreach($this->_c as $name=>$component)
                $this->detachComponent($name);
            $this->_c = null;
        }
    }

    /**
     * Прикрепляем компонент к модулю.
     * @param string название компонента.
     * @param mixed конфигурация компонента. Передается первым параметром 
     * в {@link bff::createComponent} при создании обекта компонента.
     * @return Component объект
     */
    public function attachComponent($name, $component)
    {
        if($component instanceof Component) {
            $component->init();
            return $this->_c[$name] = $component;
        } else {
            return null;
        }
    }

    /**
     * Открепляем компонент от модуля.
     * @param string название компонента 
     * @return Component объект. Null если компонент не был прикреплен.
     */
    public function detachComponent($name)
    {
        if(isset($this->_c[$name]))
        {                                    
            $component = $this->_c[$name];
            unset($this->_c[$name]);
            return $component;
        }
    }
    
    public function shutdown() 
    {
        //возвращаем настройки smarty
        $this->setupSmarty(TPL_PATH, bff::$class); 
    }
    
    private function setupSmarty($sTplDirectory, $sClassName) 
    {
        if($this->sm) {                                                                       
            $this->sm->template_dir = $sTplDirectory;
            $this->sm->assign('class', $sClassName);
        }
    }
        
    function tplAssign($tpl_var, $value = null)
    {
        $this->sm->assign($tpl_var, $value);
    }

    function tplAssigned($tpl_var, &$return = array())
    {
        if(is_array($tpl_var)) {
            foreach($tpl_var as $v) {
                $return[$v] = $this->sm->_tpl_vars[$v];
            }
            return $return;
        }
        return ($return[$tpl_var] = $this->sm->_tpl_vars[$tpl_var]);
    }
  
    function tplAssignByRef($tpl_var, &$value)
    {
        $this->sm->assign_by_ref($tpl_var, $value);
    }
    
    function tplFetch($sTemplate, $sTemplateDir = false, $mCacheID = null, $mCompileID = null, $bDisplay = false)
    {
        //устанавливаем путь к шаблонам модуля
        $this->setupSmarty( ($sTemplateDir===false ? $this->module_dir_tpl : $sTemplateDir), $this->module_name );
        
        if(!isset($mCacheID)) $mCacheID = $this->module_name;
        if(!isset($mCompileID)) $mCompileID = $this->module_name; 
        return $this->sm->fetch($sTemplate, $mCacheID, $mCompileID, $bDisplay);
    }

    function tplFetchPHP(&$aData, $sTemplatePHP, $sTemplateDir = false)
    {                 
        ob_start(); 
        include ( ($sTemplateDir === false ? $this->module_dir_tpl : $sTemplateDir) . DIRECTORY_SEPARATOR . $sTemplatePHP );
        return ob_get_clean();
    }
    
    function tplDisplay($sTemplate, $sTemplateDir = false, $mCacheID = null, $mCompileID = null)
    {
        return $this->tplFetch($sTemplate, $sTemplateDir, $mCacheID, $mCompileID, true);
    }

    function showAccessDenied()
    {                  
        if(bff::$isAjax)
            $this->ajaxResponse(Errors::ACCESSDENIED);
        
        return $this->showError(Errors::ACCESSDENIED);
    }
    
    function showImpossible()
    {                  
        if(bff::$isAjax)
            $this->ajaxResponse(Errors::IMPOSSIBLE);
        
        return $this->showError(Errors::IMPOSSIBLE);
    }
    
    function showError($mErrorKey = '')
    {
        if($mErrorKey == Errors::ACCESSDENIED)
            $this->errors->autohide(false);
        
        if(!empty($_SERVER['HTTP_REFERER']) && $this->security->isLogined())
           Func::JSRedirect($_SERVER['HTTP_REFERER'].'&errno='.$mErrorKey); 
                                                      
        $this->errors->set($mErrorKey);
        return '';
    }
    
    function showForbidden($sDescription = '', $sTitle= '')
    {
        $this->tplAssign(array('sForbiddenTitle'=>$sTitle,
                               'sForbiddenDescription'=>$sDescription,));
        
        return $this->tplFetch('forbidden.tpl', TPL_PATH, '', '');
    }
    
    function adminRedirect($nError=1, $sEv = 'listing', $sModule=null, $bUseJS = false)
    {   
        if(bff::$isAjax)
            $this->ajaxResponse($nError);
        
        if($nError == Errors::SUCCESSFULL && !$this->errors->no())
            return;

        if($sEv === true) 
            $sEv = bff::$event;
            
        $this->redirect('index.php?s='.(empty($sModule)?$this->module_name:$sModule).'&ev='.$sEv.(!empty($nError)?'&errno='.$nError:''), $bUseJS);
    }
    
    function redirect( $url, $bUseJS = false )
    {
        if (headers_sent() || $bUseJS) echo "<script type=\"text/javascript\">location='$url';</script>";
        else header('location: '.$url);
        exit();
    }
    
    function adminCreateLink($sEvent='listing', $sModule=null)
    {
        return 'index.php?s='.(empty($sModule)?$this->module_name:$sModule).'&ev='.$sEvent;
    }
    
    function adminCustomCenterArea($custom = true)
    {
        config::set('tpl_custom_center_area', $custom);
    }
    
    function adminIsListing($isListing = true)
    {
        config::set('tpl_is_listing', $isListing);
    }
        
    function haveAccessTo($sMethod, $sModule = '')
    {
        if($sModule == '')
            $sModule = $this->module_name;
            
        return $this->security->haveAccessToModuleToMethod($sModule, $sMethod);
    }
    
    /**
    * @param mixed response data
    * @param mixed response type; 0|false - raw echo, 1|true - json echo, 2 - json echo + errors
    * @desc ajax ответ: если data=0,1,2 - это не ключ ошибки, а просто краткий ответ
    */
    function ajaxResponse($mData, $mFormat = 2)
    {
        if($mFormat === 2)
        {                
            $aResponse = array(
                'data'   => $mData,
                'errors' => array()
                );
            if($this->errors->no()) {
                if(is_int($mData) && $mData>2)
                {
                    $this->errors->set($mData);              
                    $aResponse['errors'] = $this->errors->get(true);
                    $aResponse['data'] = '';
                }
            } else {
                $aResponse['errors'] = $this->errors->get(true);
            }
            echo func::php2js($aResponse);
            exit;
        }
        elseif($mFormat === TRUE || $mFormat === 1){
            echo func::php2js($mData); 
            exit;
        } else {       
            echo $mData;
            exit;
        }
    } 
    
    function makeTUID($mValue)
    {
        $key = (!isset($this->securityKey) ? $this->securityKey : '');
        return md5("{$key}%^&*9Th_65{$this->module_name}87*(gUD0teQ*&^{$mValue}{$key}");
    }
    
    function checkTUID($sTUID, $mValue)
    {
        return ( $this->makeTUID($mValue) == $sTUID ); 
    }

    function generatePagenation($nTotal, $nLimit, $href, &$sqlLimit, $tpl_name = 'pagenation.tpl', 
                                $pageParamName = 'page', $bOnlyPageNumbers = false, &$result = array() )
    {
        $perPage = $nLimit;
        $pagesPerStep = 20;
        $nCurrentPage = $this->input->getpost($pageParamName, TYPE_UINT);

        $sqlLimit = '';
        $pagenation = array();
        $result['offset'] = 0; 
        
        if($nTotal > 0)
        {
            if(!$nCurrentPage) $nCurrentPage = 1;              
            $numPages = ceil($nTotal/$perPage);

            if($nCurrentPage>$pagesPerStep)
            {
                $stepLastPage = $pagesPerStep;
                while($stepLastPage<$nCurrentPage)
                    $stepLastPage+=$pagesPerStep;

                $stepFirstPage = ($stepLastPage-$pagesPerStep) + 1;
            }
            else
            {
                $stepFirstPage = 1;
                $stepLastPage  = $pagesPerStep;                    
            }

            for($i=$stepFirstPage; $i<$stepLastPage+1; $i++)
            {
                if($i<=$numPages)
                {
                    $pagenation[$i]['page']   = $i;
                    $pagenation[$i]['active'] = ($i == $nCurrentPage?'1':'0');
                    $pagenation[$i]['link']   = str_replace('{pageId}', ($bOnlyPageNumbers ? $i : "$pageParamName=".$i), $href);
                }
            }
            
            //pages prev, next
            $sNextPages = $sPrevPages = '';
            if($nCurrentPage>$pagesPerStep)
            {
               $sPrevPages = str_replace('{pageId}', ($bOnlyPageNumbers ? ($stepFirstPage-1) : "$pageParamName=".($stepFirstPage-1)), $href);
            }
            if($stepLastPage<$numPages)
            {
                $sNextPages = str_replace('{pageId}', ($bOnlyPageNumbers ? ($stepLastPage+1) : "$pageParamName=".($stepLastPage+1)), $href);
            }
            
            //page prev, next
            $sNextPage = $sPrevPage = '';
            if($nCurrentPage>1)
            {
               $sPrevPage = str_replace('{pageId}', ($bOnlyPageNumbers ? ($nCurrentPage-1) : "$pageParamName=".($nCurrentPage-1)), $href);
            }
            if($nCurrentPage<$numPages)
            {
                $sNextPage = str_replace('{pageId}', ($bOnlyPageNumbers ? ($nCurrentPage+1) : "$pageParamName=".($nCurrentPage+1)), $href);  
            }
            
            if($perPage<$nTotal)
            {
                $result['offset'] = (($nCurrentPage-1)*$perPage);
                $result['last']   = $nTotal - (($numPages-1)*$perPage);
                $sqlLimit = $this->db->prepareLimit($result['offset'], $perPage);
            }

            $this->tplAssign(array('pgNext'=>$sNextPage,
                                   'pgPrev'=>$sPrevPage,
                                   'pgsNext'=>$sNextPages,
                                   'pgsPrev'=>$sPrevPages,));
            
            $toIndex = $nCurrentPage*$perPage;
            if($toIndex>$nTotal) $toIndex = $nTotal;                
            $sFromTo = $nCurrentPage*$perPage-($perPage-1).' - '.$toIndex;

            $this->tplAssign(array('pgFromTo'=>$sFromTo,
                                   'pgTotalCount'=>$nTotal,));
        }
        else
        {
            $this->tplAssign(array('pgNext'=>'',
                                   'pgPrev'=>'',
                                   'pgFromTo'=>0,
                                   'pgTotalCount'=>0,));
        }

        //smart limit-offset
        if(!empty($result['order_dir'])) 
        {                      
            $nOffset = &$result['offset'];
            if($nTotal && $nOffset >= $nTotal/2)
            {
                /*
                * Например общее кол-во записей 1000 (total)
                * Если offset 1-499, тогда $sqlLimit = LIMIT offset,perpage
                * Если offset 500-1000, тогда  $sqlLimit = LIMIT (total-offset-perpage),perpage
                */
                $nOffset = $nTotal - $nOffset;
                if($nOffset >= $nLimit) $nOffset -= $nLimit;
                else{ //last page
                    $nOffset = 0;
                    $nLimit = $result['last']; 
                }
                            
                $sqlLimit = ($result['order_dir'] == 'asc' ? 'desc' : 'asc').' '.$this->db->prepareLimit($nOffset, $nLimit);
            } else {
                $sqlLimit = $result['order_dir'].' '.$this->db->prepareLimit($nOffset, $nLimit);
            }
        }
        
        $this->tplAssign('pagenation', $pagenation);
        
        if($tpl_name===false) return '';
        
        $pagenation_tpl = $this->tplFetch($tpl_name, TPL_PATH);
        $this->tplAssign('pagenation_template', $pagenation_tpl);
        
        return $pagenation_tpl;
    }

    function generatePagenationPrevNext($sQuerySelect, &$aData, $sItemsKey='items', $nLimit = 20)
    {                                                                                         
        if(!empty($sQuerySelect)){     
            $aData['offset'] = Func::GETPOST('offset', false, true);
            if($aData['offset']<=0) $aData['offset'] = 0; 
            $aData[$sItemsKey] = $this->db->select($sQuerySelect.$this->db->prepareLimit($aData['offset'], $nLimit+1));
        }
        
        $aData['prev'] = ( $aData['offset'] ? $aData['offset']-$nLimit : 0);
        if(count($aData[$sItemsKey]) > $nLimit)
        {
            $aData['next'] = $aData['offset'] + $nLimit;
            array_pop($aData[$sItemsKey]);
        } else {
            $aData['next'] = 0;
        }
        
        $aData['pgn'] = ''
            .(($aData['prev'] || $aData['offset']>0)?'<a href="?page='.$aData['prev'].'" onclick="pgn.prev('.$aData['prev'].'); return false;">&larr; назад</a>':'<span class="desc">&larr; назад</span>')
            .'<span class="desc">&nbsp;&nbsp;&nbsp;&nbsp;</span>'
            .($aData['next'] ? '<a href="?page='.$aData['next'].'" onclick="pgn.next('.$aData['next'].'); return false;">вперед &rarr;</a>':'<span class="desc">вперед &rarr;</span>')
            ; 
    }
    
    function prepareOrder( &$orderBy, &$orderDirection, $defaultOrder='', $allowedOrders = array(), $orderParamName='order')
    {
        $order = Func::GETPOST($orderParamName);
       
        if(empty($order))
            $order = $defaultOrder;

        if(!empty($order))
        {
            @list($orderBy, $orderDirection) = explode(',', $order);   

            if(!isset($orderDirection)) $orderDirection = 'asc';
            
            if( !empty($allowedOrders) && (!isset($allowedOrders[$orderBy])) )
                @list($orderBy, $orderDirection) = explode(',', $defaultOrder);
 
            $orderDirectionNeeded = ($orderDirection == 'asc' ? 'desc' : 'asc');

            $this->tplAssign(array('order_by'=>$orderBy,
                                   'order_dir'=>$orderDirection,
                                   'order_dir_needed'=>$orderDirectionNeeded,));
            return true;
        }
        return false;         
    }
    
    function preparePerpage(&$nPerpage, $aValues = array(5,10,15), $sParamName = 'perpage', $nExpireDays = 7)
    {
        $nPerpage = $this->input->getpost($sParamName, TYPE_UINT);
        $sCookieKey = BFF_COOKIE_PREFIX.$this->module_name.'_'.$sParamName;
        
        $nCurCookie = func::getCOOKIE($sCookieKey, 0);
        $this->input->clean($nCurCookie, TYPE_UINT);
        
        if(!$nPerpage) {
            $nPerpage = $nCurCookie;
            
            if(!in_array($nPerpage, $aValues))
                $nPerpage = current($aValues);
        }
        else {
            if(!in_array($nPerpage, $aValues))
                $nPerpage = current($aValues);
            
            if( $nCurCookie!=$nPerpage )
                func::setCOOKIE( $sCookieKey, $nPerpage, (isset($nExpireDays) ? $nExpireDays : 7) ); //default: one week
        }
        
        array_walk($aValues, create_function('&$item,$key,$cur', '$item = "<option value=\"$item\" ".($item==$cur?"selected=\"selected\"":"").">$item</option>";'), $nPerpage);
        return join($aValues);
    }
    
    function includeJS($mInclude, $nVersion = false, $fromCore = null)
    {    
        return bff::includeJS($mInclude, $nVersion, $fromCore);
    }

    function includeCSS($mInclude, $bUseThemePath = true)
    {                                                                                                       
        bff::includeCSS($mInclude, $bUseThemePath);
    }
}
