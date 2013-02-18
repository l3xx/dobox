<?php

class CMenu extends Component 
{
    protected $items = array();
    
    protected $curMainTab      = false;
    protected $curMainTabTitle = false;
    protected $curSubTab       = false;
    protected $curSubTabTitle  = false;
    
    const typeModuleMethod = 0;
    const typeLink         = 1;
    const typeSeparator    = 2;
    
    function __construct($aMainTabs = false)
    {               
        parent::init();
          
        if(!empty($aMainTabs))
        {
            foreach($aMainTabs as $key=>$title) {
                $this->items[$title] = array(
                   'title'=>$title, 'counter'=>false, 'sub'=>array()
                ); 
            }
        }
    }
    
    function setMainTabParam($sMainTitle, $paramKey, $paramValue)
    {
        $this->items[$sMainTitle][$paramKey] = $paramValue;
    }
    
    /**
    * @param string название основного раздела меню
    * @param string название подраздела меню
    * @param string название модуля, или ссылка (@example http://example.com/page1.html)
    * @param string название метода модуля
    * @param boolean показывать пункт меню по-умолчанию
    * @param integer приоритет, определяет порядок подразделов в пределах раздела
    * @param array дополнительные настройки:
    *   1. rlink => array(title=>'', event=>'', type=>0, link=>'')
    *   2. counter => array()
    *   3. params => string доподнительные параметры ссылки типа typeModuleMethod
    * @param integer тип раздела/подраздела ( self::type.. )
    */
    function assign($sMainTitle, $itemTitle, $class, $event, $isVisible=true, $priority = 999, $aOptions = false, $nItemType=0)
    {
        $rlink = false;
        if(isset($aOptions['rlink'])) {
            $rlink = $aOptions['rlink'];
            
            if(!isset($rlink['title'])) 
                $rlink['title'] = '+ добавить';
            
            $rlink['url'] = (isset($rlink['type']) && $rlink['type'] == self::typeLink ? $rlink['link'] : 
                                    ($nItemType == self::typeLink ? '#' : Module::adminCreateLink($rlink['event'], $class )) );
        }
        
        $this->items[$sMainTitle]['sub'][] = array(
                'title'=>$itemTitle, 
                'class'=>strtolower($class), 
                'event'=>strtolower($event), 
                'eventParams'=>(isset($aOptions['params'])?$aOptions['params']:''), 
                'visible'=>$isVisible, 
                'priority'=>$priority,
                'type'=>$nItemType,
                'counter'=>(isset($aOptions['counter'])?$aOptions['counter']:false),
                'rlink'=>$rlink,
            );
    }
    
    function build($sCollectFunctionName = 'declareadminmenu', $bCheckAccess = false) //declareadminmenu, declareusermenu
    {
        $this->collect($sCollectFunctionName, $bCheckAccess);
        $aTabs = $this->getTabs();
        $this->sm->assign('menuTabs', $aTabs); 
        config::set('tpl_page_title', $this->getPageTitle() );
        
        $first = current($aTabs);
        return $first['url'];
    }
    
    protected function collect($sCollectFunctionName, $bCheckAccess = false)
    {
        global $oSecurity;
        
//        global $oMenu;
//        
//        $cache_lite = new Cache_Lite(array(
//            'cacheDir' => PATH_BASE.'cache/',
//            'caching'  => true,
//            'lifeTime' => 1000,
//            'automaticSerialization' => true
//        ));
//        
//        if( !($oMenu->items = $cache_lite->get('cmenu::getusercollection')) ) 
        {
//            echo '1';
            $sPath = PATH_MODULES;
            $aDirectories = CDir::getDirs($sPath, false, false, false);
            foreach($aDirectories as $module)
            {
                if(file_exists($sPath."$module/m.$module.class.php"))
                {
                    require_once($sPath."$module/m.$module.class.php");
                    if(class_exists('M_'.$module) && method_exists('M_'.$module, $sCollectFunctionName)) {
                        if($bCheckAccess && !$oSecurity->haveAccessToModuleToMethod($module)) {
                            continue;
                        }
                        call_user_func(array('M_'.$module, $sCollectFunctionName));
                    }
                }
            }
            
            //core modules
            $sPath = PATH_CORE.'modules'.DIRECTORY_SEPARATOR;
            $aDirectories = CDir::getDirs($sPath, false, false, false);    
            foreach($aDirectories as $module)
            {        
                if(file_exists($sPath."$module/m.$module.class.php"))
                {
                    require_once($sPath."$module/m.$module.class.php");
                    if(class_exists('M_'.$module) && method_exists('M_'.$module, $sCollectFunctionName))
                        call_user_func(array('M_'.$module, $sCollectFunctionName));
                }
            }               
            
//            $cache_lite->save( $oMenu->items, 'cmenu::getusercollection');
        }
    }       
    
