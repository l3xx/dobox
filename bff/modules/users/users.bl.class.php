<?php

define('USERS_AVATAR_PATH', PATH_BASE.'files'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR);  
define('USERS_AVATAR_URL',  SITEURL.'/files/images/avatars/' );

abstract class UsersBase extends Module
{
	var $securityKey = 'c98e21be338bb141536b03140152c8c4';
    var $manageNonSystemGroups = false;
    
    const GROUPID_SUPERADMIN  = 7;
    const GROUPID_MODERATOR   = 17;
    const GROUPID_MEMBER      = 22;

    // --------------------------------------------------------------------------- 
    // users
        
    function userInsert($aUserData)
    {
        $aUserData['password'] = $this->security->getUserPasswordMD5( $aUserData['password'] );
        
        $this->db->prepareInsertQuery($sFields, $sValues, $aUserData);
        $this->db->execute('INSERT INTO '.TABLE_USERS.' ('.$sFields.', created) 
                   VALUES ('.$sValues.', '.$this->db->getNOW().')');
        return $this->db->insert_id(TABLE_USERS, 'user_id');
    }

    function userUpdate($nUserID, $aData, $sAdd2Query='')
    {
        $this->db->prepareUpdateQuery($sQueryData, $aData);
        return $this->db->execute('UPDATE '.TABLE_USERS.' SET '.$sAdd2Query.''.$sQueryData.' WHERE user_id='.intval($nUserID) );
    }
    
    function userCreate($aUserData, $aGroups = null)
    {
        $nUserID = $this->userInsert($aUserData);
        if($nUserID>0 && !empty($aGroups)) {              
            $this->assignUser2Groups($nUserID, $aGroups, false); 
        } else { $nUserID = 0; }
        return $nUserID;
    }
    
    // ---------------------------------------------------------------------------
    // groups
        
    function assignUser2Group($nUserID, $sGroupKeyword, $bOutgoinFromGroups = true)
    {
        //get group id by keyword
        $nGroupID = (int)$this->db->one_data('SELECT group_id FROM '.TABLE_USERS_GROUPS.' 
                                         WHERE keyword='.$this->db->str2sql($sGroupKeyword).' LIMIT 1');
        if(!$nGroupID) return false;

        return $this->assignUser2Groups($nUserID, $nGroupID, $bOutgoinFromGroups);
    }

    function assignUser2Groups($nUserID, $aGroupID, $bOutgoinFromGroups = true)
    {   
        $nUserID = intval($nUserID);
        
        if(!is_array($aGroupID)) 
            $aGroupID = array($aGroupID);
        
        //outgoin user from all groups
        if($bOutgoinFromGroups) 
            $this->outgoinUserFromGroups($nUserID, null);
        
        $aQueryAdd = array();
        $sNOW = $this->db->getNOW();
        for($i=0; $i<count($aGroupID); $i++)
        {
            $aGroupID[$i] = (int)$aGroupID[$i];
            if($aGroupID[$i]<=0) continue;
            $aQueryAdd[] = '('.$nUserID.', '.$aGroupID[$i].', '.$sNOW.')';
        }
        
        if(!empty($aQueryAdd))
            return $this->db->execute('INSERT INTO '.TABLE_USER_IN_GROUPS.' (user_id, group_id, created) VALUES '.join(',', $aQueryAdd));

        return false;
    }
    
    function outgoinUserFromGroups($nUserID, $aGroupID = null)
    {
        $nUserID = intval($nUserID);
        
        if(isset($aGroupID))
        {
            if(!is_array($aGroupID)) 
                $aGroupID = array($aGroupID);
            
            return $this->db->execute('DELETE FROM '.TABLE_USER_IN_GROUPS.' 
                       WHERE user_id='.$nUserID.' AND group_id IN ('.join(',', $aGroupID).')');
        }
        else
        {
            return $this->db->execute('DELETE FROM '.TABLE_USER_IN_GROUPS.' WHERE user_id='.$nUserID);
        }
        return true;
    }

    function outgoinUsersFromGroups($mUserID, $aGroupID = null)
    {
        if(is_array($mUserID))
        {
            $aUserID = $mUserID;
            
            if(isset($aGroupID))
            {
                if(!is_array($aGroupID)) 
                    $aGroupID = array($aGroupID);
                
                $sQuery = 'DELETE FROM '.TABLE_USER_IN_GROUPS.' 
                           WHERE user_id IN('.join(',', $aUserID ).') 
                             AND group_id IN ('.join(',', $aGroupID).')';
                $this->db->execute($sQuery);
            }
            else
            {
                //outgoin userS from all groups 
                $this->db->execute('DELETE FROM '.TABLE_USER_IN_GROUPS.' 
                           WHERE user_id IN('.join(',', $aUserID ).') ');
            }
        }
        else
        {
            $nUserID = intval($mUserID);

            if(isset($aGroupID))
            {
                if(!is_array($aGroupID)) 
                    $aGroupID = array($aGroupID);
                
                $sQuery = 'DELETE FROM '.TABLE_USER_IN_GROUPS.' 
                           WHERE user_id ='.$nUserID.' AND group_id IN ('.join(',', $aGroupID).') ';
                $this->db->execute($sQuery);
            }
            else
            {
                //outgoin useR from all groups
                $this->db->execute('DELETE FROM '.TABLE_USER_IN_GROUPS.' WHERE user_id='.$nUserID.' ');
            }
        }
        return true;
    }

    function isSuperAdmin($nUserID)
    {
        return ((int)$this->db->one_data('SELECT UIG.group_id
                   FROM '.TABLE_USER_IN_GROUPS.' UIG
                   WHERE UIG.user_id  = '.intval($nUserID).' AND UIG.group_id = '.self::GROUPID_SUPERADMIN.'
                   LIMIT 1'));
    }

    function getGroups( $mExceptGroupKeyword = null)
    {
        $sQueryAdd='';
        if(!empty($mExceptGroupKeyword))
        {
            if(!is_array($mExceptGroupKeyword)) 
                $mExceptGroupKeyword = array($mExceptGroupKeyword);
                
            $sQueryAdd = ' WHERE '.$this->db->prepareIN('G.keyword', $mExceptGroupKeyword, true, false, false);
        }
        
        return $this->db->select('SELECT G.* FROM '.TABLE_USERS_GROUPS.' G '.$sQueryAdd.' ORDER BY G.title');
    }

    function getUserGroups($nUserID, $bOnlyKeywords = false)
    {
        $sQuery = 'SELECT '.($bOnlyKeywords?' G.keyword ':' G.* ').'
                  FROM '.TABLE_USERS_GROUPS.' G, 
                       '.TABLE_USER_IN_GROUPS.' UIG 
                  WHERE UIG.user_id = '.intval($nUserID).' AND UIG.group_id = G.group_id
                  ORDER BY G.group_id ASC';
        return ($bOnlyKeywords ? $this->db->select_one_column($sQuery) : $this->db->select($sQuery));
    }
    
    function isGroupKeywordExists($sGroupKeyword) 
    {
        if(!empty($sGroupKeyword))
        {
            $sQuery = 'SELECT group_id FROM '.TABLE_USERS_GROUPS.'
                       WHERE keyword='.$this->db->str2sql($sGroupKeyword).' LIMIT 1';
            return ((integer)$this->db->one_data($sQuery) > 0);
        }
        return false;
    }

    function getGroup($nGroupID) 
    {
        return $this->db->one_array('SELECT * FROM '.TABLE_USERS_GROUPS.' WHERE group_id='.$nGroupID.' LIMIT 1');
    }

    function getGroupPermissions($nGroupID, $sItemType='module') 
    {
        $nGroupID = intval($nGroupID);
        
        $sQuery = "SELECT M.*, (P.item_id IS NOT NULL) as permissed
                    FROM ".TABLE_MODULE_METHODS." M
                    LEFT JOIN ".TABLE_USERS_GROUPS_PERMISSIONS." P
                            ON P.unit_type='group'
                               AND P.unit_id = $nGroupID
                               AND P.item_type = ".$this->db->str2sql($sItemType).'
                               AND P.item_id = M.id
                    WHERE M.module=M.method
                    ORDER BY M.number, M.id
                 ';
        $aData = $this->db->select($sQuery);
        
        $sQuery = "SELECT M.*, (P.item_id IS NOT NULL) as permissed
                    FROM ".TABLE_MODULE_METHODS." M
                    LEFT JOIN ".TABLE_USERS_GROUPS_PERMISSIONS." P
                            ON P.unit_type='group'
                               AND P.unit_id = $nGroupID
                               AND P.item_type = ".$this->db->str2sql($sItemType).'
                               AND P.item_id = M.id
                    WHERE M.module!=M.method
                    ORDER BY M.number, M.id
                 ';
        $aSubData = $this->db->select($sQuery);
        $aSubData = Func::array_transparent($aSubData, 'module');
        
        for ($i=0; $i<count($aData); $i++)
        {
            $aData[$i]['subitems'] = array();
            if(isset($aSubData[$aData[$i]['module']]))
            {
                $aData[$i]['subitems'] = $aSubData[$aData[$i]['module']];
            }
        }     

        return $aData;
    }

    function setGroupPermissions($nGroupID, $aPermissionsID, $sItemType='module') 
    {
        $nGroupID = intval($nGroupID);
        $this->clearGroupPermissions($nGroupID, 'group');

        $sQueryAdd = '';
        $sQuery = 'INSERT INTO '.TABLE_USERS_GROUPS_PERMISSIONS.' (unit_type, unit_id, item_type, item_id, created) VALUES';
        $j = count($aPermissionsID);
        for($i=0;$i<$j; $i++)
        {
            $id = (int)$aPermissionsID[$i];
            if($id==0) continue;
            $sQueryAdd .= "('group', $nGroupID, ".$this->db->str2sql($sItemType).", $id, {$this->db->getNOW()}),";
        }
        $sQueryAdd = trim($sQueryAdd, ',');
        if($sQueryAdd)
            $this->db->execute($sQuery.$sQueryAdd);
    }

    function clearGroupPermissions($aGroupID, $sUnitType) 
    {
        if(!is_array($aGroupID)) 
            $aGroupID = array($aGroupID);
        
        $this->db->execute('DELETE FROM '.TABLE_USERS_GROUPS_PERMISSIONS." 
                       WHERE unit_id IN ('".implode("','", $aGroupID)."') AND unit_type = '".$sUnitType."'");
        return TRUE;
    }

    function getGroupUsers($sGroupKeyword = USERS_GROUPS_MEMBER, $sSqlLimit = '', $sOrderBy = '')
    {
        if(empty($sGroupKeyword))
            return 0;
        
        $sQueryAdd = '';        
        if(!empty($sOrderBy))
            $sQueryAdd .= ' ORDER BY '.$sOrderBy; 
                
        if(!empty($sSqlLimit))      
            $sQueryAdd .= $sSqlLimit;  

        $sQuery = 'SELECT U.*, G.title as group_title 
                   FROM '.TABLE_USERS.' U, 
                       '.TABLE_USERS_GROUPS.' G, 
                       '.TABLE_USER_IN_GROUPS." UG 
                   WHERE U.user_id = UG.user_id 
                        AND UG.group_id = G.group_id
                        AND G.keyword = '$sGroupKeyword'
                        $sQueryAdd";
        return $this->db->select($sQuery);
    }

    /**
    * @description получение кол-ва пользователей в группе
    * @param mixed ( string - group keyword | integer - group ID )
    * @param string query additional params
    * @return integer group users count  
    */
    function getGroupUsersCount($mGroupKeyword = USERS_GROUPS_MEMBER, $sQueryAdd =' AND U.blocked=0 ')
    {
        if(empty($mGroupKeyword))
            return 0;
        
        if(is_integer($mGroupKeyword))
        {
            $sQuery = 'SELECT count(U.user_id) 
                       FROM '.TABLE_USERS.' U, '.TABLE_USER_IN_GROUPS.' UG 
                       WHERE U.user_id = UG.user_id AND UG.group_id = '.intval($mGroupKeyword).' '.$sQueryAdd;
            return $this->db->one_data($sQuery);
        }
        else
        {    
            $sQuery = 'SELECT count(U.user_id) 
                       FROM '.TABLE_USERS.' U, '.TABLE_USERS_GROUPS.' G, '.TABLE_USER_IN_GROUPS.' UG 
                       WHERE U.user_id = UG.user_id AND UG.group_id = G.group_id AND G.keyword = '.$this->db->str2sql($mGroupKeyword)." $sQueryAdd";
            return $this->db->one_data($sQuery);
        }
    }
}
