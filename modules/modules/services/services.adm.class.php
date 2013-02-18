<?php

class Services extends ServicesBase
{

    function settings()
    {
        if(!$this->haveAccessTo('settings'))
            return $this->showAccessDenied();
        
        if(bff::$isAjax)
        {
            switch(func::GET('act'))
            {
                case 'update': 
                {
                    $nServiceID = $this->input->post('id', TYPE_UINT);
                    if(!$nServiceID) $this->ajaxResponse(Errors::UNKNOWNRECORD);
                   
                    $sDescription = $this->input->post('description', TYPE_STR); 
                    
                    $aService = $this->db->one_array('SELECT * FROM '.TABLE_SERVICES.' WHERE id = '.$nServiceID);
                    if(empty($aService)) $this->ajaxResponse(Errors::UNKNOWNRECORD);
                    
                    $bUpdate = false;
                    switch($aService['keyword'])
                    {
                        case 'publicate':
                        case 'up':
                        case 'mark':
                        case 'premium':
                        case 'press':
                        {
                            $p = $this->input->postm(array(
                                'price' => TYPE_UNUM,
                            ));
                            
                            $bUpdate = true; 
                        } break;
                    }
                    
                    if($bUpdate) {
                        $this->db->execute('UPDATE '.TABLE_SERVICES.' 
                                    SET settings = '.$this->db->str2sql(serialize($p)).',
                                        description = '.$this->db->str2sql($sDescription).',
                                        modified = '.$this->db->getNOW().',
                                        modified_uid = '.$this->security->getUserID().'
                                    WHERE id = '.$nServiceID);
                        $this->ajaxResponse( Errors::SUCCESSFULL );
                    }
                    
                } break;
            }
            
            $this->ajaxResponse(Errors::IMPOSSIBLE);
        }
        
        $aData = array('svc'=>array());
                                                  
        $aServices = $this->db->select('SELECT S.*, U.login as modified_login FROM '.TABLE_SERVICES.' S
                                    LEFT JOIN '.TABLE_USERS.' U ON S.modified_uid = U.user_id');
        foreach($aServices as $v)
        {
            $v['settings'] = unserialize($v['settings']);
            $aData['svc'][$v['keyword']] = $v;
        }
        
        $this->adminCustomCenterArea();
        $this->includeJS('wysiwyg');  
        return $this->tplFetchPHP($aData, 'admin.settings.php');
    }
    
    function create()
    {
        if(!FORDEV) return $this->showAccessDenied();
            
        $aData = $this->input->postm(array(
            'title' => TYPE_STR,
            'type'  => TYPE_UINT,
            'keyword' => TYPE_STR,
        ));
            
        if(bff::$isPost)
        {
            if(empty($aData['title'])) {
                $this->errors->set(_t('services', 'Название услуги указано некорректно'));
            }

            if(empty($aData['keyword'])) {
                $this->errors->set(_t('services', 'Keyword услуги указан некорректно')); 
            } else {
                $aKeywordExists = $this->db->one_array('SELECT id, title FROM '.TABLE_SERVICES.' WHERE keyword = '.$this->db->str2sql($aData['keyword']));
                if(!empty($aKeywordExists)) {
                    $this->errors->set(_t('services', 'Указанный keyword уже используется услугой "[title]"', array('title'=>$aKeywordExists['title'])));
                }
            }
            
            if($this->errors->no())
            {
                $aSettings = array();
                $aSettings = serialize($aSettings);
                
                $res = $this->db->execute('INSERT INTO '.TABLE_SERVICES.' (type, keyword, title, settings, enabled)
                    VALUES('.$aData['type'].', :keyword, :title, :settings, 1)',
                    array(':keyword'=>$aData['keyword'], ':title'=>$aData['title'], ':settings'=>$aSettings));
                $this->adminRedirect( (!empty($res) ? Errors::SUCCESS : Errors::IMPOSSIBLE), 'settings');
            }
            
            $aData = func::array_2_htmlspecialchars($aData, array('title','keyword'));
        }
        
        return $this->tplFetchPHP($aData, 'admin.create.php'); 
    }
    
}
