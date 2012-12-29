<?php

abstract class UsersBaseApp extends UsersBase
{
    const GROUPID_CLIENT = 25;
    
    function getUserPassword($sLogin = '', $sGroupKeyword = USERS_GROUPS_MEMBER, $sQueryAdd = ' AND U.blocked = 0 ')
    {
        if(!empty($sGroupKeyword))
        {
            //check user-to-group assignment
            $sQuery = ' SELECT U.password
                        FROM '.TABLE_USERS.' U,
                             '.TABLE_USER_IN_GROUPS.' UIG,
                             '.TABLE_USERS_GROUPS.' G
                        where U.login = '.$this->db->str2sql($sLogin).'
                            AND U.user_id = UIG.user_id 
                            AND UIG.group_id = G.group_id
                            AND G.keyword='.$this->db->str2sql($sGroupKeyword)." 
                            $sQueryAdd
                            LIMIT 1";
            return $this->db->one_data($sQuery);
        }
        else
        {
            return $this->db->one_data('SELECT U.password
                        FROM '.TABLE_USERS.' U
                        WHERE U.login = '.$this->db->str2sql($sLogin)." $sQueryAdd LIMIT 1");
        }
    }
    
    function updateUserPassword($nUserID, $sPassword='')
    {
        return $this->db->execute('UPDATE '.TABLE_USERS.' 
                   SET password = '.$this->db->str2sql($sPassword).'
                   WHERE user_id='.$nUserID); 
    }
    
    function userAUTH($sLogin, $sPassword, $sGroupKeyword = USERS_GROUPS_MEMBER, $bLogin2Email = false)
    {
        $sPassword = $this->security->getUserPasswordMD5( $sPassword );
        if(!empty($sGroupKeyword))
        {
        $sQuery = 'SELECT U.user_id, U.login, U.email, U.avatar, U.blocked, U.blocked_reason, U.deleted, U.activated, U.admin, U.balance,
                          U.name, U.email2, U.phone, U.contacts
                   FROM '.TABLE_USERS.' U,
                        '.TABLE_USER_IN_GROUPS.' UIG,
                        '.TABLE_USERS_GROUPS.' G
                   WHERE '.($bLogin2Email?' U.email = '.$this->db->str2sql($sLogin):' U.login = '.$this->db->str2sql($sLogin)).'
                        AND U.password = '.$this->db->str2sql($sPassword).'
                        AND U.user_id = UIG.user_id
                        AND UIG.group_id = G.group_id
                        AND G.keyword='.$this->db->str2sql($sGroupKeyword).'
                        LIMIT 1';
        } else {
        $sQuery = 'SELECT user_id, login, email, avatar, blocked, blocked_reason, deleted, activated, admin, balance,
                          name, email2, phone, contacts
                   FROM '.TABLE_USERS.'
                   WHERE '.($bLogin2Email?' email_hash = '.Func::getEmailHash($sLogin).' ':' login = '.$this->db->str2sql($sLogin)).'
                        AND password = '.$this->db->str2sql($sPassword).'
                        LIMIT 1';
        }
        $aData = $this->db->one_array($sQuery);

        if(!$aData)
        {
            // 1. пользователя с таким логином и паролем не существует
            // 2. нет пользователя в составе указанной группы
            // 3. передан неверный GROUP::KEYWORD
            return 0;
        }
        else
        {
            if($aData['blocked']==1) {
                //аккаунт заблокирован
                return array('res'=>-1,'reason'=>$aData['blocked_reason']);
            }

            if($aData['deleted']==1) {
                //аккаунт удален
                return -2;
            }
            
            if($aData['activated']==0) {
                //аккаунт не активирован
                return -3;
            }            
            
            $nUserID = (integer)$aData['user_id'];

            //стартуем сессию пользователя билетных досок
            $this->security->sessionStart('u');            
            
            //update login, last login datetime, session_id
            $sQuery = 'UPDATE '.TABLE_USERS.'
                       SET login_last_ts = login_ts, login_ts = '.$this->db->getNOW().', ip_login = '.Func::getRemoteAddress(true).',
                           session_id = '.$this->db->str2sql( session_id() ).'
                       WHERE user_id = '.$nUserID;
            $this->db->execute($sQuery);
            
            if(!empty($aData['contacts']) && is_string($aData['contacts'])) {
                $aData['contacts'] = unserialize($aData['contacts']);
            }
            if(empty($aData['contacts'])) {
                $aData['contacts'] = array();
            }
            
            $this->security->setUserInfo($nUserID, $aData['login'], $aData['email'], 
                (empty($sGroupKeyword) ? USERS_GROUPS_MEMBER : $this->getUserGroups($nUserID, true)),
                array('avatar' => $aData['avatar'], 'name' => $aData['name'], 
                      'admin' => $aData['admin'], 'balance' => $aData['balance'],
                      'contacts' => array('name'=>$aData['name'], 'email2'=>$aData['email2'], 'phone'=>$aData['phone'], 'other'=>$aData['contacts']), 
                      )
            );
            
            return 1;
        }
    }
    
    /**
     *  Получаем информацию о пользователе
     *  @param mixed integer - by ID; string - by login;
     */
	function getUserInfo($mParamBy, $bOnlyEnabled = false)
	{
        if(!empty($mParamBy))
        {   
            $sQueryAdd = '';
            if(is_integer($mParamBy)) {
                //get info by ID
                $sQueryAdd .= ' user_id='.intval($mParamBy).' ';
            }
            else if(is_string($mParamBy)) {
                //get info by login
                $sQueryAdd .= ' login='.$this->db->str2sql($mParamBy).' ';
            }
            else
                return FALSE;
            
            return $this->db->one_array('SELECT * FROM '.TABLE_USERS.' WHERE '.$sQueryAdd.($bOnlyEnabled?' AND blocked=0 ':'').' LIMIT 1');    
        }
        return FALSE;
	}
        
	function isLoginExists($sLogin, $nExceptUserID = null)
	{
		$sQueryAdd ='';
		if(!empty($nExceptUserID))
		    $sQueryAdd .=' AND user_id!='.intval($nExceptUserID).' ';

		return $this->db->one_data('SELECT COUNT(user_id) FROM '.TABLE_USERS.'
                               WHERE login='.$this->db->str2sql($sLogin)." $sQueryAdd LIMIT 1");
	}

	function isEmailExists($nEmailHash, $nExceptUserID = null)
	{
		$sQueryAdd ='';          
		if(!empty($nExceptUserID))
			$sQueryAdd .=' AND user_id!='.intval($nExceptUserID).' ';

		return $this->db->one_data('SELECT COUNT(user_id) 
                               FROM '.TABLE_USERS.'
                               WHERE email_hash='.$nEmailHash." $sQueryAdd LIMIT 1");
	}
    
    function getBirthdateOptions($sBirthdate = '')
    {                                    
        list($year,$month,$day) = (!empty($sBirthdate) ? split('[/.-]', $sBirthdate) : array('','',''));

        $func_options = create_function('&$item,$key,$cur', '$item = "<option value=\"$item\" ".($item==$cur?"selected":"").">$item</option>";');
        $result = array('days'  => range(1,31),
                        'months' => array(1=>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'),
                        'years'  => range(date('Y')-5,1930) );
                    
        array_walk($result['days'],   $func_options, $day);
        array_walk($result['months'], create_function('&$item,$key,$cur', '$item = "<option value=\"$key\" ".($key==$cur?"selected":"").">$item</option>";'), $month);
        array_walk($result['years'],  $func_options, $year);

        $result['days']   = implode($result['days']);  
        $result['months'] = implode($result['months']);
        $result['years']  = implode($result['years']);
        
        return $result;
    }
    
    function getActivationInfo(&$sCode, &$sLink)
    {
        if(empty($sCode))
            $sCode = md5( substr( md5(uniqid(rand(),true)),0,10 ).time() );

        $sLink = SITEURL.'/user/activate?c='.$sCode;    
    }

    function getPassRecoverInfo(&$sCode, &$sLink)
    {
        if(empty($sCode))
            $sCode = func::generator(10);

        $sLink = SITEURL.'/user/forgotpass?c='.$sCode;
    }
    
    function makeFavorite($nFavID, $nUserID, $sType = 'item')
    {
        if($nFavID>0 && $nUserID>0)
        {
            switch($sType)
            {
                case 'item': 
                {
                    $bRes = $this->db->execute('INSERT INTO '.TABLE_FAVS_ITEMS.' (user_id, fav_id) VALUES('.$nUserID.','.$nFavID.')');     
                    if($bRes)
                    {
                        return $this->db->execute('UPDATE '.TABLE_ITEMS.' SET favs = favs + 1 WHERE item_id='.$nFavID);
                    }
                    return $bRes;
                } break;
            }      
        }
        return false;        
    }
    
    function prepareUserContacts($data)
    {
        if(!empty($data) && is_array($data))
        {
            foreach($data as $k=>&$v) 
            {
                $v['data'] = mb_substr( func::cleanComment($v['data']), 0, 100 );
                if(empty($v['data'])) {
                    unset($data[$k]);
                    continue;
                }
            }
            return serialize( $data );
        }
        return serialize( array() );
    }
    
    
    function getBBSCategories($aSelectedID = array(), $bOptions = false)
    {
        if(!is_array($aSelectedID)) $aSelectedID = array($aSelectedID);
        
        bff::i()->GetModule('bbs');
        
        $aCats = $this->db->select('SELECT id, title, 0 as disabled FROM '.TABLE_BBS_CATEGORIES.' WHERE numlevel=1 ORDER BY numleft');
        //array_unshift($aCats, array('id'=>1, 'title'=>'Все разделы сайта', 'disabled'=>0));
        if($bOptions) 
        {
            $sOptions = '';
            foreach($aCats as $v) {
                $sOptions .= '<option value="'.$v['id'].'" class="'.($v['id']==0 || $v['id']==1?'bold':'').'" '.($v['id']==-2?'disabled':'').' '.(in_array($v['id'], $aSelectedID) ? ' selected="selected"' : '').'>'.$v['title'].'</option>';
            }
        } 
        else 
        {                                                                                  
            $sCheckbox = '';
            foreach($aCats as $v) {
                $sCheckbox .= '<label><input type="checkbox" name="cat[]" class="catcheck '.($v['id']==1?'all bold':'cat').'" value="'.$v['id'].'"'.(in_array($v['id'], $aSelectedID) ? ' checked="checked"' : '').'/> '.$v['title'].'</label><br/>';
            }
        }
        
        $aCats = func::array_transparent($aCats, 'id', true);
        return array( 'cats'=>$aCats, 'options'=>(!empty($sOptions)?$sOptions:''), 'checks'=>(!empty($sCheckbox)?$sCheckbox:'') );
    }
    
    function getUserBalance()
    {
        static $cache;
        if(!isset($cache)) {
            $nUserID = $this->security->getUserID();
            if(!$nUserID){ 
                config::set('user_balance', 0);
                return ($cache = 0); 
            }
            $cache = (float)$this->db->one_data('SELECT balance FROM '.TABLE_USERS.' WHERE user_id = '.$nUserID);
            config::set('user_balance', $cache);
        }         
        return $cache;
    }
    
}