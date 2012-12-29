<?php

require PATH_CORE.'modules/sendmail/sendmail.adm.class.php';

class Sendmail extends SendmailAdm
{
    const SENDSPAM_FROM = 'noreply@bff.ru';
    const SENDSPAM_SUBSCRIBED_USERS = 0;
    
    //---------------------------------------------------------------
    // рассылка писем
    
    function massend()
    {
        if( !$this->haveAccessTo('massend-clients') )
            return $this->showAccessDenied();
                           
        $aData = array('receivers'=>'', 'subject'=>'', 'body'=>'');

        $aReceivers = func::POSTGET('receivers', false);
        if(is_array($aReceivers)) $aReceivers = array_unique($aReceivers);

        $this->tplAssign('aData', array_merge( $this->getReceiversOptions(true, false, 1, true), $aData) );        
        return $this->tplFetch('admin.massend.tpl');
    }
    
    function ajax()
    {
        if(!$this->haveAccessTo('massend-clients'))
            $this->ajaxResponse(Errors::ACCESSDENIED);

        if(bff::$isAjax)
        {
            switch(func::POSTGET('action'))
            {
                case 'massend-filter':
                {   
                    $bClients    = (func::POSTGET('clients')   ? 1 : 0);
                    $bEmployees  = (func::POSTGET('employees') ? 1 : 0);
                    $bUsers      = (func::POSTGET('users')     ? 1 : 0);
                    $bUsers      = (func::POSTGET('usersall') && $bUsers ? 2 : $bUsers);
                    $bExludeBlocked = (func::POSTGET('blocked')   ? 1 : 0);
                    
                    $this->ajaxResponse( $this->getReceiversOptions($bClients, $bEmployees, $bUsers, $bExludeBlocked) );
                }break;
                case 'massend':
                {
                    $bClients   = (func::POSTGET('clients')  ? 1 : 0);
                    $bEmployees = (func::POSTGET('employees')? 1 : 0);
                    $bUsers     = (func::POSTGET('users')    ? 1 : 0);
                    $aReceivers = $this->getReceiversInfo(($bClients || $bEmployees), $bUsers, func::POST('receivers', false) );
                    
                    $sFrom     = func::POST('from', true);
                    $sSubject  = func::POST('subject', true);
                    $sBodyHTML = func::POST('body', true);
                    
                    if(false && count($aReceivers)>0 && !empty($sFrom) && !empty($sSubject) && !empty($sBodyHTML))
                    {                                                                         
                        $mailer = new CMail();
                        
                        $mailer->From    = $sFrom;
                        $mailer->Subject = $sSubject;
                        
                        $mailer->MsgHTML($sBodyHTML);
                        
                        //определяем какой текст требует обработки шаблонизатором   
                        $aTemplateVars = array('{name}', '{login}', '{email}', '{password}');                        
                        str_replace($aTemplateVars, '', $sFrom, $bPrepareFrom );  
                        str_replace($aTemplateVars, '', $sSubject, $bPrepareSubject );
                        str_replace($aTemplateVars, '', $sBodyHTML, $bPrepareBodyHTML );
                        
                        foreach($aReceivers as $receiver) 
                        {
                            set_time_limit(30); // sets (or resets) maximum  execution time to 30 seconds)

                            $mailer->AddAddress($receiver['email'], $receiver['name']);
                            
                            $aTemplateData = array(
                                '{name}'     => $receiver['name'],
                                '{login}'    => $receiver['login'],
                                '{email}'    => $receiver['email'],
                                '{password}' => $receiver['password']);
                            
                            //prepare FROM 
                            if($bPrepareFrom)
                                $mailer->From = strtr($sFrom, $aTemplateData);

                            //prepare SUBJECT 
                            if($bPrepareSubject)
                                $mailer->Subject = strtr($sSubject, $aTemplateData);
                            
                            //prepare BODY-HTML 
                            if($bPrepareBodyHTML)
                                $mailer->MsgHTML( strtr($sBodyHTML, $aTemplateData) );

                            $mailer->Send();

                            $mailer->ClearAddresses();
                                 
                            usleep(1000000); // sleep for 1 second 
                        }                        
                    }
                    
                    $this->ajaxResponse($aReceivers);
                    
                }break;
            }
        }
        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
    
    //---------------------------------------------------------------
    // рассылка писем 'пользователям'
    function users_sendspam()
    {
        if( !$this->haveAccessTo('sendspam-users') )
            return $this->showAccessDenied();

        $aData = array(); 
        
        $aSelectedUsers = func::POSTGET('user_id', false);
        if(is_array($aSelectedUsers))
            $aSelectedUsers = array_unique($aSelectedUsers);
       
        $sFrom = self::SENDSPAM_FROM;
       
        if(func::isPostMethod() || func::POSTGET('submit')=='send')
        {
            $sSubject = func::POSTGET('subject', true);
            $sBody = func::POSTGET('body', true);
            if(!$sSubject) $this->errors->set('no_subject');
            if(!$sBody) $this->errors->set('no_body');

            if(!is_array($aSelectedUsers) || count($aSelectedUsers)==0)
                $this->errors->set('no_receivers');
                
            if($this->errors->no())
            {
                //get users
                $sQueryAdd = '';
                if(isset($aSelectedUsers))
                    $sQueryAdd = ' WHERE id IN ('.implode(',', $aSelectedUsers).') ';
                if(self::SENDSPAM_SUBSCRIBED_USERS)
                    $sQueryAdd = ' WHERE subscribed=1 ';
                    
                $aUsers = $this->db->select('SELECT * FROM '.TABLE_USERS.' '.$sQueryAdd.' ORDER BY login');
                
                include (PATH_CORE.'mail/htmlMimeMail.php');
                @set_time_limit(0);
                $nCountSuccessfull = 0;
                $nCountUnsuccessfull = 0;
                $aUsersSuccessfull = array();

                $oMail = new htmlMimeMail(); 
                $oMail->setHeadCharset('UTF-8');
                $oMail->setTextCharset('UTF-8');
                $oMail->setSubject($sSubject);
                $oMail->setFrom($sFrom);
                $oMail->setText($sBody);
                                    
                for ($i=0; $i<count($aUsers); $i++)
                {
                    if( @$oMail->send(array($aUsers[$i]['email'])) )
                    {
                        $nCountSuccessfull++;
                        $aUsersSuccessfull[] = $aUsers[$i]['id'];
                    }
                    else $nCountUnsuccessfull++;
                }

                if($nCountUnsuccessfull==0)
                {
                    $this->adminRedirect(Errors::SUCCESSFULL, 'users_sendspam');
                }
                else {
                    $this->errors->set('result_of_sendmail', false, 'успешно - '.$nCountSuccessfull.' / с ошибками - '.$nCountUnsuccessfull);
                    //leave only unsuccessfull users :)                                 
                    $aSelectedUsers = array_diff($aSelectedUsers, $aUsersSuccessfull);
                }

            }
            $aData = $_REQUEST;
        }
        
        $aUsers = $this->db->select('SELECT id, email, login FROM '.TABLE_USERS.' '.(self::SENDSPAM_SUBSCRIBED_USERS?' WHERE subscribed=1 ':'').' ORDER BY login');
        
        $htmlExistsOptions = '';
        $htmlReceiversOptions = '';
        for ($i=0; $i<count($aUsers); $i++)
        {
            if (!$aUsers[$i]['email']) continue;
            $htmlItem = '<option value="'.$aUsers[$i]['id'].'">'.$aUsers[$i]['login'].' &lt;'.$aUsers[$i]['email'].'&gt; </option>';
            if(is_array($aSelectedUsers) && in_array($aUsers[$i]['id'], $aSelectedUsers))
            {
                $htmlReceiversOptions .=$htmlItem;
            }
            else $htmlExistsOptions .=$htmlItem;
        }
        
        $aData['from'] = $sFrom;         
        $this->tplAssign('aData', $aData);
        $this->tplAssign("exists_values", $htmlExistsOptions);
        $this->tplAssign("sendtousers_options", $htmlReceiversOptions);
        
        return $this->tplFetch('admin.users.sendspam.tpl');
    }
    
    //---------------------------------------------------------------
    // рассылка писем 'подписавшимся'
    function subscriber_sendspam()
    {
        if( !$this->haveAccessTo('sendspam-subscribers') )
            return $this->showAccessDenied();

        $aData = array(); 
        
        $aSelectedEmails = func::POSTGET('user_id', false);
        if(is_array($aSelectedEmails))
            $aSelectedEmails = array_unique($aSelectedEmails );
       
        $sFrom = self::SENDSPAM_FROM;
       
        if(func::isPostMethod() || func::POSTGET('submit')=='send')
        {
            $sSubject = func::POSTGET('subject', true);
            $sBody = func::POSTGET('body', true);
            if(!$sSubject) $this->errors->set('no_subject');
            if(!$sBody)    $this->errors->set('no_body');

            if(!is_array($aSelectedEmails) || count($aSelectedEmails)==0)
                $this->errors->set('no_receivers');
                
            if($this->errors->no())
            {
                include (PATH_CORE.'mail/htmlMimeMail.php');
                @set_time_limit(0);
                $nCountSuccessfull = 0;
                $nCountUnsuccessfull = 0;
                $aEmailsSuccessfull = array();

                $oMail = new htmlMimeMail(); 
                $oMail->setSubject($sSubject);
                $oMail->setFrom($sFrom);
                $oMail->setText($sBody);
                                    
                for ($i=0; $i<count($aSelectedEmails); $i++)
                {
                    if( @$oMail->send(array($aSelectedEmails[$i])) )
                    {
                        $nCountSuccessfull++;
                        $aEmailsSuccessfull[] = $aSelectedEmails[$i];
                    }
                    else $nCountUnsuccessfull++;
                }

                if($nCountUnsuccessfull==0)
                {
                    $this->adminRedirect(Errors::SUCCESSFULL, 'subscriber_sendspam');
                }
                else {
                    $this->errors->set('result_of_sendmail', false, 'успешно - '.$nCountSuccessfull.' / с ошибками - '.$nCountUnsuccessfull);
                    //leave only unsuccessfull emails :)                                 
                    $aSelectedEmails = array_diff($aSelectedEmails, $aEmailsSuccessfull);
                }

            }
            $aData = $_REQUEST;
        }
        
        $aSubscribers = $this->db->select('SELECT id, email, name FROM '.DB_PREFIX.'subscribers ORDER BY name');
        
        $htmlExistsOptions = '';
        $htmlReceiversOptions = '';
        for ($i=0; $i<count($aSubscribers); $i++)
        {
            if (!$aSubscribers[$i]['email']) continue;
            $htmlItem = '<option value="'.$aSubscribers[$i]['email'].'">'.$aSubscribers[$i]['name'].' &lt;'.$aSubscribers[$i]['email'].'&gt; </option>';
            if(is_array($aSelectedEmails) && in_array($aSubscribers[$i]['email'], $aSelectedEmails))
            {
                $htmlReceiversOptions .=$htmlItem;
            }
            else $htmlExistsOptions .=$htmlItem;
        }
        
        $aData['from'] = $sFrom;         
        $this->tplAssign('aData', $aData);
        $this->tplAssign("exists_values", $htmlExistsOptions);
        $this->tplAssign("sendtousers_options", $htmlReceiversOptions);
        return $this->tplFetch('admin.subscriber.sendspam.tpl');
    }
    
    function subscriber_listing()
    {
        if( !$this->haveAccessTo('subscribers-listing') )
            return $this->showAccessDenied();

        // prepare order 
        $this->prepareOrder($orderBy, $orderDirection, 'name,desc');

        //generate pagenation
        $nCount = $this->db->one_data('SELECT count(id) from '.DB_PREFIX.'subscribers');    
        $this->generatePagenation($nCount, 20, $this->adminCreateLink("subscriber_listing&order=$orderBy,$orderDirection&{pageId}"), $sSqlLimit);

        $sAdd2Query = " ORDER BY $orderBy $orderDirection"; 
        if(!empty($sSqlLimit)) $sAdd2Query .=$sSqlLimit;  
                    
        //get all subscribed
        $aData = $this->db->select('SELECT * FROM '.DB_PREFIX.'subscribers '.$sAdd2Query);               

        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.subscriber.listing.tpl');
    }

    function subscriber_add()
    {
        if( !$this->haveAccessTo('subscribers-edit') )
            return $this->showAccessDenied();
        
        $aData = array();        
        if(func::isPostMethod())
        {
            $sName = $aData['name'] = func::POST('name');
            $sEmail = $aData['email'] = func::POST('email');
            
            if(!$sName) 
                $this->errors->set('no_subscriber_name');
                
            if(!$sEmail) 
                $this->errors->set('no_subscriber_email');        
            elseif (!func::IsEmailAddress($sEmail))
                $this->errors->set('subscriber_wrong_email');
            elseif($this->isSubscribed($sEmail))
                $this->errors->set('subscriber_email_exists');

            if($this->errors->no())
            {
                $this->db->execute('INSERT INTO '.DB_PREFIX.'subscribers (name, email, create_datetime)
                               VALUES ('.$this->db->str2sql($sName).', '.$this->db->str2sql($sEmail).', '.$this->db->getNOW().')');

                $this->adminRedirect(Errors::SUCCESSFULL, 'subscriber_listing');
            }
        }

        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.subscriber.form.tpl');
    }

    function subscriber_edit()
    {
        if( !$this->haveAccessTo('subscribers-edit') )
            return $this->showAccessDenied();
            
        $nRecordID = func::POSTGET('rec', false, true);
        if(!$nRecordID) $this->adminRedirect(Errors::IMPOSSIBLE, 'subscriber_listing');

        $aData = $this->db->one_array('SELECT * FROM '.DB_PREFIX.'subscribers WHERE id='.$nRecordID.' LIMIT 1');
        if(!$aData) $this->adminRedirect(Errors::IMPOSSIBLE, 'subscriber_listing'); 
        
        if(func::isPostMethod())
        {
            $sName = $aData['name'] = func::POST('name');
            $sEmail = $aData['email'] = func::POST('email');
            
            if(!$sName) 
                $aErrors[] = $this->errors->set('no_subscriber_name');
                
            if(!$sEmail) 
                $this->errors->set('no_subscriber_email');        
            elseif (!func::IsEmailAddress($sEmail))
                $this->errors->set('subscriber_wrong_email');
            elseif($aData['email']!=$sEmail && $this->isSubscribed($sEmail))
                $this->errors->set('subscriber_email_exists');

            if($this->errors->no())
            {
                $this->db->execute('UPDATE '.DB_PREFIX.'subscribers
                               SET name='.$this->db->str2sql($sName).', email='.$this->db->str2sql($sEmail).', create_datetime='.$this->db->getNOW().'
                               WHERE id='.$nRecordID);

                $this->adminRedirect(Errors::SUCCESSFULL, 'subscriber_listing');
            }
        }

        $this->tplAssign('rec', $nRecordID);
        $this->tplAssign('aData', $aData);
        return $this->tplFetch('admin.subscriber.form.tpl');
    }

    function subscriber_delete()
    {
        if( !$this->haveAccessTo('subscribers-edit') )
            return $this->showAccessDenied();
        
        $nRecordID = func::POSTGET('rec', false, true);
        if(!$nRecordID) $this->adminRedirect(Errors::IMPOSSIBLE, 'subscriber_listing');

        $this->db->execute('DELETE FROM '.DB_PREFIX.'subscribers WHERE id='.$nRecordID);

        $this->adminRedirect(Errors::SUCCESSFULL, 'subscriber_listing');
    }
}