    protected function getCurrentTabs()
    {
        $class = bff::$class;
                                                                     
        if(!empty($class))
        {
            $n = 0;
            foreach($this->items as $mainTitle => $main)
            {           
                foreach($main['sub'] as $i=>$sub)
                {   
                    if( $sub['class'] == $class ) 
                    {   
                        if( $sub['event'] == bff::$event )
                        {                                          
                            $this->curMainTab = $n; 
                            $this->curMainTabTitle = $mainTitle;
                            $this->curSubTab = ($inLink ? -1 : $i);
                            $this->curSubTabTitle = $sub['title'];
                            break 2;
                        } else {    
                            $this->curMainTab = $n;
                            $this->curMainTabTitle = $mainTitle;
                            $this->curSubTab = -1;
                        }
                    } 
                }
                $n++;
            }
        }

        if($this->curMainTab === false) {                           
            $first = current($this->items);
            $this->curMainTab = 0;
            $this->curMainTabTitle = $first['title']; 
        }    
        
       // echo "main tab: {$this->curMainTabTitle}({$this->curMainTab})<br/>sub tab index: {$this->curSubTabTitle}({$this->curSubTab})"; exit;
    }

    function getMainTabs($updateCurrentTabs = true) // not implemented
    {
        if($updateCurrentTabs)
            $this->getCurrentTabs();

        $n = 0;
        $aMainTabs = array();
        foreach ($this->items as $title => $v) 
        {
            if(sizeof($v['sub'])==0) continue;

            $firstSub = reset($v['sub']);
            $aMainTabs[] = array('title'=>$title, 'active'=>( $this->curMainTab == $n ), 
                                 'url'=>'index.php?s='.$firstSub['class'].'&ev='.$firstSub['event'].$firstSub['eventParams']
                                );
            $n++; 
        }
        return $aMainTabs;
    }

    function getSubTabs() // not implemented
    {
        $aSubTabs = array();
        $i=0;
        
        if(isset($this->items[$this->curMainTabTitle])) 
        {
            $item = $this->items[$this->curMainTabTitle];
            for($i=0; $i<count($item['sub']); $i++)
            {
                $sub = &$item['sub'][$i];
                if(!$sub['visible'] && !$bSelected) continue;
                
                $aSubTabs[] = array(
                    'title'     => $sub['title'] . (isset($sub['counter']) && $sub['counter']!==false ? ' '.$sub['counter'] : ''),
                    'active'    => ($i == $this->curSubTab) ,
                    'separator' => ($sub['type'] == self::typeSeparator), 
                    'url'       => ($sub['type'] == self::typeModuleMethod ?
                                    Module::adminCreateLink( $sub['event'].$sub['eventParams'], $sub['class']) : //mm
                                    $sub['class']), // link
                    'priority'  => $sub['priority'],
                    'rlink'     => $sub['rlink'],
                    'id' => ($sub['type'] == self::typeModuleMethod ? $sub['class'].'-'.$sub['event'] : '')
                );
            }
        }
        
        Func::array_sort_by_subkey($aSubTabs, 'priority', true);

        return $aSubTabs;
    }
    
    function getTabs($updateCurrentTabs = true)
    {   
        if($updateCurrentTabs)
            $this->getCurrentTabs();
     
        $aTabs = array();
        $n = 0;  
                 
        foreach($this->items as $title => $params)
        {      
            if(sizeof($params['sub'])==0) { 
                $n++;
                continue;
            }

            //main tabs
            $paramsMain = &$params['sub'][0];
            
            $bSelected = ( $this->curMainTab == $n ); 
            
            $aTabs[$n] = array('title'     => $title . (isset($params['counter']) && $params['counter']!==false ? ' '.$params['counter'] : ''), 
                               'active'    => $bSelected,
                               'class'     => $paramsMain['class'], 
                               'separator' => ($paramsMain['type'] == self::typeSeparator), 
                               'url'       => ($paramsMain['type'] == self::typeModuleMethod ?
                                    Module::adminCreateLink( $paramsMain['event'].$paramsMain['eventParams'], $paramsMain['class']) : //mm
                                    $paramsMain['class']), // link 
                               'subtabs'    => array() 
                               );     
            
            //sub tabs
            if(count($params['sub'])>1) 
            {
                foreach($params['sub'] as $index=>$sub)
                {
                    $bSelectedSub = ($bSelected && $index == $this->curSubTab);
                                             
                    if(!$sub['visible'])// && !$bSelectedSub) 
                        continue;
                    
                    $aTabs[$n]['subtabs'][] = array(
                        'title'  => $sub['title'] . (isset($sub['counter']) && $sub['counter']!==false ? ' '.$sub['counter'] : ''),
                        'class'  => $sub['class'],
                        'event'  => $sub['event'],
                        'params' => $sub['eventParams'],
                        'active' => $bSelectedSub,
                        'separator' =>($sub['type'] == self::typeSeparator),
                        'rlink'  => $sub['rlink'], 
                        'url'    => ($sub['type'] == self::typeModuleMethod ?
                            'index.php?s='.$sub['class'].'&ev='.$sub['event'].$sub['eventParams'] : //mm
                            $sub['class']), //link
                        'priority'=>$sub['priority'],
                        'id' => ($sub['type'] == self::typeModuleMethod ? $sub['class'].'-'.$sub['event'] : '')  
                    );
                }
            }
            
            $n++;
        }
        
        return $aTabs;
    }
    
    function getPageTitle()
    {
        return $this->curMainTabTitle.(!empty($this->curSubTabTitle)?' / '.$this->curSubTabTitle:'');
    }
    
}
