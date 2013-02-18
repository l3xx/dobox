<?php

# Класс пользователей (members)

class Users extends UsersBaseApp
{
    function activate()
    {
        $sActivateKey = $this->input->get('c', TYPE_STR);

        do
        {
            if(empty($sActivateKey) || strlen($sActivateKey)!=32) {
                $sMessage = 'Не верно указан ключ активации'; 
                break;
            }
            
            $aUserInfo = $this->db->one_array('SELECT user_id, email, activated FROM '.TABLE_USERS.' 
                                            WHERE activatekey='.$this->db->str2sql($sActivateKey).' LIMIT 1');
                                          
            if(empty($aUserInfo)) {
                $sMessage = 'Не верно указан ключ активации'; 
                break;
            }

            if($aUserInfo['activated'] != 0) {
                $sMessage = 'Аккаунт с данным ключем активации уже был успешно активирован ранее.'; 
                break;
            }
          
            $this->db->execute('UPDATE '.TABLE_USERS.' SET activated=1, activity_ts='.$this->db->getNOW().' WHERE user_id='.$aUserInfo['user_id']); 
            $sMessage = 1;
            
        } while(false);
        
        $this->tplAssign('activate_message', $sMessage);
        return $this->tplFetch('activate.tpl');
    }
    
	function login()
	{
        if( $this->security->isLogined()) 
            Func::JSRedirect(SITEURL);
        
        config::set('title', 'Авторизация - '.config::get('title', '') ); 

		$sEmail = '';
		if(Func::isPostMethod())
		{
			$sEmail = Func::POST('email', true);
            if(!$sEmail) $this->errors->set('no_email');
            
			$sPassword = Func::POST('password', true);
            if(!$sPassword) $this->errors->set('no_password');
				
			if($this->errors->no())
			{
                $sBlocked = $this->security->checkBan(false, func::getRemoteAddress(), false, true);
                if($sBlocked) {
                     return $this->showForbidden('В доступе отказано', $sBlocked);
                }
                
				$nResult = $this->userAUTH($sEmail, $sPassword, null, true);
				if($nResult == 0)
				{          
					$this->errors->set('email_and_password_unknow', '', false, $sEmail);
				}
                else if($nResult == -1)
                {
                    return $this->showForbidden('аккаунт заблокирован', 'Аккаунт заблокирован');
                }
                else if($nResult == -2)
                {
                    return $this->showForbidden('аккаунт удален', 'Аккаунт удален');
                }
				else
				{
                    if(array_key_exists('remember_me', $_POST))
                        $this->security->setRememberMe('u', $sLogin, $sPassword);
                    
                    $sRedirectURL = $_SERVER['HTTP_REFERER'];
                    if($sRedirectURL == SITEURL.'/user/login/' || $sRedirectURL == SITEURL.'/user/registration/') 
                       $sRedirectURL =  SITEURL.'/user/profile/';

					Func::JSRedirect($sRedirectURL);
				}
			}
			$aData = $_POST;
		}

		$this->tplAssign('email', $sEmail);
        $this->tplAssign('aErrors', $this->errors->show());
		return $this->tplFetch('member.login.tpl');
	}

    function logout()
    {
        if($this->security->isLogined()) {
            
            if(!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'user/profile')===FALSE)
                $sRedirectURL = $_SERVER['HTTP_REFERER'];
            else
                $sRedirectURL = SITEURL;
                      
            $this->security->logout($sRedirectURL, true, 'u', '/');
        }
        
        Func::JSRedirect(SITEURL);
    }
    
