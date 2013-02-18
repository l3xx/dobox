<?php
          
require PATH_CORE.'modules/users/users.adm.class.php'; 

class Users extends UsersAdm
{                                                              
	function main()
	{
		return $this->login();
	}
   
    function profile()
    {
        if(!$this->haveAccessTo('profile'))
            return $this->showAccessDenied();
        
        $bChangeLogin = 0; //1 - для изменения логина
        
        $nUserID = $this->security->getUserID();
        if(!$nUserID) $this->adminRedirect(Errors::IMPOSSIBLE, 'login');        

        if(Func::isPostMethod())
        {
            $sEmail     = Func::POST('email', true);
            $nEmailHash = Func::getEmailHash($sEmail);
            if(!$sEmail || !Func::IsEmailAddress($sEmail) ) {
                $this->errors->set('no_email');
            }
            
            $bChangePassword = Func::POST('changepass');
            if($this->errors->no() && $bChangePassword == 1) {
                $sPasswordCur  = Func::POST('password0', true);    
                $sPassword1    = Func::POST('password1', true);
                $sPassword2    = Func::POST('password2', true);   
                
                if(empty($sPasswordCur))
                    $this->errors->set('no_password_current');

                $sPasswordCurReal = $this->db->one_data('SELECT password FROM '.TABLE_USERS.' WHERE user_id='.$nUserID.' LIMIT 1');
                if( $sPasswordCurReal != $this->security->getUserPasswordMD5($sPasswordCur) )
                    $this->errors->set('current_password_missmatch');
                else {    
                    if(!$sPassword1) 
                        $this->errors->set('no_password_new');
                    elseif($sPassword1!==$sPassword2)
                        $this->errors->set('password_confirmation');
                }
            }
            
            if($this->errors->no() && $bChangeLogin) 
            {
                $sLogin = Func::POST('login', true);
                if(!$sLogin) $this->errors->set('no_login');
                elseif(!Func::checkLoginName($sLogin))
                    $this->errors->set('login_please_use_simple_chars');
                    
                //check if login exist
                $res = $this->db->one_data('SELECT user_id FROM '.TABLE_USERS.' 
                                       WHERE login='.$this->db->str2sql($sLogin)." AND user_id!=$nUserID LIMIT 1");
                if($res) {
                    $this->errors->set('login_exists');
                }
            }

            if($this->errors->no())
            {
                $sQuery = 'UPDATE '.TABLE_USERS.'
                           SET email = '.$this->db->str2sql($sEmail).'
                                '.($bChangeLogin?' , login = '.$this->db->str2sql($sLogin).' ':'').' 
                                '.($bChangePassword?' , password = '.$this->db->str2sql( $this->security->getUserPasswordMD5($sPassword1) ).' ':'').'
                           WHERE user_id='.$nUserID;
                $this->db->execute($sQuery);
                
                $this->security->expire();
                
                $this->adminRedirect(Errors::SUCCESSFULL, 'profile');
            }
        }

        $aData = array( 'user_id'=>$nUserID,
                        'login'=> $this->security->getUserLogin(),
                        'avatar'=>$this->security->getUserInfo('avatar'), 
                        'email'=> $this->security->getUserEmail(), 
                        'tuid' => $this->makeTUID($nUserID),
                        'changelogin' => $bChangeLogin );         
         
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.profile.tpl');
    }
                                
	function logout()
	{
        $this->security->logout($this->adminCreateLink('login'), true, 'a', '/admin/');
	}

	function login()
	{
        if($this->security->haveAccessToAdminPanel())
            $this->adminRedirect(null, 'profile');
        
        $sLogin = '';
		if(bff::$isPost)
		{
			$sLogin = func::POST('login', true);
            if(!$sLogin) $this->errors->set('no_login');
                
			$sPassword = func::POST('password', true);
            if(!$sPassword) $this->errors->set('no_password');

			if($this->errors->no())
			{       
                $sPassword = $this->security->getUserPasswordMD5( $sPassword );

				$sQuery = 'SELECT user_id, login, email, name, avatar, admin, cat FROM '.TABLE_USERS.'
                           WHERE login = '.$this->db->str2sql($sLogin).' AND password = '.$this->db->str2sql($sPassword).'
                           LIMIT 1';

				$aUserData = $this->db->one_array($sQuery);

				if(!$aUserData)
				{   
                    $this->errors->set('login_and_password_unknow', '', false, $sLogin);
				}
				else
				{
                    $nUserID = $aUserData['user_id']; 
                    
                    if(bff::i()->Ban_checkBan(Func::getRemoteAddress(), false, false, true))
                        $this->errors->set(Errors::ACCESSDENIED);
                    else if(!$this->security->haveAccessToAdminPanel($nUserID))
                        $this->errors->set(Errors::ACCESSDENIED);
                    
                    if($this->errors->no())
                    {
                        $aUserGroups = $this->getUserGroups($nUserID, true); 

                        //стартуем сессию администратора
                        session_set_cookie_params(0, '/admin/');
                        $this->security->sessionStart('a', false);
                        
                        //update login_last_datetime, login_datetime to current time
						$this->db->execute('UPDATE '.TABLE_USERS.'
                                   SET login_last_ts=login_ts, login_ts='.$this->db->getNOW().', ip_login= '.Func::getRemoteAddress(true).',
                                        session_id='.$this->db->str2sql(session_id()).'
                                   WHERE user_id='.$nUserID);

						$this->security->setUserInfo($nUserID, $aUserData['login'], $aUserData['email'], $aUserGroups, 
                                array('avatar' => $aUserData['avatar'], 'name' => $aUserData['name'], 'surname' => $aUserData['surname'], 'admin'=>$aUserData['admin']
                                , 'cat' => explode(',', $aUserData['cat']) )
                            );

						Func::JSRedirect('index.php');
                    }
				}
			}
		}

        $this->errors->assign();
        
		$this->tplAssign('login', $sLogin);        
		$this->tplDisplay('login.tpl', TPL_PATH, '', '');
		exit(0);
	}

    //-------------------------------------------------------------------
    // users

    function listing() //members listing
    {
        if(!$this->haveAccessTo('members-listing'))
            return $this->showAccessDenied();

        $aData = $this->input->getm(array(
            'status' => TYPE_INT,
            'city'   => TYPE_INT, 
            )); 
                
        $sQueryFilter = '';
        if($aData['status']>0) {
            switch($aData['status']) {
                case 1: $sQueryFilter .= ' AND U.activated = 1';  break;
                case 2: $sQueryFilter .= ' AND U.activated = 0';  break;
                case 3: $sQueryFilter .= ' AND U.blocked = 1';    break;
                case 4: $sQueryFilter .= ' AND U.subscribed = 1'; break; 
            }
        }
        if($aData['city']>0) {
            $sQueryFilter .= ' AND U.city_id = '.$aData['city'];
        }
        
         // prepare order 
        $this->prepareOrder($orderBy, $orderDirection, 'login,asc');
        $nCount = (integer)$this->db->one_data( 'SELECT count(U.user_id) FROM '.TABLE_USERS.' U WHERE U.member=1 '.$sQueryFilter);

        //generate pagenation
        $this->generatePagenation( $nCount, 20, $this->adminCreateLink("listing&order=$orderBy,$orderDirection&{pageId}"), $sqlLimit);
        
        //get members
        $sQuery = 'SELECT U.user_id, U.admin, U.name, U.login, U.email, U.created, U.blocked, U.activated
                        , U.social, U.vk_id, U.fb_id, U.items
                   FROM '.TABLE_USERS.' U                                    
                    WHERE U.member=1 AND U.admin = 0 '.$sQueryFilter.'
                   ORDER BY'." $orderBy $orderDirection ".($orderBy=='activated'?', created desc':'')." $sqlLimit";

        $aData['users'] = $this->db->select($sQuery);
                
        foreach($aData['users'] as &$v) {
            $v['tuid'] = $this->makeTUID($v['user_id']);
            $v['social_link'] = '';
            if($v['social']) {
                switch($v['social'])
                {
                    case 'vk': { $v['social_link'] = 'http://vkontakte.ru/id'.$v['vk_id']; } break;   
                    case 'fb': { $v['social_link'] = 'http://www.facebook.com/profile.php?id='.$v['fb_id']; } break;
                }
            }
        }
                                                                                                                                              
        $aData['status_options'] = array(0=>'любой', 1=>'активированные', 2=>'неактивированные', 3=>'заблокированные');
        //$aData['city_options']   = bff::i()->Sites_geoCityOptions($aData['city'], 'list');

        $this->tplAssignByRef('aData', $aData);
        return $this->tplFetch('admin.member.listing.tpl');
    }   
    
	function admin_listing() //admins listing
	{
        if(!$this->haveAccessTo('admins-listing'))
            return $this->showAccessDenied();

        $bShowDeleted = true;            
        
        //prepare order 
        $this->prepareOrder($orderBy, $orderDirection, 'login,asc');
        
        //get users
        $this->generatePagenationPrevNext('SELECT U.user_id, U.login, U.email, U.password, U.blocked, U.deleted, U.activated
                        , U.social, U.vk_id
                   FROM '.TABLE_USERS.' U
                   WHERE U.admin = 1 '.(!FORDEV ? ' AND U.user_id!=1 ':'').'
                   ORDER BY U.'.$orderBy.' '.$orderDirection,
                   $aData, 'users', 15);
                     
        //get 'all admins groups'
        $aUsersGroups = $this->db->select(' SELECT G.*, U.user_id
                    FROM '.TABLE_USERS.' U, '.TABLE_USER_IN_GROUPS.' UIG, '.TABLE_USERS_GROUPS.' G
                    WHERE G.adminpanel=1 AND UIG.group_id = G.group_id AND U.user_id = UIG.user_id 
                    ORDER BY G.group_id ');
        $aUsersGroups = Func::array_transparent($aUsersGroups , 'user_id');
        
		foreach($aData['users'] as &$v)
		{
            $v = func::array_2_htmlspecialchars($v);
            $v['tuid']   = $this->makeTUID($v['user_id']);
            $v['groups'] = '';
            
            if(isset($aUsersGroups[ $v['user_id'] ]))
                $v['groups'] = $aUsersGroups[ $v['user_id'] ];
		}

		$this->tplAssign('aData', $aData);
		return $this->tplFetch('admin.admin.listing.tpl');
	}
    
	function member_add()
	{
        if(!$this->haveAccessTo('members-edit'))
            return $this->showAccessDenied();
        
        $aData = array('email'=>'','name'=>'','password'=>'','avatar'=>'',
                       'skype'=>'','email2'=>'','phone'=>'','city_id'=>0);
                       
		if(bff::$isPost)
		{
            $this->input->postm(array(   
                'name'      => TYPE_STR,    
                'email'     => TYPE_STR,
                'password'  => TYPE_STR,
                'password2' => TYPE_STR,
                //'city_id'   => TYPE_INT,            
                'skype'     => TYPE_STR,
                'email2'    => TYPE_STR,
                'phone'     => TYPE_STR,        
            ), $aData); 
            
            do
            {
//                if($aData['city_id']<=0) {
//                    $this->errors->set('wrong:city'); break;
//                }
     
                $aData['email_hash'] = func::getEmailHash($aData['email']);  
            
                if(!$aData['email']) {
                    $this->errors->set('empty:email');  break; 
                }
                elseif (!Func::IsEmailAddress($aData['email'])) {
                    $this->errors->set('wrong:email');  break; 
                }
                if($this->isEmailExists($aData['email_hash'])) {
                    $this->errors->set('email_exist'); 
                     break;
                }
                
                if(!$aData['password']) {
                    $this->errors->set('empty:password');  break;
                }
                elseif($aData['password']!=$aData['password2']) {
                    $this->errors->set('password_confirmation');  break;
                }

			    if($this->errors->no())
			    {                                                    
                    $aData['login']  = $aData['email'];
                    $aData['member'] = 1;
                    $aData['ip_reg'] = func::getRemoteAddress(true);
                    $aData['activated'] = 1;
                    
                    unset($aData['password2']);
                    
				    $nRecordID = $this->userInsert($aData);
				    if($nRecordID>0) 
                    {
                        $avatar = new CAvatar(TABLE_USERS, USERS_AVATAR_PATH, 'avatar', 'user_id');
                        $avatar->update($nRecordID, false, true);
				        
                        $this->assignUser2Groups($nRecordID, array(USERS_GROUPS_MEMBER) );
                    }
                    
                    $this->adminRedirect(Errors::SUCCESSFULL, 'listing');
			    }
                
            } while(false);

            $this->input->postm(array(
                'password2' => TYPE_STR,
            ), $aData); 
            
            func::array_2_htmlspecialchars($aData, null, true);
		}

        $aData = array_merge($aData, array('password2'=>'','user_id'=>''));
 
        //$aData['city_options'] = bff::i()->Sites_geoCityOptions($aData['city_id'], 'edit');
        $aData['edit'] = false;
		$this->tplAssignByRef('aData', $aData);
		return $this->tplFetch('admin.member.form.tpl');
	}  
    
	function member_edit()
	{
        if(!$this->haveAccessTo('members-edit'))
            return $this->showAccessDenied();
                
        if(!($nRecordID = $this->input->id())) 
            $this->adminRedirect(Errors::IMPOSSIBLE, 'listing');
        
		$sTUID = func::GET('tuid');
		if(!$this->checkTUID($sTUID, $nRecordID))
		    return $this->showAccessDenied();

        $aData = array('admin'=>0);
        
		if(func::isPostMethod())
		{	
            $this->input->postm(array(   
                'name'      => TYPE_STR,    
                'email'     => TYPE_STR, 
                'changepass'=> TYPE_BOOL,   
                'password'  => TYPE_STR, 
              //  'city_id'   => TYPE_INT,
                'skype'     => TYPE_STR,
                'email2'    => TYPE_STR,
                'phone'     => TYPE_STR, 
            ), $aData); 
                
			if(empty($aData['email']))
				$this->errors->set('empty:email');
			elseif (!func::IsEmailAddress($aData['email']))
			    $this->errors->set('wrong:email');                      
                
            if($aData['changepass']) {
                if(empty($aData['password'])) 
                    $this->errors->set('empty:password');
                else {
                    $aData['password'] = $this->security->getUserPasswordMD5( $aData['password'] );
                }          
            } else {
                unset($aData['password']);
            }
 
//            if($aData['city_id']<=0)
//                $this->errors->set('wrong:city');

            $aData['email_hash'] = func::getEmailHash( $aData['email'] );
            if($this->isEmailExists($aData['email_hash'], $nRecordID)) {
                $this->errors->set('email_exist'); 
            }
                
            if($this->errors->no())
			{
                #update user data
                unset($aData['changepass']);
                $aData['member'] = 1;
                $aData['login']  = $aData['email'];
				$this->userUpdate($nRecordID, $aData);

                $avatar = new CAvatar(TABLE_USERS, USERS_AVATAR_PATH, 'avatar', 'user_id');
                $avatar->update($nRecordID, true, true);
                
                $this->adminRedirect(Errors::SUCCESSFULL, (!func::GET('members')?'admin_':'').'listing');
			}
		}
        
        $aUserInfo = $this->db->one_array('SELECT U.*, C.title as city, R.region_id, R.title as region 
                                        FROM '.TABLE_USERS.' U
                                        LEFT JOIN '.TABLE_CITY.' C   ON U.city_id=C.city_id
                                        LEFT JOIN '.TABLE_REGION.' R ON C.region_id=R.region_id
                                       WHERE U.user_id='.$nRecordID.' LIMIT 1');
        $aData = func::array_2_htmlspecialchars(array_merge($aUserInfo,$aData), null, true);

        $aData['social_link'] = '';
        if($aData['social']) {
            switch($aData['social'])
            {
                case 'vk': { $aData['social_link'] = 'http://vkontakte.ru/id'.$aData['vk_id']; }
            }
        }

        $aData['tuid'] = $sTUID;
        $aData['edit'] = true;
        $this->tplAssignByRef('aData', $aData);
		return $this->tplFetch('admin.member.form.tpl');
	}

    function mod_add()
    {
        if(!$this->haveAccessTo('users-edit'))
            return $this->showAccessDenied();
        
        $this->input->postm(array(
            'login'     => TYPE_STR,
            'avatar'    => TYPE_STR,
            'name'      => TYPE_STR,
            'email'     => TYPE_STR,
            'password'  => TYPE_STR,
            'password2' => TYPE_STR,
            'balance'   => TYPE_NUM,
            //'city_id'   => TYPE_INT,    
            'skype'     => TYPE_STR,
            'email2'    => TYPE_STR,
            'phone'     => TYPE_STR,
            'group_id'  => TYPE_ARRAY_INT,
            'cat'       => TYPE_ARRAY_UINT,
        ), $aData); 
        
        $aData['admin'] = 0;
        
        if(bff::$isPost)
        {
            do
            {
//                if($aData['city_id']<=0) {
//                    $this->errors->set('wrong:city'); break;
//                }
     
                $aData['email_hash'] = func::getEmailHash($aData['email']);  

                if(!$aData['login']){
                    $this->errors->set('empty:login');  break;
                }
                                 
                if(!$aData['email']) {
                    $this->errors->set('empty:email');  break; 
                }
                elseif (!Func::IsEmailAddress($aData['email'])) {
                    $this->errors->set('wrong:email');  break; 
                }
                if($this->isLoginExists($aData['login'])) {
                    $this->errors->set('login_exist');  break; 
                }
                if($this->isEmailExists($aData['email_hash'])) {
                    $this->errors->set('email_exist'); break;
                }
                
                if(!$aData['password']) {
                    $this->errors->set('empty:password');  break;
                }
                elseif($aData['password']!=$aData['password2']) {
                    $this->errors->set('password_confirmation');  break;
                }

                if($this->errors->no())
                {
                    $aGroupID = $aData['group_id']; //array  
                    $aData['member']  = 0;
                    $aData['ip_reg']  = func::getRemoteAddress(true);
                    $aData['activated'] = 1;
                    $aData['cat']  = join(',', $aData['cat']); 
                    
                    unset($aData['password2'], $aData['group_id']);
                    
                    $nRecordID = $this->userInsert($aData);
                    if($nRecordID>0) 
                    {
                        $avatar = new CAvatar(TABLE_USERS, USERS_AVATAR_PATH, 'avatar', 'user_id');
                        $avatar->update($nRecordID, false, true);
                        
                        if(empty($aGroupID)) $aGroupID = array(USERS_GROUPS_MEMBER);
                        else $this->assignUser2Groups($nRecordID, $aGroupID);
                        
                        # обновляем, является ли юзер администратором
                        $bIsAdmin = 0;  
                        if(!(count($aGroupID)==1 && current($aGroupID) == self::GROUPID_MEMBER)) {
                            if(in_array(self::GROUPID_SUPERADMIN, $aGroupID) || in_array(self::GROUPID_MODERATOR, $aGroupID)) {
                                $bIsAdmin = 1;
                            } else {
                                $aUserGroups = $this->getGroups(null, $aGroupID);
                                foreach($aUserGroups as $v){
                                    if($v['adminpanel'] == 1) {
                                        $bIsAdmin = 1; break;
                                    }
                                }
                            }
                            
                            if($bIsAdmin)
                                $this->db->execute('UPDATE '.TABLE_USERS.' SET admin='.$bIsAdmin.' WHERE user_id='.$nRecordID);
                        }
                    }
                    
                    $this->adminRedirect(Errors::SUCCESSFULL, (!$aData['member']?'admin_':'').'listing');
                }
                
            } while(false);

            $this->input->postm(array(
                'password2' => TYPE_STR, 
                'group_id'  => TYPE_ARRAY_INT,
            ), $aData); 
            
            func::array_2_htmlspecialchars($aData, null, true);

            $aActiveGroupsID = $aData['group_id'];
        }
        else
        {
            $aActiveGroupsID = array();
        }

        $aData = array_merge($aData, array('password2'=>'','user_id'=>''));
        
        //assign groups         
        $exists_options = '';
        $active_options = '';
        $aGroups = $this->getGroups(array(USERS_GROUPS_MEMBER, USERS_GROUPS_SUPERADMIN));
        for ($i=0; $i<count($aGroups); $i++)
        {
            if(in_array($aGroups[$i]['group_id'], $aActiveGroupsID)) {
                $active_options .= '<option value="'.$aGroups[$i]['group_id'].'" style="color:'.$aGroups[$i]['color'].';">'.$aGroups[$i]['title'].'</option>';
            }
            else
            $exists_options .= '<option value="'.$aGroups[$i]['group_id'].'" style="color:'.$aGroups[$i]['color'].';">'.$aGroups[$i]['title'].'</option>';
        }
        $this->tplAssign('exists_options', $exists_options);
        $this->tplAssign('active_options', $active_options);

        //$aData['city_options'] = bff::i()->Sites_geoCityOptions($aData['city_id'], 'edit');
        $aData['edit'] = false;
        $this->tplAssign('aCategories', $this->getBBSCategories($aData['cat']));
        $this->tplAssignByRef('aData', $aData);
        return $this->tplFetch('admin.mod.form.tpl');
    }  
    
    function mod_edit()
    {
        if(!$this->haveAccessTo('users-edit'))
            return $this->showAccessDenied();
                
        if(!($nRecordID = $this->input->id())) 
            $this->adminRedirect(Errors::IMPOSSIBLE, 'listing');
        
        $sTUID = func::GET('tuid');
        if(!$this->checkTUID($sTUID, $nRecordID))
            return $this->showAccessDenied();

        $aData = array('admin'=>0);
        
        #анализируем группы, в которые входит пользователь
        $bUserSuperadmin = 0;
        $aUserGroups = $this->getUserGroups($nRecordID);
        foreach($aUserGroups as $v){
            if($v['group_id'] == self::GROUPID_SUPERADMIN) 
                $bUserSuperadmin = 1;
            if($v['adminpanel'] == 1)
                $aData['admin'] = 1;
        }
        
        if(bff::$isPost)
        {    
            $this->input->postm(array(   
                'name'      => TYPE_STR,   
                'email'     => TYPE_STR, 
                'changepass'=> TYPE_BOOL,   
                'password'  => TYPE_STR, 
                'balance'   => TYPE_NUM,
              //  'city_id'   => TYPE_INT,      
                'skype'     => TYPE_STR,
                'email2'    => TYPE_STR,
                'phone'     => TYPE_STR,
              //  'im_noreply'=> TYPE_BOOL,
                'group_id'  => TYPE_ARRAY_INT,
                'cat'       => TYPE_ARRAY_UINT, 
            ), $aData); 

            if(!$aData['admin']){ //удаляем настройки предназначенные для админов
                unset($aData['im_noreply']);
            }
                
            if(empty($aData['email']))
                $this->errors->set('empty:email');
            elseif (!func::IsEmailAddress($aData['email']))
                $this->errors->set('wrong:email');                      
                
            if($aData['changepass']) {
                if(empty($aData['password'])) 
                    $this->errors->set('empty:password');
                else {
                    $aData['password'] = $this->security->getUserPasswordMD5( $aData['password'] );
                }          
            } else {
                unset($aData['password']);
            }
 
//            if($aData['city_id']<=0)
//                $this->errors->set('wrong:city');
            
            $aGroupID = $aData['group_id'];
            
            $aData['email_hash'] = func::getEmailHash( $aData['email'] );
            if($this->isEmailExists($aData['email_hash'], $nRecordID)) {
                $this->errors->set('email_exist'); 
            }
                
            if($this->errors->no())
            {
                #update user data
                unset($aData['changepass'], $aData['group_id']);
                $aData['member'] = in_array(self::GROUPID_MEMBER,$aGroupID)?1:0;
                $aData['cat']  = join(',', $aData['cat']); 
                $this->userUpdate($nRecordID, $aData);

                $avatar = new CAvatar(TABLE_USERS, USERS_AVATAR_PATH, 'avatar', 'user_id');
                $avatar->update($nRecordID, true, true);
                
                #set user groups
                if($bUserSuperadmin && !in_array(self::GROUPID_SUPERADMIN,$aGroupID))
                    $aGroupID = array_merge($aGroupID, array(self::GROUPID_SUPERADMIN));
                
                $this->assignUser2Groups($nRecordID, $aGroupID);
                
                #обновляем, является ли юзер администратором 
                $bIsAdmin = 0;  
                if($this->errors->no()) {
                    if($bUserSuperadmin || in_array(self::GROUPID_MODERATOR, $aGroupID)) {
                        $bIsAdmin = 1;
                    } elseif(count($aGroupID)==1 && current($aGroupID) == self::GROUPID_MEMBER) {
                        $bIsAdmin = 0;
                    } else {
                        $aUserGroups = $this->getUserGroups($nRecordID);
                        foreach($aUserGroups as $v){
                            if($v['adminpanel'] == 1) {
                                $bIsAdmin = 1; break;
                            }
                        }
                    }
                    
                    if($aData['admin'] != $bIsAdmin) {
                        $sQuery = ', im_noreply = 0';
                        $this->db->execute('UPDATE '.TABLE_USERS.' SET admin='.$bIsAdmin.(!$bIsAdmin?$sQuery:'').' WHERE user_id='.$nRecordID);
                    }
                }
                
                #если пользователь редактирует собственные настройки
                if($this->security->isCurrentUser($nRecordID))
                    $this->security->expire();
                
                $this->adminRedirect(Errors::SUCCESSFULL, (!func::GET('members')?'admin_':'').'listing');
            }
            
            $aActiveGroupsID = $aGroupID;
        }
        else
        {
            $aActiveGroupsID = array();
            for ($j=0; $j<count($aUserGroups); $j++) {
                $aActiveGroupsID[] = $aUserGroups[$j]['group_id'];
            } 
        }
        
        $aUserInfo = $this->db->one_array('SELECT U.*, C.title as city, R.region_id, R.title as region 
                                        FROM '.TABLE_USERS.' U
                                        LEFT JOIN '.TABLE_CITY.' C   ON U.city_id=C.city_id
                                        LEFT JOIN '.TABLE_REGION.' R ON C.region_id=R.region_id
                                       WHERE U.user_id='.$nRecordID.' LIMIT 1');
        $aData = func::array_2_htmlspecialchars(array_merge($aUserInfo,$aData), null, true);

        $aData['social_link'] = '';
        if($aData['social']) {
            switch($aData['social'])
            {
                case 'vk': { $aData['social_link'] = 'http://vkontakte.ru/id'.$aData['vk_id']; }
            }
        }
  
        //assign groups
        $exists_options = $active_options = '';  
        $aGroupsExlude = array(USERS_GROUPS_MEMBER);
        if(!$bUserSuperadmin) $aGroupsExlude[] = USERS_GROUPS_SUPERADMIN;
        $aGroups = $this->getGroups( $aGroupsExlude );
        for ($i=0; $i<count($aGroups); $i++)
        {
            if(in_array($aGroups[$i]['group_id'], $aActiveGroupsID))
            {
                $active_options .= '<option value="'.$aGroups[$i]['group_id'].'" style="color:'.$aGroups[$i]['color'].';">'.$aGroups[$i]['title'].'</option>';
            }
            else
            $exists_options .= '<option value="'.$aGroups[$i]['group_id'].'" style="color:'.$aGroups[$i]['color'].';">'.$aGroups[$i]['title'].'</option>';
        }
        $this->tplAssignByRef('exists_options', $exists_options);
        $this->tplAssignByRef('active_options', $active_options);
        
        //$aData['city_options'] = bff::i()->Sites_geoCityOptions($aData['city_id'], 'edit');

        $aData['cat'] = explode(',', $aData['cat']);
        $this->tplAssign('aCategories', $this->getBBSCategories($aData['cat'])); 
        
        $aData['superadmin'] = $bUserSuperadmin;
        $aData['tuid'] = $sTUID;
        $aData['edit'] = true;
        $this->tplAssignByRef('aData', $aData);
        return $this->tplFetch('admin.mod.form.tpl');
    }
    
	function user_action()
	{
        if(!$this->haveAccessTo('users-edit'))
            return $this->showAccessDenied();
            
        if(!($nRecordID = $this->input->id('rec', 'gp'))) 
            $this->adminRedirect(Errors::IMPOSSIBLE);
 
        $sTUID = func::GETPOST('tuid');
        if(!$this->checkTUID($sTUID, $nRecordID))
            return $this->showAccessDenied();

        if( $this->isSuperAdmin($nRecordID) )
            $this->adminRedirect(Errors::ACCESSDENIED);

        switch( Func::GET('type') )
        {
            case 'delete': 
            {
                break;
                
                //delete avatar
                $avatar = new CAvatar(TABLE_USERS, USERS_AVATAR_PATH, 'avatar', 'user_id');
                $avatar->delete($nRecordID, false);
                
                $this->db->execute('DELETE FROM '.TABLE_USER_IN_GROUPS.' WHERE user_id='.$nRecordID);
                $this->db->execute('DELETE FROM '.TABLE_USERS.' WHERE user_id='.$nRecordID.' ');              
            } break;
            case 'logout': 
            {
                $bMember = $this->input->get('member', TYPE_UINT);
                $sUserSessionID = $this->db->one_data('SELECT session_id FROM '.TABLE_USERS.' WHERE user_id='.$nRecordID.' LIMIT 1');
                if(!empty($sUserSessionID)) {
                    $this->security->impersonalizeSession($sUserSessionID, null, true);
                    $this->db->execute('UPDATE '.TABLE_USERS." SET session_id=".$this->db->str2sql('')." WHERE user_id=$nRecordID ");
                    
                    $this->adminRedirect(Errors::SUCCESSFULL, ($bMember?'member':'mod')."_edit&rec=$nRecordID&tuid=$sTUID");
                }
                
            } break;
        }
            
        $this->adminRedirect(Errors::SUCCESSFULL);
	}
    
    function user_ajax()
    {
        if(!($nRecordID = $this->input->id('rec', 'gp'))) 
            $this->ajaxResponse(Errors::IMPOSSIBLE);
        
        if(func::isAjaxRequest(null))
        {
            switch(Func::GETPOST('action'))
            {
                case 'avatar-delete':
                {
                    if(!$this->haveAccessTo('users-edit'))
                        $this->ajaxResponse(Errors::ACCESSDENIED);

                    $avatar = new CAvatar(TABLE_USERS, USERS_AVATAR_PATH, 'avatar', 'user_id');
                    $avatar->delete($nRecordID, true);

                    $this->ajaxResponse(Errors::SUCCESSFULL); 
                }break;
                case 'user-info':
                {
                    $aData = $this->db->one_array('SELECT U.*, C.title as city, R.region_id, R.title as region 
                                                    FROM '.TABLE_USERS.' U
                                                    LEFT JOIN '.TABLE_CITY.' C   ON U.city_id=C.city_id
                                                    LEFT JOIN '.TABLE_REGION.' R ON C.region_id=R.region_id
                                                   WHERE U.user_id='.$nRecordID.' LIMIT 1');
                                                   
                    $aData['tuid'] = $this->makeTUID($nRecordID);
                    $aData['sendmsg'] = 0;//($this->security->isAdmin() || $aData['im_noreply'] == 0);  
                    $this->tplAssignByRef('aData', $aData); 
                    $this->adminCustomCenterArea();       
                    
                    $this->tplDisplay('admin.user.info.tpl'); 
                    exit;
                }break;
                 case 'user-block':
                {   
                    if(!$this->haveAccessTo('users-edit') || $this->security->isCurrentUser($nRecordID))  
                        $this->ajaxResponse(Errors::ACCESSDENIED); 
                             
                    $sReason  = mb_strcut( Func::POSTGET('blocked_reason',true), 0, 300);
                    $nBlocked = Func::POSTGET('blocked')?1:0;

                    $this->db->execute('UPDATE '.TABLE_USERS.' 
                                   SET blocked_reason = '.$this->db->str2sql($sReason).',
                                       blocked = '.$nBlocked.'
                                   WHERE user_id = '.$nRecordID); 

                    $this->ajaxResponse( Errors::SUCCESSFULL );               
                } break;
            }
        }
        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }


} 
