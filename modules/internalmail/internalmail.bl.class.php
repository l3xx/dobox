<?php

//message types                           
define('INTERNALMAIL_INCOMING', 0);
define('INTERNALMAIL_OUTGOING', 1);

define('INTERNALMAIL_FOLDER_ALL',      0);
define('INTERNALMAIL_FOLDER_FAVORITE', 1);
define('INTERNALMAIL_FOLDER_IGNORE',   2);

define('INTERNALMAIL_STATUS_NEW',          1);
define('INTERNALMAIL_STATUS_DELRECIPIENT', 2);
define('INTERNALMAIL_STATUS_DELAUTHOR',    4);                                   

define('INTERNALMAIL_ATTACH_PATH', PATH_BASE.'files/im/');
define('INTERNALMAIL_ATTACH_URL',  SITEURL.'/files/im/' );

abstract class InternalMailBase extends Module
{
	var $securityKey = '258e6379ce805822c2c73b8c40de0a02';
    
    /*
        Bitwise Operators

        $a & $b     And Bits that are set in both $a and $b are set. 
        $a | $b     Or Bits that are set in either $a or $b are set. 
        $a ^ $b     Xor Bits that are set in $a or $b but not both are set.  
        ~ $a        Not Bits that are set in $a are not set, and vice versa.  
        $a << $b    Shift left Shift the bits of $a $b steps to the left (each step means "multiply by two")  
        $a >> $b    Shift right Shift the bits of $a $b steps to the right (each step means "divide by two")  
    */
    
    function getFolders()
    {     
        return array(
            INTERNALMAIL_FOLDER_ALL      => array('name'=>'Все'),
            INTERNALMAIL_FOLDER_FAVORITE => array('name'=>'Избранные'),
            INTERNALMAIL_FOLDER_IGNORE   => array('name'=>'Игнорирую'),
            );
    }
    