    function profile()
    {
        $nUserID = $this->security->getUserID();
        if(!$nUserID) func::JSRedirect(SITEURL);
                              
        $aData = $this->db->one_array('SELECT * FROM '.TABLE_USERS.' WHERE user_id='.$nUserID);
          
//        if($aData['blocked'])
//            return $this->showForbidden('Aккаунт заблокирован', 'Аккаунт заблокирован');

//        if($aData['deleted'])
//            return $this->showForbidden('Aккаунт удален', 'Аккаунт удален');
        
        if(bff::$isAjax)
        {
            switch(func::POST('act'))
            {
                case 'info': {
                    $info = $this->input->postm(array(
                        'name'=>TYPE_NOHTML,
                        'email'=>TYPE_NOHTML,
                        'phone'=>TYPE_NOHTML,  
                        'contacts'=>TYPE_ARRAY,
                    ));
                    
                    $info['contacts'] = $this->prepareUserContacts( $info['contacts'] );
                    
                    $this->db->execute('UPDATE '.TABLE_USERS.'  
                                        SET name=?, email2 = ?, phone = ?, contacts = ? '."WHERE user_id=$nUserID AND member=1", 
                                        array( $info['name'], $info['email'], $info['phone'], $info['contacts'] ) );

                    $this->security->updateUserInfo(array(
                              'contacts' => array('name'=>$info['name'], 'email2'=>$info['email'], 'phone'=>$info['phone'], 'other'=>unserialize($info['contacts'])), 
                              )
                    );                    
                                        
                    $this->ajaxResponse(Errors::SUCCESS); 
                } break;                   
                case 'pass': {
                     $this->input->postm(array(
                        'old'=>TYPE_STR,
                        'new'=>TYPE_STR,
                    ), $aData);

                    # проверяем указанный текущий пароль на корректность
                    if( $this->security->getUserPasswordMD5($aData['old']) != $aData['password'] ) {
                        $this->errors->set('Текущий пароль указан некорректно');
                    }

                    if(empty($aData['new']) || mb_strlen($aData['new'])<3 ) {
                        $this->errors->set('password_short');
                    }

                    if($this->errors->no()) 
                    {
                        $this->db->execute('UPDATE '.TABLE_USERS.'  
                                            SET password = ? '." WHERE user_id=$nUserID AND member=1", 
                                            array( $this->security->getUserPasswordMD5($aData['new']) ) 
                                           );
                                            
                        $this->ajaxResponse(Errors::SUCCESS);
                    }                        
                } break;
                case 'email': 
                {
                    $sPass  = $this->input->post('pass', TYPE_STR);
                    $sEmail = $this->input->post('email', TYPE_STR);
                    if(!func::IsEmailAddress($aData['email'])) {
                         $this->errors->set('wrong:email'); break; //email не корректный
                    }
                    
                    if($sEmail == $this->security->getUserEmail()) {
                        $this->ajaxResponse(Errors::SUCCESS);
                    }
                    
                    if( $this->security->getUserPasswordMD5($sPass) !== $aData['password']) {
                        $this->errors->set('Текущий пароль указан некорректно'); break;
                    }
                    
                    $nEmailHash = func::getEmailHash($sEmail);
                    
                    if($this->isEmailExists( $nEmailHash, $nUserID )) {
                        $this->errors->set('email_exist'); break; //email уже занят
                    }
                    
                    $this->db->execute('UPDATE '.TABLE_USERS.'  
                                        SET login = ?, email = ?, email_hash = ? '." WHERE user_id=$nUserID AND member=1", 
                                        array( $sEmail, $sEmail, $nEmailHash ) ); 
                                        
                    $this->ajaxResponse(Errors::SUCCESS);
                } break;
            }
            
            $this->ajaxResponse(null);
        }
        
        if(!empty($aData['contacts'])) {
            $aData['contacts'] = unserialize( $aData['contacts'] );
            if($aData['contacts'] === false) $aData['contacts'] = array();
        } else {
            $aData['contacts'] = array();
        }
        
        config::set('userMenuCurrent', 4);
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('profile.tpl');
    }
    
    function forgot()
    {
        $nUserID = $this->security->getUserID();
        if($nUserID) func::JSRedirect('/user/profile');
        
        if(bff::$isAjax)
        {
            switch(func::POST('act'))
            {
                case 'reg': {
                    $aData = $this->input->postm(array(
                        'email'=> TYPE_STR,
                        'pass' => TYPE_STR,  
                    ));
                    
                    if(empty($aData['pass']) || strlen($aData['pass'])<3) { 
                         $this->errors->set('password_short');  break; //пароль слишком короткий   
                    }

                    if($this->security->checkBan(false, func::getRemoteAddress(), $aData['email'], true)) {
                        $this->errors->set(Errors::ACCESSDENIED); break; //не прошли бан-фильтр
                    }                     
                    
                    $aData['email_hash'] = func::getEmailHash($aData['email']);
                    
                    if($this->isEmailExists( $aData['email_hash'] )) {
                        $this->errors->set('email_exist'); break; //email уже занят
                    }
                    
                    $this->getActivationInfo($sCode, $sLink);
                    
                    $nUserID = $this->userCreate(array('login'=>$aData['email'], 'email'=>$aData['email'], 'email_hash'=>$aData['email_hash'], 'password'=>$aData['pass'], 
                        'ip_reg'=>Func::getRemoteAddress(true), 'activatekey'=>$sCode, 'activated'=>0), 
                        self::GROUPID_MEMBER);
                    if($nUserID) {                                                                                
                        $res = bff::sendMailTemplate(array('password'=>$aData['pass'], 'email'=>$aData['email'], 'activate_link'=>"<a href=\"$sLink\">$sLink</a>"), 
                            'member_registration', $aData['email']);  

                        $this->ajaxResponse(Errors::SUCCESS);
                    }     
                                    
                } break;
                case 'forgot': {
                    $sEmail = $this->input->post('email', TYPE_STR);
                    if(!func::IsEmailAddress($aData['email'])) {
                         $this->errors->set('wrong:email'); break; //email не корректный
                    }
                    
                    $nEmailHash = func::getEmailHash($sEmail);
                    
                    if(!$this->isEmailExists( $nEmailHash )) {
                        $this->errors->set('email_not_found'); break; //email не корректный (не нашли)
                    }
                    
                    $aData = $this->db->one_array('SELECT user_id, email, name FROM '.TABLE_USERS.' WHERE email_hash='.$nEmailHash.' AND member=1 AND activated = 1 AND blocked = 0'); 
                    if(empty($aData)) {
                        $this->errors->set('email_not_found'); break; //email не корректный (не нашли)
                    }

                    $this->getPassRecoverInfo($sCode, $sLink); 
                    $this->db->execute('UPDATE '.TABLE_USERS.' SET activatekey = '.$this->db->str2sql($sCode).'  WHERE user_id = '.$aData['user_id']);
                    
                    $aData['password_link'] = "<a href=\"$sLink\">$sLink</a>";
                    
                    $res = bff::sendMailTemplate($aData,  'member_passforgot', $sEmail);  
                                        
                    $this->ajaxResponse(Errors::SUCCESS);
                    
                } break;
            }
            
            $this->ajaxResponse(null);
        }
        
        return $this->tplFetch('forgot.tpl');
    }
    
