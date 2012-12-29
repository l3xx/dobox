<?php             

class Sites extends SitesBase
{                                     
    #---------------------------------------------------------------------------------------
    # Сводная информация
    
    function main()       
    {              
        $aData['tabs'] = array(
            'stat'=>array('t'=>'Сводная информация','a'=>0),
            'profile'=>array('t'=>'Мой профиль','a'=>0), 
            
        );    
        $sTab = func::GET('tab'); if(empty($sTab)) $sTab = 'stat';        
        
        switch($sTab)
        {        
            case 'profile': {                    
                $aData['tabContent'] = bff::i()->Users_profile();     
            } break;
            case 'stat': {
                $aData['subscribers'] = $this->db->one_data('SELECT COUNT(*) FROM '.TABLE_USERS.' WHERE subscribed=1');    
            } break;
        }
        $aData['tabs'][$sTab]['a'] = 1;    
        
        $this->tplAssign('tab', $sTab);
        $this->tplAssignByRef('aData', $aData);
        $this->adminCustomCenterArea();
        
        return $this->tplFetch('admin.main.tpl');
    }

    #---------------------------------------------------------------------------------------
    # Настройки сайта
    
    function siteconfig()
    {
        if(!$this->haveAccessTo('settings'))
            return $this->showAccessDenied();  
        
        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'im-attach-total-update':
                {    
                    $nSize = CAttachment::dirSize(PATH_BASE.'files/im');
                    $this->ajaxResponse( array('view'=>func::getfilesize($nSize,true), 'size'=>$nSize) );
                } break;
            }
            $this->ajaxResponse(Errors::IMPOSSIBLE);
        }
         
        $sCurrentTab = func::POSTGET('tab'); 
        if(empty($sCurrentTab))
            $sCurrentTab = 'general';
            
        if(func::isPostMethod() && func::POST('saveconfig')==1)
        {        
            $conf = func::POST('config', false);
            
            //set off checkboxes
            $this->setCheckboxes($conf, array('mail_smtp1_on', 'mail_smtp2_on'));
            
            $this->saveConfig($conf, false); //в БД
            $this->saveConfig($conf, true); //в файл
            
            $this->adminRedirect(Errors::SUCCESS, 'siteconfig&tab='.$sCurrentTab );
        }                         

        $aConfig = config::getAll();
        $aConfig = array_map('stripslashes', $aConfig);        
        
        $aConfig['options'] = array();
        $aConfig['options']['im_attach_maxsize'] = $this->getOptionsSizetype(config::get('im_attach_maxsize', 0));
        $aConfig['options']['items_images_limit'] = range(1,10,1);

        $aData = $aConfig;
        $aData['tabs'] = array(
            'general'=>array('t'=>'Общие настройки','a'=>0),
            //'paymentsystems'=>array('t'=>'Платёжные системы','a'=>0), 
            'mail'=>array('t'=>'Почта','a'=>0),
        ); 
        
        $aData['tabs'][$sCurrentTab]['a'] = 1;
        $this->tplAssign('tab', $sCurrentTab);        
        $this->tplAssignByRef('aData', $aData);  
        $this->adminCustomCenterArea();   
        return $this->tplFetch('admin.siteconfig.tpl');  
    }
    
    function setCheckboxes(&$conf, $aKeys = array())
    {
        foreach($aKeys as $k)
            $conf[$k] = (!isset($conf[$k])?0:1);
    }
    
    #---------------------------------------------------------------------------------------
    #Cчетчики посещаемости сайта        
    function counters_listing()
    {
        if( !$this->haveAccessTo('counters') )
            return $this->showAccessDenied();

        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'rotate':
                {
                    $this->db->rotateTablednd(TABLE_COUNTERS, '', 'id', 'num');                    
                    $this->ajaxResponse(Errors::SUCCESS);
                } break;
                case 'toggle':
                {
                    $nCounterID = $this->input->id('rec', 'pg');
                    if(!$nCounterID) $this->ajaxResponse(Errors::UNKNOWNRECORD);
                    
                    $this->db->execute('UPDATE '.TABLE_COUNTERS.' SET enabled = (1 - enabled) WHERE id = '.$nCounterID);
                    $this->ajaxResponse(Errors::SUCCESS);
                } break;
                case 'delete':
                {
                    $nCounterID = $this->input->id('rec', 'pg');
                    if(!$nCounterID) $this->ajaxResponse(Errors::UNKNOWNRECORD);
                    
                    $this->db->execute('DELETE FROM '.TABLE_COUNTERS.' WHERE id = '.$nCounterID);
                    $this->ajaxResponse(Errors::SUCCESS);
                } break;
            }
        }
            
        $isAddAction = false;
        $aData = $this->getSiteCounters();
        $aDataAdd = array('title'=>'','code'=>'');
        
        if(func::isPostMethod())
        {
            $aDataAdd = $_POST;
            $sTitle = func::POST('title', true);
            $sCode = func::POST('code', true);
            
            if(!$sTitle) $this->errors->set('no_title');       
            if(!$sCode)  $this->errors->set('no_code');       
            
            if($this->errors->no())
            {
                 $this->db->execute('INSERT INTO '.TABLE_COUNTERS.' (title, code, created)
                            VALUES('.$this->db->str2sql($sTitle).', '.$this->db->str2sql($sCode).', '.$this->db->getNOW().')');
                 
                 $nInsertID = $this->db->insert_id(TABLE_COUNTERS, 'id');
                 $this->db->execute('UPDATE '.TABLE_COUNTERS.' SET num='.$nInsertID.' WHERE id='.$nInsertID);
                 
                 $this->adminRedirect(Errors::SUCCESS, true);
            }
            else $isAddAction = true; 
        }         
             
        $this->tplAssign('aDataAdd', $aDataAdd);
        $this->tplAssign('aData',    $aData);
        $this->tplAssign('isAddAction', ($isAddAction?1:0) );
        
        $this->adminCustomCenterArea();
        $this->includeJS('tablednd');
        return $this->tplFetch('admin.counters.listing.tpl');
    }
    
    function counters_edit()
    {
        if( !$this->haveAccessTo('counters') )
            return $this->showAccessDenied();
        
        if(!($nRecordID = $this->input->id())) 
            $this->adminRedirect(Errors::UNKNOWNRECORD, 'counters_listing');
        
        $aData = $this->db->one_array('SELECT * FROM '.TABLE_COUNTERS.' WHERE id='.$nRecordID);
        if(empty($aData)) $this->adminRedirect(Errors::UNKNOWNRECORD, 'counters_listing');
        
        if(func::isPostMethod())
        {
            $this->input->postm(array(
                    'title' => TYPE_STR,
                    'code'  => TYPE_STR,
                ), $aData, array('title', 'code'));
            
            if($this->errors->no())
            {
                 $this->db->execute('UPDATE '.TABLE_COUNTERS.' 
                           SET title='.$this->db->str2sql($aData['title']).', code = '.$this->db->str2sql($aData['code']).'  
                           WHERE id = '.$nRecordID);
                 
                 $this->adminRedirect(Errors::SUCCESS, 'counters_listing');
            }                        
        }       
  
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.counters.edit.tpl');
    }
    
    function counters_action()
    {
        if( !$this->haveAccessTo('counters') )
            return $this->showAccessDenied();
            
       if(!($nRecordID = $this->input->id()))
            $this->adminRedirect(Errors::UNKNOWNRECORD, 'counters_listing');
       
       switch(func::GET('act'))
       {
            case 'delete':
            {
                  $this->db->execute('DELETE FROM '.TABLE_COUNTERS.' WHERE id='.$nRecordID);
            } break;
       }
       $this->adminRedirect(Errors::SUCCESS, 'counters_listing');
    }

    #---------------------------------------------------------------------------------------
    #Города 
    function cities_listing_main()
    {
        return $this->cities_listing(1);
    }
    
    function cities_listing($nOnlyMain = 0)
    {
        if( !$this->haveAccessTo('cities') )
            return $this->showAccessDenied();
        
        if(bff::$isAjax)
        {
            //$cache = Cache::singleton();
            
            switch(func::GET('act'))
            {
               case 'toggle-enabled':
               {   
                    if(!($nRecordID = $this->input->id())) 
                        $this->ajaxResponse(Errors::UNKNOWNRECORD);
                    
                    $res = $this->db->execute('UPDATE '.TABLE_CITY.' SET enabled=(1-enabled) WHERE city_id='.$nRecordID); 
                    if($res) { 
                        //$cache->delete('geo-сities-all');
                        //$cache->delete('geo-сities-main');
                    }
                    
                    $this->ajaxResponse( $res ? Errors::SUCCESS : Errors::IMPOSSIBLE ); 
                } break;
                case 'toggle-main':
                {   
                    if(!($nRecordID = $this->input->id())) 
                        $this->ajaxResponse(Errors::UNKNOWNRECORD);
                    
                    $res = $this->db->execute('UPDATE '.TABLE_CITY.' SET main=(1-main) WHERE city_id='.$nRecordID);
                    if($res) { 
                        //$cache->delete('geo-сities-main');
                    }
                    
                    $this->ajaxResponse( $res ? Errors::SUCCESS : Errors::IMPOSSIBLE ); 
                } break;
                case 'main-add':
                {   
                    if(!($nRecordID = $this->input->id('city', 'p'))) 
                        $this->ajaxResponse(Errors::UNKNOWNRECORD);
                    
                    $res = $this->db->execute('UPDATE '.TABLE_CITY.' SET main=1 WHERE city_id='.$nRecordID);
                    if($res) { 
                        //$cache->delete('geo-сities-main');
                    }
                    
                    $this->ajaxResponse( $res ? Errors::SUCCESS : Errors::IMPOSSIBLE );  
                } break;             
                case 'rotate':
                {
                    $f = func::GET('f');
                    $f = ($f == 'num' ? 'num' : 'numreg');
                    $res = $this->db->rotateTablednd(TABLE_CITY, '', 'city_id', $f);
                    if($res) {
                        //$cache->delete('geo-сities-all');
                        //$cache->delete('geo-сities-main');
                        $this->ajaxResponse(Errors::SUCCESS);
                    } else {
                        $this->ajaxResponse(Errors::IMPOSSIBLE);
                    }
                                 
                } break;
                case 'notmain-list': #autocomplete
                {
                    $sQ = func::POST('q', true);
                    //получаем список подходящих по названию городов, исключая
                    //- основные города                                  
                    $aResult = $this->db->select('SELECT C.city_id as id, C.title FROM '.TABLE_CITY.' C 
                                  WHERE C.main=0 AND C.title LIKE ('.$this->db->str2sql($sQ.'%').')
                                  ORDER BY C.title');   
                    
                    $aCities = array();
                    foreach($aResult as $c) { 
                        $aCities[$c['id']] = $c['title'];
                    } unset($aResult);
                                  
                    $this->ajaxResponse($aCities);            
                } break;                             
            }
            $this->ajaxResponse(Errors::IMPOSSIBLE);
        }
                                                                                                                  
        $aData = array('main'=>$nOnlyMain,'users'=>func::GET('users'),'region'=>func::GET('region',false,true));  
        $aData['rotate']  = ( ($aData['main'] || $aData['region']) && !($aData['main'] && $aData['region']) && !$aData['users'] ? 1 : 0);
        $aData['rotate_field'] = ( $aData['rotate'] ? ($aData['main'] ? 'num' : 'numreg') : 'title' );
                                                     
        $aData['cities'] = $this->db->select('SELECT C.*, C.city_id as id, COUNT(U.user_id) as users 
            FROM '.TABLE_CITY.' C
                LEFT JOIN '.TABLE_USERS.' U ON U.city_id=C.city_id
            WHERE 1=1 '.($aData['region']? ' AND C.region_id = '.$aData['region'].' ':'').' 
                      '.($aData['main']? ' AND C.main = 1 ':'').'
            GROUP BY C.city_id
           '.($aData['users']? ' HAVING users > 0 ':'').'
            ORDER BY C.'.$aData['rotate_field']  
            );
        
        $aData['regions_options'] = $this->geoOblastOptions($aData['region'], $aRegions);
        $aData['regions'] = func::array_transparent($aRegions, 'region_id', true);
             
        $this->tplAssignByRef('aData', $aData);    
        $this->includeJS(array('tablednd', 'autocomplete'));
        return $this->tplFetch('admin.cities.listing.tpl');
    }

    function cities_add()
    {
        if(!$this->haveAccessTo('cities'))
            return $this->showAccessDenied();
        
        $aData = array('region_id'=>0,'ycoords'=>'','title'=>'','keyword'=>'','main'=>(func::GETPOST('main')?1:0),'enabled'=>0);
        if(func::isPostMethod())
        {
            $this->input->postm(array(
                    'region_id' => TYPE_UINT,
                    'ycoords'   => TYPE_STR,
                    'enabled'   => TYPE_BOOL,
                    'title'     => TYPE_STR,
                    'keyword'   => TYPE_STR,
                ), $aData, array('title', 'keyword', 'region_id'));
             
            if($this->errors->no())
            {   
                $this->db->execute('INSERT INTO '.TABLE_CITY.' (region_id, ycoords, title, keyword, main, enabled)                 
                               VALUES ('.$aData['region_id'].', '.$this->db->str2sql($aData['ycoords']).', 
                                       '.$this->db->str2sql($aData['title']).', '.$this->db->str2sql($aData['keyword']).', 
                                       '.$aData['main'].', '.$aData['enabled'].')');
                              
                $this->adminRedirect(Errors::SUCCESS, 'cities_listing_main');
            }  
            func::array_2_htmlspecialchars($aData); 
        }
        
        $aData['regions_options'] = $this->geoOblastOptions($aData['region_id']);
        $aData['edit'] = false;
        
        $this->tplAssign('aData', $aData);
        $this->adminCustomCenterArea();
        $this->includeJS(array(GEO_YMAPS_JS.'&loadByRequire=1'), false, false);
        return $this->tplFetch('admin.cities.form.tpl');
    }

    function cities_edit()
    {
        if(!$this->haveAccessTo('cities'))
            return $this->showAccessDenied();

        if(!($nRecordID = $this->input->id())) 
            $this->adminRedirect(Errors::UNKNOWNRECORD, 'cities_listing_main');
            
        $aData = $this->db->one_array('SELECT C.*, C.city_id as id, COUNT(U.user_id) as users
                   FROM '.TABLE_CITY.' C
                    LEFT JOIN '.TABLE_USERS.' U ON U.city_id = C.city_id
                   WHERE C.city_id='.$nRecordID.' 
                   GROUP BY C.city_id
                   LIMIT 1');
        if(empty($aData))
            $this->adminRedirect(Errors::IMPOSSIBLE, 'cities_listing_main');
        
        if(func::isPostMethod())
        {
            $this->input->postm(array(
                    'region_id' => TYPE_UINT,
                    'ycoords'   => TYPE_STR,
                    'enabled'   => TYPE_BOOL,
                    'main'      => TYPE_BOOL,
                    'title'     => TYPE_STR,
                    'keyword'   => TYPE_STR,
                ), $aData, array('title', 'keyword', 'region_id'));
                                                   
            if($this->errors->no())
            {   
                $this->db->execute('UPDATE '.TABLE_CITY.' SET
                                   region_id = '.$aData['region_id'].',        
                                   ycoords = '.$this->db->str2sql($aData['ycoords']).',    
                                   title   = '.$this->db->str2sql($aData['title']).', 
                                   keyword = '.$this->db->str2sql($aData['keyword']).', 
                                   main = '.$aData['main'].', 
                                   enabled = '.$aData['enabled'].'
                               WHERE city_id = '.$nRecordID.' LIMIT 1');
                              
                $this->adminRedirect(Errors::SUCCESS, 'cities_listing_main');
            }  
            func::array_2_htmlspecialchars($aData); 
        }
        
        $aData['regions_options'] = $this->geoOblastOptions($aData['region_id']);
        $aData['cregions'] = $this->db->select('SELECT R.*, COUNT(U.user_id) as users 
                                           FROM '.TABLE_REGION.' R 
                                             LEFT JOIN '.TABLE_USERS.' U ON R.region_id=U.region_id
                                           WHERE R.city_id = '.$nRecordID.' 
                                           GROUP BY R.region_id ORDER BY R.title');
        
        $aData['edit'] = true;
        $this->tplAssign('aData', $aData);
        $this->adminCustomCenterArea();
        $this->includeJS(array(GEO_YMAPS_JS.'&loadByRequire=1'), false, false);
        return $this->tplFetch('admin.cities.form.tpl');
    }
    
    function cities_regions()
    {
        if(!$this->haveAccessTo('cities'))
            return $this->showAccessDenied();
            
        $nCityID = $this->input->id('city'); 
                                 
        if(bff::$isAjax)
        {
            $nRegionID = $this->input->id('region', 'p'); 
            
            if(!$nCityID || !$nRegionID) 
                $this->ajaxResponse(Errors::IMPOSSIBLE);
                        
            switch(func::GET('act'))
            {
                case 'edit':
                {                                                         
                    $aRegionData = $this->db->one_array('SELECT * FROM '.TABLE_REGION.' WHERE region_id='.$nRegionID.' AND city_id='.$nCityID);
                    $this->ajaxResponse($aRegionData); 
                } break;  
                case 'delete':
                {                                                 
                    $this->db->execute('DELETE FROM '.TABLE_REGION.' WHERE region_id='.$nRegionID.' AND city_id='.$nCityID);
                    $this->geoRegionsCacheDelete($nCityID);
                    $this->ajaxResponse(Errors::SUCCESS);             
                } break;                      
            }
            
            $this->ajaxResponse(Errors::IMPOSSIBLE);                    
        }
            
        if($nCityID && func::isPostMethod())
        { 
            switch(func::POSTGET('act'))
            {
                case 'add': //niu
                {
                    $sYBounds = Func::POSTGET('ybounds', true);
                    $sYPoly = Func::POSTGET('ypoly', true); 
                    $sTitle = func::POSTGET('title', true);                         
                    if(empty($sTitle))
                        $this->errors->set('empty:title');
                        
                    if($this->errors->no())
                    {
                        $this->db->execute('INSERT INTO '.TABLE_REGION.' (city_id, title, ybounds, ypoly) 
                            VALUES('.$nCityID.','.$this->db->str2sql($sTitle).','.$this->db->str2sql($sYBounds).','.$this->db->str2sql($sYPoly).')');
                            
                        $this->geoRegionsCacheDelete($nCityID);
                    }
                } break;     
                case 'add_many': 
                {      
                    $aRegionBounds = Func::POSTGET('regionbounds', false);
                    $aRegionPoly = Func::POSTGET('regionpoly', false);                        
                    if(!empty($aRegionBounds)) 
                    {
                        $aRegions = array();
                        foreach($aRegionBounds as $k=>$v)
                        {
                            $aRegions[$k] = array(
                                'ybounds' => $v,
                                'ypoly'   => (isset($aRegionPoly[$k])?$aRegionPoly[$k]:'')
                                );
                        }
                        
                        $aExistentRegions = $this->db->select_one_column('SELECT title FROM '.TABLE_REGION.' WHERE city_id='.$nCityID);
                        $aQueryRegions = array();
                        foreach($aRegions as $title=>$v) {
                            if(!in_array($title, $aExistentRegions))
                                $aQueryRegions[] = '('.$nCityID.','.$this->db->str2sql($title).','.$this->db->str2sql($v['ybounds']).','.$this->db->str2sql($v['ypoly']).')';
                        }                        
                        
                        if(!empty($aQueryRegions)) {
                            $this->db->execute('INSERT INTO '.TABLE_REGION.' (city_id, title, ybounds, ypoly) 
                                VALUES'.implode(',', $aQueryRegions) );  
                                
                            $this->geoRegionsCacheDelete($nCityID);                               
                        }                            
                    }
                } break;                
                case 'edit': 
                {     
                    $this->input->postm(array(
                        'region'  => TYPE_UINT,
                        'title'   => TYPE_STR,
                        'ybounds' => TYPE_STR,
                        'ypoly'   => TYPE_STR,
                    ), $aData, array('title'));   
                    
                    if(!$nRegionID) $this->errors->set(Errors::UNKNOWNRECORD);

                    if($this->errors->no())
                    {
                        $this->db->execute('UPDATE '.TABLE_REGION.' 
                            SET title = '.$this->db->str2sql($aData['title']).',
                                ybounds = '.$this->db->str2sql($aData['ybounds']).',
                                ypoly = '.$this->db->str2sql($aData['ypoly']).'
                            WHERE region_id='.$aData['region'].' AND city_id = '.$nCityID);  
                            
                        $this->geoRegionsCacheDelete($nCityID);                            
                    }
                } break;
            }
            
            $this->adminRedirect('', 'cities_edit&rec='.$nCityID);
        }

        $this->adminRedirect(Errors::IMPOSSIBLE, 'cities_edit&rec='.$nCityID);
        
    }
    
    #---------------------------------------------------------------------------------------
    #Подписка        
    function subscribes_listing()
    {
        if( !$this->haveAccessTo('subscribes') )
            return $this->showAccessDenied();

        #order
        $this->prepareOrder($orderBy, $orderDirection, 'created,desc');

        #generate pagenation
        $this->generatePagenation( $this->getSubscribesCount(), 20, 
            $this->adminCreateLink(bff::$event."&order=$orderBy,$orderDirection&{pageId}"), 
            $sqlLimit);
        
        
        $aData  = $this->db->select('SELECT * FROM '.TABLE_SUBSCRIBES." 
                                    ORDER BY $orderBy $orderDirection $sqlLimit");
        
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.subscribes.listing.tpl');
    }
    
    #---------------------------------------------------------------------------------------
    #AJAX
    
    function ajax()
    {
        if(!bff::$isAjax || !$this->security->haveAccessToAdminPanel())
            $this->ajaxResponse(Errors::ACCESSDENIED);       
        
        switch(func::GET('act'))
        {
            case 'city-list': /* @methods: items::items_listing, items::items_add, items::items_edit, 
                                           items::specs_listing, items::specs_add, items::specs_edit,  */
            {                      
                $sPos = func::GETPOST('pos');
                $aExtra = array('expand'=>true);

                $sEmptyTitle = func::GETPOST('empty_title');
                if(!empty($sEmptyTitle)) {
                    $aExtra['empty_title'] = $sEmptyTitle;
                }
                
                $this->ajaxResponse( $this->geoCityOptions(0, $sPos, $aExtra) ); 
            } break;
            
            case 'city-regions': /* @methods: items::items_add, items::items_edit,
                                              items::specs_add, items::specs_edit,   */
            {
                $nCityID = $this->input->id('city', 'p');
                if(!$nCityID) $this->ajaxResponse(Errors::UNKNOWNRECORD); 
                
                $bGetYData = (func::GET('ydata')==1);
                
                $sEmptyTitle = func::GETPOST('empty_title');
                $sEmptyTitle = (!empty($sEmptyTitle) ? $sEmptyTitle : 'не указан');
                
                $aResponse = $this->geoRegionOptions($nCityID, 0, true, $sEmptyTitle, $bGetYData);
                if(!$bGetYData) { unset($aResponse['regdata']); }
                $this->ajaxResponse( $aResponse ); 
            } break;
            case 'unsubscribe':
            {
                if(($nRecordID = func::POSTGET('rec', false, true))<=0) 
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                        
                $this->db->execute('DELETE FROM '.TABLE_SUBSCRIBES.' WHERE id = '.$nRecordID);

                $this->ajaxResponse(Errors::SUCCESS);
            } break;
        }            

        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
         
}
