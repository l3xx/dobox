<?php

class InternalMail extends InternalMailBase
{
    //список сообщений папки                      
    function listing()
    {
        if(!$this->haveAccessTo('read'))
            return $this->showAccessDenied();
        
        $nUserID = $this->security->getUserID();

        if(($nFolderID = func::GETPOST('f', false, true))<=0)
           $nFolderID = INTERNALMAIL_FOLDER_ALL;
        
        $aData = array('f'=>$nFolderID);         
        if(func::isPostMethod())
        {
            switch(func::POST('act'))
            {
                case 'send':
                {
                    $aData['recipient'] = func::POST('recipient', false, true);
                    if(!$aData['recipient']) $this->errors->set('no_recipient'); 
                    
                    $aData['message'] = $this->cleanMessage( func::POST('message') );
                    if(!$aData['message']) $this->errors->set('no_message');
                        
                    if($this->errors->no())
                    {                                      
                        $this->sendMessage($nUserID, $aData['recipient'], nl2br($aData['message']),
                                    $this->uploadAttachment(), $this->db->getNOW());

                        $this->adminRedirect(Errors::SUCCESSFULL, 'listing&f='.$nFolderID);  
                    }
                } break;
            }   
        }

        $nLimit = 15;
        $aData['offset'] = func::GET('offset', false, true);
        if($aData['offset']<=0) $aData['offset'] = 0;

        $sQuery = 'SELECT U.user_id, U.name, U.login, U.avatar, U.admin,
                          COUNT(IM.id) AS msgs_count, 
                          SUM( (IM.recipient='.$nUserID.' AND !(IM.status & '.INTERNALMAIL_STATUS_NEW.')) ) AS newmsgs, 
                          MAX(IM.id) AS lastmsg_id, 
                          MAX(IM.created) AS last 
                   FROM '.TABLE_INTERNALMAIL.' IM, '.TABLE_USERS.' U 
                        LEFT JOIN '.TABLE_INTERNALMAIL_FOLDERS_USERS.' IMFU 
                            ON IMFU.user_id='.$nUserID.' AND IMFU.interlocutor_id=U.user_id '.(!$nFolderID ? ' AND IMFU.folder_id='.INTERNALMAIL_FOLDER_IGNORE.' ' : '').'
                   WHERE ((IM.author='.$nUserID.' AND !(IM.status & '.(INTERNALMAIL_STATUS_DELAUTHOR).')) OR 
                          (IM.recipient='.$nUserID.' AND !(IM.status & '.(INTERNALMAIL_STATUS_DELRECIPIENT).')) )  AND 
                         U.user_id = IF(IM.author = '.$nUserID.', IM.recipient, IM.author) AND U.blocked = 0
        '.(!$nFolderID ? ' AND IMFU.folder_id IS NULL ' : 'AND IMFU.folder_id='.$nFolderID).'
                   GROUP BY 1  
                   ORDER BY last DESC'.$this->db->prepareLimit($aData['offset'], $nLimit+1);
        $aData['contacts'] = $this->db->select($sQuery);
        
        if(!empty($aData['contacts'])) {
            $aContacts = array();
            $aLastMessageID = array();
            foreach($aData['contacts'] as $v){  
                $aContacts[$v['user_id']] = $v;
                $aContacts[$v['user_id']]['folders'] = array(); 
                $aLastMessageID[] = $v['lastmsg_id'];
            }
            
            $aUsersFolders = $this->db->select('SELECT folder_id as f, interlocutor_id as id FROM '.TABLE_INTERNALMAIL_FOLDERS_USERS.'
                WHERE user_id = '.$nUserID.' AND interlocutor_id IN('.implode(',', array_keys($aContacts)).') ');
            foreach($aUsersFolders as $v){
                $aContacts[$v['id']]['folders'][] = $v['f'];
            }
            
            $aLastMessageID = $this->db->select('SELECT id, author, recipient, status, created, readed, !(status & '.INTERNALMAIL_STATUS_NEW.') as newmsg 
                        FROM '.TABLE_INTERNALMAIL.' WHERE id IN ('.implode(',',$aLastMessageID).')');
            foreach($aLastMessageID as $m){
                $aContacts[ ( $m['author']==$nUserID ? $m['recipient'] : $m['author'] ) ]['lastmsg'] = $m;
            }
            
            $aData['contacts'] = $aContacts; unset($aContacts, $aLastMessageID, $aUsersFolders);
        }  
        
        //generate pagenation: prev, next
        $this->generatePagenationPrevNext(null, $aData, 'contacts', $nLimit);

        $aData['folders'] = $this->getFolders();
          
        $this->adminCustomCenterArea();
        $this->includeJS('autocomplete'); 
        $this->includeCSS('im'); 
        $this->tplAssignByRef('aData', $aData);  
        return $this->tplFetch('admin.listing.folders.tpl');
    }  
	
    //просмотр переписки
    function conversation()
    {
        if(!$this->haveAccessTo('read'))
            return $this->showAccessDenied();
    
        $mFolder = INTERNALMAIL_FOLDER_ALL;
        $nUserID = $this->security->getUserID(); 
        if(($nInterlocutorID = func::GETPOST('i',0,1))<=0)
            $this->adminRedirect(Errors::IMPOSSIBLE, 'listing&f='.$mFolder);
        
        $aData = array('f'=>$mFolder, 'iid'=>$nInterlocutorID, 'uadmin'=>$this->security->isAdmin());
        if(func::isPostMethod() && !bff::$isAjax)
        {
            $aData['message'] = $this->cleanMessage( func::POST('message') );
            if(!$aData['message']) $this->errors->set('no_message');
                
            if($this->errors->no())
            {
                $this->sendMessage($nUserID, $nInterlocutorID, nl2br($aData['message']), 
                            $this->uploadAttachment(), $this->db->getNOW());

                $this->adminRedirect(Errors::SUCCESSFULL, 'listing&f='.$mFolder);  
            }   
        }
        
        $nTotal = $this->db->one_data('SELECT COUNT(IM.id) FROM '.TABLE_INTERNALMAIL.' IM 
                    WHERE (IM.author='.$nUserID.' AND IM.recipient='.$nInterlocutorID.' AND !(IM.status & '.(INTERNALMAIL_STATUS_DELAUTHOR).') ) OR 
                          (IM.author='.$nInterlocutorID.' AND IM.recipient='.$nUserID.' AND !(IM.status & '.(INTERNALMAIL_STATUS_DELRECIPIENT).') )');
        $this->generatePagenation($nTotal, 15, 'doPage({pageId});', $sqlLimit, 'pagenation.ajax.tpl', 'page', true, $aPgnResult);
                
        $sQuery = 'SELECT IM.*, (IM.author='.$nUserID.') as my 
                   FROM '.TABLE_INTERNALMAIL.' IM
                   WHERE (IM.author='.$nUserID.' AND IM.recipient='.$nInterlocutorID.' AND !(IM.status & '.(INTERNALMAIL_STATUS_DELAUTHOR).') ) OR 
                         (IM.author='.$nInterlocutorID.' AND IM.recipient='.$nUserID.' AND !(IM.status & '.(INTERNALMAIL_STATUS_DELRECIPIENT).') ) 
                   ORDER BY IM.created DESC'.$sqlLimit;
        $aData['mess'] = $this->db->select($sQuery);
        
        $aData['i'] = $this->db->one_array('SELECT U.user_id, U.name, U.login, U.avatar, U.admin, U.im_noreply, U.blocked 
                                       FROM '.TABLE_USERS.' U WHERE U.user_id = '.$nInterlocutorID);
        $aData['i']['folders'] = $this->db->select_one_column('SELECT folder_id FROM '.TABLE_INTERNALMAIL_FOLDERS_USERS.'
                WHERE user_id = '.$nUserID.' AND interlocutor_id ='.$nInterlocutorID.' ');
        $aData['uname'] = $this->security->getUserInfo('name');
        
        $this->tplAssignByRef('aData', $aData); 
        
        if(bff::$isAjax) {
            $this->ajaxResponse( array( 
                'list'=> $this->tplFetch('admin.conversation.ajax.tpl'),
                'pages'=> ( !empty($aData['mess']) ? $this->tplAssigned('pagenation_template') : '<div id="pagenation"></div>')
                ) );
        }
        
        #отмечаем как прочитанные все новые сообщения, в которых юзер является получателем 
        $nUpdatedCnt = $this->db->execute('UPDATE '.TABLE_INTERNALMAIL.' SET status = (status | '.INTERNALMAIL_STATUS_NEW.'), readed = '.$this->db->getNOW().'
                       WHERE author='.$nInterlocutorID.' AND recipient='.$nUserID.' AND !(status & '.INTERNALMAIL_STATUS_NEW.')  ');
        if($nUpdatedCnt) {
            $this->updateNewMessagesCounter($nUserID, true);
        }
        
        $aData['ignored'] = $this->isIgnoredByInterlocutor($nUserID, $nInterlocutorID);
        $aData['total']  = $nTotal;
        
        $this->adminCustomCenterArea(); 
        $this->includeCSS('im');
        return $this->tplFetch('admin.conversation.tpl');
    }
    
    //листинг сообщений
    function listing_messages() //niu
    {
        if(!$this->haveAccessTo('read'))
            return $this->showAccessDenied();
        
        $nUserID = $this->security->getUserID();
        
        $mFolder = func::GETPOST('f'); 
        if($mFolder != INTERNALMAIL_INCOMING && $mFolder != INTERNALMAIL_OUTGOING)
           $mFolder = INTERNALMAIL_INCOMING;
        
        $aData = array('f'=>$mFolder);         
        if(func::isPostMethod())
        {
            switch(func::POST('act'))
            {
                case 'send':
                {
                    $aData['recipient'] = func::POST('recipient', false, true);
                    if(!$aData['recipient']) $this->errors->set('no_recipient'); 
                    
                    $aData['message'] = $this->cleanMessage( func::POST('message') );
                    if(!$aData['message']) $this->errors->set('no_message');
                        
                    if($this->errors->no())
                    {

                        $this->sendMessage($nUserID, $aData['recipient'], $aData['message'], $this->db->getNOW());

                        $this->adminRedirect(Errors::SUCCESSFULL, 'listing&f='.$mFolder);  
                    }
                } break;
                case 'massmsg':
                {
                    $aMessageID = func::POST('msg', false);
                    if(!empty($aMessageID) && is_array($aMessageID))
                    switch(func::POST('type'))
                    {
                        case 'read':
                        {   //отметить как прочитанные
                            $this->changeMessageNewStatus($aMessageID, $nUserID, false);
                        } break;
                        case 'new':
                        {   //отметить как непрочитанные (новые)
                            $this->changeMessageNewStatus($aMessageID, $nUserID, true);
                        } break;
                        case 'del':
                        {   //удалить 
                            $this->deleteMessage($aMessageID, $nUserID);
                        } break;
                    }

                    $this->adminRedirect(Errors::SUCCESSFULL, 'listing&f='.$mFolder);
                    
                } break;
            }   
        }
                    
        $nCount = $this->getMessagesCount($nUserID, $mFolder);
                                        
        $this->generatePagenation($nCount, 20, $this->adminCreateLink("listing&f=$mFolder&{pageId}"), $sSqlLimit);

        $aData['messages'] = $this->getMessagesListing($nUserID, $mFolder, $sSqlLimit);
        
        $this->tplAssignByRef('aData', $aData);    
        return $this->tplFetch('admin.listing.tpl');
    }  
    
    function read() //niu
    {
        if(!$this->haveAccessTo('read'))
            return $this->showAccessDenied();
        
        $nUserID = $this->security->getUserID();
                                            
        $mFolder = func::GETPOST('f'); 
        if($mFolder != INTERNALMAIL_INCOMING && $mFolder != INTERNALMAIL_OUTGOING)
           $mFolder = INTERNALMAIL_INCOMING;
        
        $nMessageID = func::GETPOST('mid'); 
        if(!$nMessageID)
           $this->adminRedirect(Errors::IMPOSSIBLE, 'listing&f='.$mFolder);
        
        $aData = array('f'=>$mFolder);         

        if(func::isPostMethod())
        {
            $nRecipientID = func::POST('iid', false, true);
            if(!$nRecipientID) $this->errors->set('no_recipient'); 

            $aData['message'] = $this->cleanMessage( func::POST('message') );
            if(!$aData['message']) $this->errors->set('no_message');
                
            if($this->errors->no())
            {
                $this->sendMessage($nUserID, $nRecipientID, $aData['message'], $this->db->getNOW());

                $this->adminRedirect(Errors::SUCCESSFULL, 'listing&f='.$mFolder);  
            }   
        }
        
        $aData['m'] = $this->getMessage($nMessageID, $nUserID, $mFolder);
        if(!$aData['m']) $this->adminRedirect(null, 'listing&f='.$mFolder);

        //если просматривает получатель и сообщение новое, отмечаем как прочитанное
        if($aData['m']['recipient'] == $nUserID && $aData['m']['new'])
            $this->changeMessageNewStatus($nMessageID, $nUserID, false);  
           
        $aData['uinfo'] = array( 'login' => $this->security->getUserLogin(), 'id' => $nUserID );
        
        $this->adminCustomCenterArea(); 
        $this->tplAssignByRef('aData', $aData);
        return $this->tplFetch('admin.read.tpl');
    } 
    
    function delete() //niu
    {
        if(!$this->haveAccessTo('read'))
            return $this->showAccessDenied();
                                            
        $mFolder = func::GETPOST('f'); 
        if($mFolder != INTERNALMAIL_INCOMING && $mFolder != INTERNALMAIL_OUTGOING)
           $mFolder = INTERNALMAIL_INCOMING;
        
        $nMessageID = func::GETPOST('mid'); 
        if(!$nMessageID)
           $this->adminRedirect(Errors::IMPOSSIBLE, 'listing&f='.$mFolder);

        $bResult = $this->deleteMessage($nMessageID, $this->security->getUserID());
        
        $this->adminRedirect( ($bResult ? Errors::SUCCESSFULL : Errors::IMPOSSIBLE) , 'listing&f='.$mFolder);
    } 
    
    function ajax()
    {
        if(!$this->haveAccessTo('read'))
            $this->ajaxResponse(Errors::ACCESSDENIED);

        if(bff::$isAjax)
        {   
            $nUserID = $this->security->getUserID();
            switch( func::POSTGET('action') )
            {
                case 'recipients': #autocomplete 
                {               
                    $sQ = func::POST('q', true);
                    //получаем список подходящих по логину собеседников, исключая
                    //- текущего пользователя
                    //- администраторов, запретивших им писать (im_noreply=1)
                    //- заблокированных пользователей
                    //- добавивших текущего пользователя в ignore-папку
                    $aResult = $this->db->select('SELECT U.user_id, U.login FROM '.TABLE_USERS.' U 
                                    LEFT JOIN '.TABLE_INTERNALMAIL_FOLDERS_USERS.' IMFU ON 
                                        U.user_id=IMFU.user_id AND IMFU.interlocutor_id = '.$nUserID.' AND IMFU.folder_id = '.INTERNALMAIL_FOLDER_IGNORE.'
                                  WHERE U.login LIKE ('.$this->db->str2sql("$sQ%").') AND U.user_id!='.$nUserID.' 
                                    AND U.im_noreply=0 AND U.blocked=0 AND IMFU.folder_id IS NULL
                                  ORDER BY U.login');
                    
                    $aUsers = array();
                    foreach($aResult as $u) {
                        $aUsers[$u['user_id']] = $u['login'];
                    } unset($aResult);
                                  
                    $this->ajaxResponse($aUsers);
                } break;
                case 'history': //niu
                {                                                            
                    if(!($nInterlocutorID = func::POSTGET('iid', false, true)))
                        $this->ajaxResponse(Errors::IMPOSSIBLE);

                    $aData = array('uid'=>$nUserID, 'ulogin'=>$this->security->getUserLogin());
                    
                    $aData['il'] = $this->db->one_array('SELECT user_id, login FROM '.TABLE_USERS.' WHERE id='.$nInterlocutorID.' LIMIT 1');
                    if(!$aData['il'])
                        $this->ajaxResponse(Errors::IMPOSSIBLE); 
                        
                    //получаем переписку с собеседником
                    $sQuery = 'SELECT id, author, message, status, created
                               FROM '.TABLE_INTERNALMAIL.'              
                               WHERE ((author='.$nInterlocutorID.' AND recipient='.$nUserID.' AND (status & '.INTERNALMAIL_STATUS_DELRECIPIENT.')=0 ) 
                                       OR (author='.$nUserID.' AND recipient='.$nInterlocutorID.' AND (status & '.INTERNALMAIL_STATUS_DELAUTHOR.')=0 )
                                     )
                               ORDER BY created DESC';     
                    $aData['messages'] = $this->db->select($sQuery);
                    $this->tplAssignByRef('aData', $aData);
                    $this->ajaxResponse(array('history'=>$this->tplFetch('admin.history.ajax.tpl')));
                    
                } break;
                case 'delete-conv':
                {
                    if(($nInterlocutorID = func::POSTGET('iid', false, true))<=0)
                        $this->ajaxResponse(Errors::IMPOSSIBLE);
                    
                    $nDeleted = ($this->deleteConversation($nUserID, $nInterlocutorID) ? 1 : 0);
                    
                    $this->ajaxResponse( $nDeleted );
                } break;
                case 'move2folder':
                {
                    if(($nInterlocutorID = func::POSTGET('iid', false, true))<=0)
                        $this->ajaxResponse(Errors::IMPOSSIBLE);
                        
                    if(($nFolderID = func::POSTGET('fid', false, true))<=0)
                        $this->ajaxResponse(Errors::IMPOSSIBLE);
                    
                    $aResponse['added'] = $this->moveUser2Folder($nUserID, $nInterlocutorID, $nFolderID);
                    
                    $this->ajaxResponse($aResponse);
                } break;
                case 'delete-msg':  //niu
                {
                    $nMessageID = func::POSTGET('rec', false, true);
                    if(!$nMessageID) $this->ajaxResponse(Errors::IMPOSSIBLE);
                    
                    $this->deleteMessage( $nMessageID, $nUserID);
                    
                    $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;
                case 'recover-msg':  //niu
                {
                    $nMessageID = func::POSTGET('rec', false, true);
                    if(!$nMessageID) $this->ajaxResponse(Errors::IMPOSSIBLE);
                    
                    $this->recoverMessage( $nMessageID, $nUserID);
                    
                    $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;
                case 'send-msg': 
                {
                    if(($nInterlocutorID = func::POSTGET('iid', false, true))<=0)
                        $this->ajaxResponse(Errors::IMPOSSIBLE);
                        
                    $aData['message'] = $this->cleanMessage( func::POST('message') );
                    if(!$aData['message']) $this->ajaxResponse(Errors::IMPOSSIBLE);

                    $this->sendMessage($nUserID, $nInterlocutorID, nl2br($aData['message']), 
                                $this->uploadAttachment(), $this->db->getNOW());  
                    
                    $this->ajaxResponse(Errors::SUCCESSFULL);
                } break;
            }
        }
            
        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
    
	function sendspam() //niu
	{
        if( !$this->haveAccessTo('admin-message') )
            return $this->showAccessDenied();

        //get sender information
        $nUserID = $this->security->getUserID();
        $aSenderInfo = bff::i()->Users_getUserInfo($nUserID, false);

        //получаем список пользователей (members)
        $nCount = bff::i()->Users_getGroupUsersCount(USERS_GROUPS_MEMBER, '');
     
        //generate pagenation
        $this->generatePagenation($nCount, 20, $this->adminCreateLink('sendspam&{pageId}'), $sqlLimit);
		
		if(func::isPostMethod())
		{
			$aRecipients = func::POST('recipients', false);
            $sMessage    = func::POST('message', true);
            $all         = func::POST('all', false, true);
            
            if(!$sMessage) $this->errors->set('no_message');

            if($aRecipients == false && !$all)
                $this->errors->set('no_recipient');

			if($this->errors->no())
			{
                if($all)
                {
                    $this->sendMessageToUsersGroupFromAdmin($sMessage, USERS_GROUPS_MEMBER);
                }
                else
                {
                    $this->sendMessage($aRecipients, $sMessage, false); 
                }
                //Сообщение успешно отправлено
                $this->adminRedirect(Errors::SUCCESSFULL, 'sendspam');
			}
			else 
			{
				$this->tplAssign('message', $sMessage);
			}
		}

		$this->tplAssign('user_info', $aSenderInfo);
        $this->tplAssign('aData', bff::i()->Users_getGroupUsers(USERS_GROUPS_MEMBER, $sqlLimit) );  
		return $this->tplFetch('admin.sendspam.tpl');
	}

}