    function moveUser2Folder($nUserID, $nInterlocutorID, $nFolderID)
    {
        if($nUserID<=0 || $nInterlocutorID<=0 || $nFolderID<=0)
            return false;
        
        $nExists = $this->db->one_data('SELECT interlocutor_id FROM '.TABLE_INTERNALMAIL_FOLDERS_USERS.' 
                WHERE user_id='.$nUserID.' AND interlocutor_id='.$nInterlocutorID.' AND folder_id='.$nFolderID);
        
        if($nExists) {
            $this->db->execute('DELETE FROM '.TABLE_INTERNALMAIL_FOLDERS_USERS.'
                   WHERE user_id='.$nUserID.' AND interlocutor_id='.$nInterlocutorID.' AND folder_id='.$nFolderID);
            return 0;
        } else {   
            $this->db->execute('INSERT INTO '.TABLE_INTERNALMAIL_FOLDERS_USERS.'
                    (folder_id, user_id, interlocutor_id)
                    VALUES('.$nFolderID.', '.$nUserID.', '.$nInterlocutorID.')
                ');
            return 1;
        }
    }

    /**
      * проверяет добавлен ли юзер собеседником в папку "Игнорирую"
      * @param int folder_id: ID папки
      * @return boolean
      */    
    function isIgnoredByInterlocutor($nUserID, $nInterlocutorID)
    {                    
        return (bool)$this->db->one_data('SELECT COUNT(*) FROM '.TABLE_INTERNALMAIL_FOLDERS_USERS.' 
                   WHERE user_id='.$nInterlocutorID.' AND interlocutor_id='.$nUserID.' AND folder_id='.INTERNALMAIL_FOLDER_IGNORE);
    }
                                  
	/**
      * отправка сообщения получателю(получателям)
      * @param integer ID отправителя
      * @param mixed ID получателя(-лей)  
      * @param string текст сообщения
      * @param string дата создания сообщения 
     */
	function sendMessage($nAuthorID, $mRecipientID, $sMessage='', $sAttachment='', $sCreatedAt='')
	{
        $nAuthorID = intval($nAuthorID);
        if(!$nAuthorID) return false;
                                             
        $sMessage = $this->db->str2sql($sMessage);
        $sAttachment = $this->db->str2sql( (empty($sAttachment) ? '' : $sAttachment) );        
                
        if(is_array($mRecipientID) && !empty($mRecipientID)) //niu
        {
            $aQueryValues = array();
                           
            foreach($mRecipientID as $rid)
                $aQueryValues[] = '('.$nAuthorID.', '.intval($rid).', '.$sMessage.', '.$sAttachment.', 0, '.$sCreatedAt.')';
            
            if(!empty($aQueryValues)) {
                return $this->db->execute('INSERT INTO '.TABLE_INTERNALMAIL.' (author, recipient, message, attach, status, created)
                           VALUES '.implode(',', $aQueryValues));
            }
        }            
        else
        {
            $mRecipientID = intval($mRecipientID);
            $bResult = $this->db->execute('INSERT INTO '.TABLE_INTERNALMAIL.' (author, recipient, message, attach, status, created)
                           VALUES ('.$nAuthorID.', '.$mRecipientID.', '.$sMessage.', '.$sAttachment.', 0, '.$sCreatedAt.')');
            if($bResult)
                $bResult = $this->updateNewMessagesCounter($mRecipientID);
        }
	}

    /**
      * удаляем переписку
      * @param integer ID удаляющего
      * @param integer ID собеседника
      */
    function deleteConversation($nUserID, $nInterlocutorID)
    {
        if($nUserID>0 && $nInterlocutorID>0)
        {
            $this->db->execute('UPDATE '.TABLE_INTERNALMAIL.' 
                       SET status = (status | IF(author = '.$nUserID.','.INTERNALMAIL_STATUS_DELAUTHOR.','.INTERNALMAIL_STATUS_DELRECIPIENT.') ) 
                       WHERE'." (author=$nUserID AND recipient=$nInterlocutorID) OR 
                                (author=$nInterlocutorID AND recipient=$nUserID)  ");
            $this->updateNewMessagesCounter($nUserID, true);
            return true;
        }
        return false;
    } 
    
    /**
      * удаляем сообщение(-я)
      * @param mixed mMessageID: ID сообщения(сообщений)
      */
    function deleteMessage($mMessageID, $nUserID)
    {
        $isArray = (is_array($mMessageID) && !empty($mMessageID));
        if($isArray) $mMessageID = array_map('intval', $mMessageID);
        else $mMessageID = intval($mMessageID);
        
        if($isArray || $mMessageID)
        {
            $this->db->execute('UPDATE '.TABLE_INTERNALMAIL.' 
                       SET status = (status | IF(author = '.$nUserID.','.INTERNALMAIL_STATUS_DELAUTHOR.','.INTERNALMAIL_STATUS_DELRECIPIENT.') ) 
                       WHERE '.$this->db->prepareIN('id', $mMessageID).' AND (author = '.$nUserID.' OR recipient = '.$nUserID.') ');
            $this->updateNewMessagesCounter($nUserID, true);
            return true;
        }
        return false;
    } 
    
    /**
      * восстанавливаем сообщение(-я)
      * @param mixed mMessageID: ID сообщения(сообщений)
      */
    function recoverMessage($mMessageID, $nUserID)
    {
        $isArray = (is_array($mMessageID) && !empty($mMessageID));
        if($isArray) $mMessageID = array_map('intval', $mMessageID);
        else $mMessageID = intval($mMessageID);
        
        if($isArray || $mMessageID)
        {
            $this->db->execute('UPDATE '.TABLE_INTERNALMAIL.' 
                       SET status = (status & (~ IF(author = '.$nUserID.','.INTERNALMAIL_STATUS_DELAUTHOR.','.INTERNALMAIL_STATUS_DELRECIPIENT.') ) ) 
                       WHERE '.$this->db->prepareIN('id', $mMessageID).' AND (author = '.$nUserID.' OR recipient = '.$nUserID.') ');
            $this->updateNewMessagesCounter($nUserID, true);
            return true;
        }
        return false;
    }  
    
    /**
      * устанавливает статус сообщения (прочитано)
      * @param mixed mMessageID: ID сообщения(сообщений)
      * @param integer ID клиента
      * @param boolead bNew: новое / прочитанное
      */
    function changeMessageNewStatus( $mMessageID, $nUserID=0, $bNew = true )
    {
        $isArray = (is_array($mMessageID) && !empty($mMessageID));
        if($isArray) $mMessageID = array_map('intval', $mMessageID);
        else $mMessageID = intval($mMessageID);
          
        /*
        * new  => status - new [0110 & (~0001) = 0110 или 0111 & (~0001) = 0110]
        * read => status + new [0110 | 0001 = 0111 или 0110 | 0001 = 0111]
        */
        if($isArray || $mMessageID)
        {
            $this->db->execute('UPDATE '.TABLE_INTERNALMAIL.' 
                           SET status = (status '.($bNew?'& (~'.INTERNALMAIL_STATUS_NEW.')':' | '.INTERNALMAIL_STATUS_NEW).') 
                           WHERE '.$this->db->prepareIN('id', $mMessageID).' AND (author='.$nUserID.' OR recipient='.$nUserID.')
                          ');
            $this->updateNewMessagesCounter($nUserID, true);
        } 
        
    }
    
    /**
      * возвращает список входящих-исходящих сообщений
      * @param int ID пользователя, просматривающего свои сообщения
      * @param string папка сообщений ('incoming', 'outgoing') 
      * @param integer ID клиента 
      * @param string лимит выборки
      * @return array
      */    
    function getMessagesListing($nUserID, $sFolder, $sSqlLimit='')
    {
        //get messages 
        $sAdd2Query='';
        if(!empty($sSqlLimit))      
            $sAdd2Query .=$sSqlLimit;     

        list($myRole, $interlocutorRole, $maskDeleted) = ( $sFolder == INTERNALMAIL_INCOMING ? array('recipient','author',INTERNALMAIL_STATUS_DELRECIPIENT) : array('author','recipient',INTERNALMAIL_STATUS_DELAUTHOR) );
                       
        $sQuery = 'SELECT I.id, I.created, ((I.status & '.INTERNALMAIL_STATUS_NEW.')=0) as new, 
                          U.user_id as iid, U.login as ilogin, U.avatar as iavatar
                   FROM '.TABLE_INTERNALMAIL.' I, '.TABLE_USERS." U                         
                   WHERE I.$myRole = $nUserID AND (I.status & $maskDeleted)=0 AND U.user_id = I.$interlocutorRole
                   ORDER BY I.created desc $sAdd2Query";

        return $this->db->select($sQuery);
    }
    
    /**
      * возвращает кол-во сообщений
      * @param integer ID пользователя просматривающего свои сообщения
      * @param string папка сообщений ('incoming', 'outgoing')
      * @param integer ID клиента
      * @return integer
      */
    function getMessagesCount($nUserID, $sFolder)
    {                                        
        list($role, $statusMask) = ( $sFolder == INTERNALMAIL_INCOMING ? array('recipient',INTERNALMAIL_STATUS_DELRECIPIENT) : array('author',INTERNALMAIL_STATUS_DELAUTHOR) );
        
        return (integer)$this->db->one_data('SELECT count(*) FROM '.TABLE_INTERNALMAIL."
                    WHERE $role = $nUserID AND (status & $statusMask)=0 ");               
    }

    /**
      * возвращает сообщение
      * @param integer ID пользователя просматривающего сообщение
      * @param string папка сообщений ('incoming', 'outgoing')
      * @param integer ID клиента
      * @return integer
      */
    function getMessage($nMessageID, $nUserID, $sFolder)
    {                                        
        list($myRole, $interlocutorRole, $statusMask) = ( $sFolder == INTERNALMAIL_INCOMING ? array('recipient','author',INTERNALMAIL_STATUS_DELRECIPIENT) : array('author','recipient',INTERNALMAIL_STATUS_DELAUTHOR) );
                       
        $sQuery = 'SELECT I.id, I.recipient, ((I.status & '.INTERNALMAIL_STATUS_NEW.')=0) as new, I.created, I.message, I.attach, 
                          U.user_id as iid, U.login as ilogin, U.avatar as iavatar
                   FROM '.TABLE_INTERNALMAIL.' I, '.TABLE_USERS." U                         
                   WHERE I.id=$nMessageID AND I.$myRole = $nUserID AND (I.status & $statusMask)=0 AND U.user_id = I.$interlocutorRole
                   LIMIT 1";
        return $this->db->one_array($sQuery);            
    }
    
    /**
      * возвращает кол-во новых сообщений
      * @param integer ID пользователя просматривающего свои сообщения
      * @param integer ID клиента
      * @return integer
      */
    function getNewMessagesCount($nUserID)
    {
        return (integer)$this->db->one_data('SELECT COUNT(*) FROM '.TABLE_INTERNALMAIL.' 
                   WHERE recipient='.$nUserID.' 
                   AND (status & '.(INTERNALMAIL_STATUS_NEW | INTERNALMAIL_STATUS_DELRECIPIENT).')=0');
    }
    
    /**
      * обновляем счетчик новых сообщений
      * @param integer ID пользователя
      * @param integer ID клиента
      * @param boolean обновить счетчик в сессии )
      * @return boolean текущее значение счетчика
      */
    function updateNewMessagesCounter($nUserID, $bUpdateSession = false)
    {
        $nNewMessagesCount = $this->getNewMessagesCount($nUserID);
        $bResult = $this->db->execute('UPDATE '.TABLE_USERS.' SET im_newmsg='.intval($nNewMessagesCount).' WHERE user_id='.$nUserID);
        if($bUpdateSession && $bResult)
        {
            $this->security->updateUserInfo(array('im_newmsg'=>$nNewMessagesCount));
        }
        return $nNewMessagesCount;
    }
    
    function cleanMessage( $sMessage, $nMaxLenght = 4100 )
    {                   
        $sMessage = preg_replace ("/(\<script)(.*?)(script>)/si", '', $sMessage);
        $sMessage = htmlspecialchars($sMessage);
        $sMessage = preg_replace ("/(\<)(.*?)(--\>)/mi", ''.nl2br("\\2").'', $sMessage);
        return mb_substr( Func::hrefActivate($sMessage), 0, $nMaxLenght);    
    }
    
    function uploadAttachment($sInputName = 'attach')
    {
        $attach = new CAttachment($sInputName, INTERNALMAIL_ATTACH_PATH, config::get('im_attach_maxsize', 0) );
        return $attach->upload();
    }
}