    function forgotpass()
    {
        if(bff::$isAjax)      
        {
            switch(func::POST('act'))
            {
                case 'changepass':
                {
                    $p = $this->input->postm(array(
                        'c'    => TYPE_STR,
                        'pass' => TYPE_STR,
                        'uid'  => TYPE_UINT,
                    ));
                    
                    if(empty($p['c']) || strlen($p['c'])!=10 || !$p['uid'])
                        $this->ajaxResponse( Errors::IMPOSSIBLE );

                    if(empty($p['pass']) || strlen($p['pass'])<3) { 
                         $this->errors->set('password_short');  //пароль слишком короткий   
                         $this->ajaxResponse(null);
                    }
                        
                    $aUserData = $this->db->one_array('SELECT user_id, activated, email FROM '.TABLE_USERS.' 
                                                    WHERE activatekey='.$this->db->str2sql($p['c']).' AND user_id = '.$p['uid'].' AND activated = 1 
                                                    LIMIT 1');

                    if(empty($aUserData)) {
                        $this->errors->set('password_link_not_valid');
                        $this->ajaxResponse(null);
                    } else {
                        $this->db->execute('UPDATE '.TABLE_USERS.' 
                                            SET activatekey = '.$this->db->str2sql('').',
                                                password = '.$this->db->str2sql( $this->security->getUserPasswordMD5( $p['pass'] ) ).'
                                            WHERE user_id = '.$p['uid']);
                        $this->ajaxResponse( Errors::SUCCESSFULL );
                    }
                    
                } break; 
            } 
            $this->ajaxResponse( Errors::IMPOSSIBLE );
        }
        
        $sCode = $this->input->get('c', TYPE_STR);         
        $aUserData = array('user_id'=>0);
        
        do
        {
            if(empty($sCode) || strlen($sCode)!=10) {
                $this->errors->set('password_link_not_valid');
                break;
            }
            
            $aUserData = $this->db->one_array('SELECT user_id, email FROM '.TABLE_USERS.' 
                                            WHERE activatekey='.$this->db->str2sql($sCode).' AND activated = 1 
                                            LIMIT 1');
                                          
            if(empty($aUserData)) {
                $this->errors->set('password_link_not_valid');
                break;
            }

            
        } while(false);
        
        $aUserData['c'] = $sCode;
                                
        $this->errors->assign();                           
        $this->tplAssign('forgotData', $aUserData);
        return $this->tplFetch('forgotpass.tpl');
    }
    
    # ============================================================================
    # ajax
    
    function ajax()
    {
        if(bff::$isAjax)      
        {
            switch(func::GETPOST('act'))
            {
                case 'subscribe': //ok
                {          
                    /*
                    * При подписке:
                    * - email выступает в дальнейшем в качестве логина
                    * - пароль генерируется автоматически
                    */
                    $sName  = $this->input->post('name', TYPE_NOHTML);
                    $sEmail = mb_strtolower( $this->input->post('email', TYPE_NOHTML) );
                    
                    $response = '';
                    do
                    {
                        if(empty($sEmail) || !func::IsEmailAddress($sEmail)) {
                            $response = 0; break; // некорректно указан email
                        }

                        $isSubscribed = $this->db->one_data('SELECT user_id FROM '.TABLE_USERS.' WHERE login='.$this->db->str2sql($sEmail));
                        if($isSubscribed) {
                            $response = 2; break; // данный email уже подписан
                        }
                        
                        $sPassword = func::generator(); 
                        
                        //'email' - для рассылки, 'login' - для авторизации
                        $nUserID = $this->userCreate(array('login'=>$sEmail, 'email'=>$sEmail, 'password'=>$sPassword, 'name'=>$sName, 'subscribed'=>1,
                                                           'ip_reg'=> func::getRemoteAddress(true)), self::GROUPID_MEMBER);
                        if($nUserID) {
                            $response = 1; // успешно подписались
                            
                            # высылаем письмо (ставим в очередь на рассылку)
                            CMail::SendQueue('subscribe', array('user_id'=>$nUserID));
                        } else {
                            $response = 4; // системная ошибка
                        }
                    
                    } while(false);
                    
                    $this->ajaxResponse(array('result'=>$response));
                } break;    
                
                case 'enter': //ok
                {
                    if($this->security->isLogined())
                        $this->ajaxResponse( array('result'=>'login-ok') );
                                            
                    $aData = $this->input->postm(array(
                        'email'=> TYPE_STR,
                        'pass' => TYPE_STR,
                        'reg'  => TYPE_BOOL,
                        ));

                    if(!func::IsEmailAddress($aData['email'])) {
                         $this->errors->set('wrong:email'); break; //email не корректный
                    }
                    
                    if($this->security->checkBan(false, func::getRemoteAddress(), $aData['email'], true)) {
                        $this->errors->set(Errors::ACCESSDENIED); break; //не прошли бан-фильтр
                    } 
                        
                    if($aData['reg']) 
                    {                       
                        //регистрация
                        if(empty($aData['pass']) || strlen($aData['pass'])<3) { 
                             $this->errors->set('password_short');  break; //пароль слишком короткий   
                        }
                        
                        $aData['email_hash'] = func::getEmailHash($aData['email']);
                        
                        if($this->isEmailExists( $aData['email_hash'] )) {
                            $this->errors->set('email_exist'); break; //email уже занят
                        }
                        
                        $this->getActivationInfo($sCode, $sLink);
                        
                        $nUserID = $this->userCreate(array('login'=>$aData['email'], 'email'=>$aData['email'], 'email_hash'=>$aData['email_hash'], 'password'=>$aData['pass'], 
                            'ip_reg'=>Func::getRemoteAddress(true), 'activatekey'=>$sCode, 'activated'=>0), 
                            self::GROUPID_MEMBER);
                        if($nUserID) {
                            //$this->userAUTH($aData['email'], $aData['pass'], null, true);
                            
                            $res = bff::sendMailTemplate(array(
                                'password'=>$aData['pass'], 'email'=>$aData['email'], 'activate_link'=>"<a href=\"$sLink\">$sLink</a>"), 
                                'member_registration', $aData['email']);  

                            $this->ajaxResponse(array('result'=>'reg-ok'));
                        } else {
                            $this->ajaxResponse(Errors::IMPOSSIBLE);
                        }

                    } else {
                        //авторизация
                        $nResult = $this->userAUTH($aData['email'], $aData['pass'], null, true);
                        if($nResult == 1) {    
                            //$this->security->setRememberMe('u', $aData['email'], $aData['pass']);
                            
                            bff::i()->Bbs_getFavorites(true);
                            
                            $bReload = false;
                            if(!empty($_SERVER['HTTP_REFERER'])) {
                                if(stripos($_SERVER['HTTP_REFERER'], '/item/')!==FALSE || 
                                   stripos($_SERVER['HTTP_REFERER'], '/items/fav')!==FALSE ) {
                                    $bReload = true;
                                }
                            }
                            
                            $userMenu = $this->tplFetch('user.menu.tpl');
                            $this->ajaxResponse(array('result'=>'login-ok', 'usermenu'=>$userMenu, 'reload'=>$bReload));
                        } else {             
                            $mResponse = null;
                            switch($nResult) {                                
                                case 0:  { $this->errors->set('email_or_pass_incorrect'); } break;
                                case -3: { $this->errors->set('activate_first'); } break; //активируйте ваш аккаунт
                                case -2: { $this->errors->set(Errors::ACCESSDENIED); } break; //удален
                            }
                            if(is_array($nResult)) {
                                if($nResult['res'] == -1)
                                {
                                     $this->errors->set('Аккаунт заблокирован.'.(!empty($nResult['reason'])?' <br/><b>Причина:</b>'.nl2br($nResult['reason']):''));
                                }
                            }
                        }
                    }                             

                } break;
            }
        }
        $this->ajaxResponse( null );
    }

}
