<?php
                                                                                                
abstract class UsersAdm extends UsersBaseApp
{
    function group_listing()
    {
        if(!$this->haveAccessTo('groups-listing'))
            return $this->showAccessDenied();

        $this->tplAssign(array('bManageNonsystemGroups' => $this->manageNonSystemGroups,
                               'aData' => $this->getGroups(),)); 
        return $this->tplFetch('admin.group.listing.tpl', PATH_CORE.'modules/users/tpl/'.LANG_DEFAULT.'/');
    }

    function group_add()
    {
        if(!FORDEV && !$this->manageNonSystemGroups)
            return $this->showAccessDenied();

        if(!$this->haveAccessTo('groups-edit'))
            return $this->showAccessDenied();

        $aData = array('title'=>'','keyword'=>'','adminpanel'=>'','issystem'=>'','color'=>'', 'deletable'=>1);

        if(func::isPostMethod())
        {
            $this->input->postm(array(
                'title'      => TYPE_STR,
                'keyword'    => TYPE_STR,
                'adminpanel' => TYPE_BOOL,
                'color'      => TYPE_STR,
                'issystem'   => TYPE_BOOL, 
            ), $aData);

            if(!$aData['title'])   $this->errors->set('no_group_title'); 
            
            if(empty($aData['keyword'])) { 
                $this->errors->set('no_group_keyword');
            } else {
                $aData['keyword'] = mb_strtolower($aData['keyword']);
                if($this->isGroupKeywordExists($aData['keyword']))
                    $this->errors->set('group_keyword_exists');
            }
            
            if(empty($aData['color'])) {
                $aData['color'] = '#000';
            }
                
            if($this->errors->no())
            {
                $sQuery = 'INSERT INTO '.TABLE_USERS_GROUPS.' (issystem, adminpanel, color, title, keyword, created, modified)
                           VALUES('.(FORDEV?$this->db->str2sql($aData['issystem']):'0').', '.$aData['adminpanel'].', 
                                  '.$this->db->str2sql($aData['color']).', '.$this->db->str2sql($aData['title']).', 
                                  '.$this->db->str2sql($aData['keyword']).", {$this->db->getNOW()}, {$this->db->getNOW()})";
                $this->db->execute($sQuery);
                
                $this->adminRedirect(Errors::SUCCESSFULL, 'group_listing');
            }
        }

        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.group.form.tpl', PATH_CORE.'modules/users/tpl/'.LANG_DEFAULT.'/');
    }

    function group_edit()
    {
        if(!FORDEV && !$this->manageNonSystemGroups)
            return $this->showAccessDenied();
            
        if(!$this->haveAccessTo('groups-edit'))
            return $this->showAccessDenied();
           
        $aData = array();
            
        if(!($nGroupID = $this->input->id())) 
            $this->adminRedirect(Errors::IMPOSSIBLE, 'group_listing');
 
        $aGroupInfo = $this->getGroup($nGroupID);
        if($aGroupInfo && $aGroupInfo['issystem'] && !FORDEV)
            return $this->showAccessDenied();

        if(func::isPostMethod())
        {
            $this->input->postm(array(
                'title'      => TYPE_STR,
                'keyword'    => TYPE_STR,
                'adminpanel' => TYPE_BOOL,
                'color'      => TYPE_STR,
                'issystem'   => TYPE_BOOL, 
            ), $aData);
                        
            if(!$aData['title'])   $this->errors->set('no_group_title'); 
            
            if(empty($aData['keyword'])) { 
                $this->errors->set('no_group_keyword');
            } else {
                $aData['keyword'] = mb_strtolower($aData['keyword']);
                if($this->isGroupKeywordExists($aData['keyword']))
                    $this->errors->set('group_keyword_exists');
            }
            
            if(empty($aData['color'])) {
                $aData['color'] = '#000';
            }
            
            if($this->errors->no())
            {
                $sQueryAdd = '';
                if(isset($aData['keyword']) && $aData['keyword']) {
                    $sQueryAdd .=' keyword = '.$this->db->str2sql($aData['keyword']).', ';
                }

                $this->db->execute('UPDATE '.TABLE_USERS_GROUPS.'
                            SET title = '.$this->db->str2sql($aData['title']).',
                                color = '.$this->db->str2sql($aData['color']).',
                                adminpanel = '.$aData['adminpanel'].',
                                '.(FORDEV?'issystem = '.$this->db->str2sql($aData['issystem']).', ':'').'
                                '. $sQueryAdd .' modified = '.$this->db->getNOW().'
                            WHERE group_id='.$nGroupID);

                $this->adminRedirect(Errors::SUCCESSFULL, 'group_listing');
            }
        }
        else
        {
            $aData = $aGroupInfo;
        }

        $aData['deletable'] = !in_array($nGroupID, array(self::GROUPID_MEMBER, self::GROUPID_MODERATOR, self::GROUPID_SUPERADMIN));
                                            
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.group.form.tpl', PATH_CORE.'modules/users/tpl/'.LANG_DEFAULT.'/');
    }

    function group_permission_listing()
    {
        if(!$this->haveAccessTo('groups-edit'))
            return $this->showAccessDenied();           
             
        if(!($nRecordID = $this->input->id())) 
            $this->adminRedirect(Errors::IMPOSSIBLE, 'group_listing');

        if(Func::isPostMethod())
        {                                                           
            $aPermissions = $this->input->post('permission', TYPE_ARRAY_INT); 
            $aPermissions = array_unique($aPermissions);
            $aPermissions = array_values($aPermissions);
            $this->setGroupPermissions($nRecordID, $aPermissions);
            
            $this->adminRedirect(Errors::SUCCESSFULL, 'group_listing');
        }

        $aData = $this->getGroup($nRecordID);
        $aData['permissions'] = $this->getGroupPermissions($nRecordID, 'module');

        $this->tplAssign('aData', $aData); 
        return $this->tplFetch('admin.group.permission.tpl', PATH_CORE.'modules/users/tpl/'.LANG_DEFAULT.'/');
    }

    function group_delete()
    {    
        if(!FORDEV && !$this->manageNonSystemGroups)
            return $this->showAccessDenied();
            
        if(!$this->haveAccessTo('groups-edit'))
            return $this->showAccessDenied();
                                                        
        if(!($nGroupID = $this->input->id())) 
            $this->adminRedirect(Errors::IMPOSSIBLE, 'group_listing');
            
        $aGroupInfo = $this->getGroup($nGroupID);
        if($aGroupInfo)
        {
            if($aGroupInfo['issystem'] && !FORDEV)
                return $this->showAccessDenied();

            if(in_array($aGroupInfo['keyword'], array(USERS_GROUPS_SUPERADMIN, USERS_GROUPS_MODERATOR, USERS_GROUPS_MEMBER)))
                return $this->showAccessDenied();
                
            $this->db->execute('DELETE FROM '.TABLE_USERS_GROUPS.'  WHERE group_id='.$nGroupID);
            $this->db->execute('DELETE FROM '.TABLE_USERS_GROUPS_PERMISSIONS.' WHERE unit_type='.$this->db->str2sql('group').' AND unit_id='.$nGroupID); 
            $this->db->execute('DELETE FROM '.TABLE_USER_IN_GROUPS.' WHERE group_id='.$nGroupID);
        }
        
        $this->adminRedirect(Errors::SUCCESSFULL, 'group_listing');
    }         
}

