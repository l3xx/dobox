<?php

require_once PATH_CORE.'mail/class.imap.cache.php';

define('IMAP_SESSION', 'imap');

define('IMAPMNGR_BODY_ARRAY', 0); 
define('IMAPMNGR_BODY_PRINT', 1);
define('IMAPMNGR_BODY_PRINTREPLY', 2);

/*
*  uses: 
*  mbstring (Multibyte String Functions)
*  imap (IMAP, POP3 and NNTP Functions)
* 
*  todo:
*  imap_timeout(IMAP_OPENTIMEOUT, 20);
*  IMAP_OPENTIMEOUT, IMAP_READTIMEOUT, IMAP_WRITETIMEOUT, or IMAP_CLOSETIMEOUT
* 
*  Настройки в сессии (акромя кеша):
*  $_SESSION['imap']['mailbox']  - текущий открытый ящик
*  $_SESSION['imap']['settings'] - настройки доступа к ящику
*/

class IMAPManager 
{
    public $user = '';
    public $password = '';
    public $server = '';
    public $port = '110';
    public $type = 'pop3';
    public $serverString = '';        
    public $displayCharset = 'UTF-8';

    private $_mailbox = null;      //Текущий открытый ящик.
    private $_mailboxFlags = null; //IMAP настройки для текущего открытого ящикa.  

    private $_stream = false;
    private $_config = array();
    private $_encryptPassword = true;
    private $_encryptionKey = '#0064dee1e57test96062&*()e#05d7e3a]21]73d30c#';
    
    public  $cache = null;

    # ------------------------------------------------------------------
    # Конструктор
      
    /** @example: $imap_mngr = &IMAPManager::singleton(); */
    static function &singleton( $restoreSettings = true, $initCache = false )
    {     
        static $object;
        if (!isset($object)) 
        {
            $object = new IMAPManager(); 
            
            if($restoreSettings)
                $object->restoreSettings(CID);
              
            //init imap cache
            if($initCache)
            {                                              
                $object->cache = &IMAPCache::singleton();
                if(!empty($object->user)) //если доступ был настроен
                    $object->cache->init();
            }
        }
        return $object;
    }
    
    function __construct() 
    {                 
        if (FORDEV && !Func::extensionLoaded('imap'))
        {
            if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
        	    dl('php_imap.dll');
            else
                dl('php_imap.so');
            
            if (!Func::extensionLoaded('imap',true))
                throw new Exception('Could not load required extension (PHP_IMAP)... Please install extension.');
        } 
        
        $this->_config['login_tries']      = 3;  
        $this->_config['stripscripttags']  = 1;
        $this->_config['linknewwindow']    = 1;
                                               
        $this->_config['attachments_download'] = 0;
        $this->_config['attachments_path']     = PATH_BASE.'files/mail/tmp/';
        $this->_config['attachments_url']      = SITEURL.'/files/mail/tmp/';
        $this->_config['attachments_prefix']   = substr(md5($this->user), 0, 8);
    }
    
    function __destruct()
    {                  
        $this->close();
    }
    
    function saveSettings( $settings, $clientLogin, $updatebase = true )
    {
        global $oDb, $oSecurity;

        foreach($settings as $key=>$value)
            $this->$key = $value;
        
        //update server string
        $this->serverString = "{{$this->server}:{$this->port}/{$this->type}}"; 
        
        if($updatebase)
        {              
            if(!empty($settings['password']) && $this->_encryptPassword)
                $settings['password'] = $oSecurity->encrypt($this->password, md5($clientLogin).$this->_encryptionKey);
                
            $oDb->execute('UPDATE '.TABLE_CLIENTS_SETTINGS.' SET mail_settings='.$oDb->str2sql( serialize($settings) ).' WHERE cid='.CID.' '); 
        }
        
        unset( $_SESSION[IMAP_SESSION]['cache'] );
    }
    
    function restoreSettings($CID)
    {
        global $oDb, $oSecurity, $oClientConfig;
        
        if(defined('CID')) {
            $settings = $oClientConfig['settings']['mail_settings']; 
            $clientLogin = CDOMAIN;   
        } else {
            $aResult = $oDb->one_array('SELECT C.login, CS.mail_settings FROM '.TABLE_CLIENTS_SETTINGS.' CS, '.TABLE_CLIENTS.' C WHERE C.cid='.$CID.' AND CS.cid=C.id LIMIT 1');
            
            $settings = $aResult['mail_settings'];   
            $clientLogin = $aResult['login'];
            unset($aResult);
        }
        
        if(!empty($settings))
            $settings = unserialize($settings);
        
        if(!empty($settings['password']) && $this->_encryptPassword)
            $settings['password'] = $oSecurity->decrypt($settings['password'], md5($clientLogin).$this->_encryptionKey);
        
        if($settings)
        foreach($settings as $key=>$value)
            $this->$key = $value;

        $this->serverString = "{{$this->server}:{$this->port}/{$this->type}}"; 
    }
    
    # ------------------------------------------------------------------
    # Соединение
    
    function open($mailbox = '', $flags = 0) 
    {
        if (empty($this->_stream)) {           
            $i = -1;

//            debug_print_backtrace();
//            exit;
            
            if(empty($mailbox)) { 
                $mailbox = $this->getCurrentMailbox(); //берем из сессии последний mailbox
                if(empty($mailbox))
                    $flags |= OP_HALFOPEN; 
            }
            
            $sServerString = $this->getServerString($mailbox);                                        
//            echo $sServerString.'/user='.$this->user.'/password='.$this->password.'<br />'; 
            
            if (version_compare(PHP_VERSION, '5.2.1') != -1) {
                $this->_stream = @imap_open($sServerString, $this->user, $this->password, $flags, $this->_config['login_tries']);
                //login error
                if($this->_stream === false && strstr(strtolower(imap_last_error()), 'login failure')!==FALSE)
                    throw new Exception('login_failure');

            } else {
                while (($this->_stream === false) &&
                       !strstr(strtolower(imap_last_error()), 'login failure') &&
                       (++$i < $this->_config['login_tries'])) {
                    if ($i != 0) {
                        sleep(1);
                    }
                    $this->_stream = @imap_open($sServerString, $this->user, $this->password, $flags);  
                }
            }

    	    if($this->_stream) {
                if(!empty($mailbox)) { 
                    $this->setCurrentMailbox($mailbox, $flags);
                }
    	        return true;
            }
    	    if(imap_last_error()) {                        
                throw new Exception( imap_last_error() );
            }
            else { 
                throw new Exception( "Couldn't open stream <b>{$this->server}:{$this->port}</b>..." );
            }
        }    
    	return true;
    }

    function reopen($mailbox, $flags)
    {
        if (version_compare(PHP_VERSION, '5.2.1') != -1) {
            $result = @imap_reopen($this->_stream, $this->getServerString($mailbox), $flags, $this->_config['login_tries']);
        } else {
            $result = @imap_reopen($this->_stream, $this->getServerString($mailbox), $flags);
        }
        return $result;
    }

    function close() 
    {
        if(!empty($this->_stream))
            @imap_close($this->_stream);
    }
    
    function changeMailbox($mailbox, $flags = 0)
    {
        /* Открываем соединение, если не было открыто */
        if (empty($this->_stream)) {
            if($this->open($mailbox, $flags)) {
                return true;
            } else {
                return false;
            }
        }

        /* Если меняем mailbox, то делаем reopen для нового mailbox */
        if ($this->_mailbox != $mailbox) {
            $result = $this->reopen($mailbox, $flags);
            if ($result) {
                $this->setCurrentMailbox($mailbox, $flags);
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
   
    function stream()
    {
        if (!$this->_stream) {
            $this->open();
        }
        return $this->_stream;
    }
   
    function error($error) 
    {
        echo $error;
        exit();
    }
    
    function getServerString($mailbox)
    {
        return $this->serverString.(!empty($mailbox)?$mailbox:'');
    }

    # ------------------------------------------------------------------
    # Письма (Messages)
    
    /**
     * Возвращает список найденных UID писем, в порядке получения.
     * @param string $mailbox   краткое название ящика.
     * @param string $base      строка поиска IMAP.
     * @param boolean $delhide  прятать ли удаленные сообщения.
     * @return array
     */       
    function searchMessages($mailbox, $search = 'ALL', $delhide = true, $check = false)
    {
        $search = array( mb_strtoupper( trim($search) ) );

        if ($delhide)
            $search[] = 'UNDELETED';

        $cacheID = serialize($search);

        $res = $this->cache->get($mailbox, $cacheID, $check); //последний параметр выполняет проверку статуса ящика!
        if (!$res){
            if ($this->changeMailbox( $mailbox )) {
                $res = @imap_search($this->stream(), implode(' ', $search), SE_UID, $this->displayCharset);
            } else {
                $res = array();
            }
            $res = ( empty($res) ? array() : array_reverse($res) );
            $this->cache->set( $mailbox, array($cacheID => $res) );
        }

        return $res;
    } 
    
    function getMessage($uid, $extended = false, $useCache = true)
    {
        if($useCache)
        {
            $cacheID = $this->cache->getMessageCacheID($uid);
            $aMessage = $this->cache->get( $this->getCurrentMailbox(), $cacheID, false );
            $updateCache = false; 
        } else { $aMessage = false; }
                 
        if($aMessage === false)
        {    
            $aMessage = array('uid'=>$uid, 'header'=>'', 'date'=>'', 'body'=>false, 'attachments'=>false, 'attachments_view'=>'');
            
            //получаем краткую информацию о письме
            $aMessage['header'] = imap_headerinfo($this->stream(), imap_msgno($this->stream(), $uid));
            $aMessage['header'] = array( 
                    'subject' => $this->decodeHeader( (property_exists($aMessage['header'], 'subject') ? $aMessage['header']->subject : '') ),
                    'from' => ($aMessage['header']->from[0]->mailbox=='INVALID_ADDRESS'?'':$aMessage['header']->from[0]->mailbox.'@'.$aMessage['header']->from[0]->host),
                    'from_personal' => $this->decodeHeader( (property_exists($aMessage['header']->from[0], 'personal') ? $aMessage['header']->from[0]->personal : '') ),
                    'date' => $aMessage['header']->udate
                    );
            $aMessage['date'] = Func::datetime2dateUSSRWithMonth( date('Y-m-d H:i:s', $aMessage['header']['date']), true );
            
            $updateCache = true;
        }
        
        if( $extended && empty($aMessage['body']) )
        {
            $updateCache = true;
            
            $old_error = error_reporting(0);
            $structure = @imap_fetchstructure($this->stream(), $uid, FT_UID);
            error_reporting($old_error);
          
//            echo '<pre>';
//            print_r($structure);
//            echo '</pre><br /><br />';
//            exit;
            
            if(!$aMessage['body']) {
                $aMessage['body'] = $this->_getMessageBody($uid, false, $structure, IMAPMNGR_BODY_PRINT);
            }
            
            if(!$aMessage['attachments'])
            {
                //анализируем структуру сообщения, расскладываем на секции        
                $this->_parseMessageStructure($sections, $structure); 
                
                $aMessage['attachments'] = $this->_getMessageAttachments($uid, $sections);
//                if($aMessage['attachments'])
//                {
//                    $sAttach = '<hr size="1" style="color:#ddd; margin-bottom:4px;" />';
//                    foreach($aMessage['attachments'] as $attach)
//                        $sAttach .= "<div class=\"attachment\">{$attach['filename']} ({$attach['mimeType']}, ".Func::getfilesize($attach['size']).")</div>";
//                    
//                    $aMessage['attachments_view'] = $sAttach;
//                }

    //        echo '<pre>';
    //        print_r($sections);
    //        echo '</pre>';
    //        echo '<br /><br />';
            } 
        }

//            echo '<pre>';
//            print_r($aMessage);
//            echo '</pre><br /><br />';
//            exit;
        
        if($useCache && $updateCache)
            $this->cache->set( $this->getCurrentMailbox(), array($cacheID=>$aMessage) );
                                
        return $aMessage;
    }    

    function deleteMessage($uid, $expunge = false) 
    {
        imap_delete($this->_stream, $uid, FT_UID);
        
        if($expunge) {
            imap_expunge($this->_stream);
            $this->cache->expire( $this->getCurrentMailbox(), $this->cache->getMessageCacheID($uid) );
        }
    }

    function flagMessages($flag, $aMessagesUID)
    {
        if(empty($aMessagesUID))
            return false;
        
        reset($aMessagesUID);
        $msglist = '';
        while(list($key, $value) = each($aMessagesUID))
        {
            if(!empty($msglist)) $msglist .= ",";
            $msglist .= $value;
        }

        switch($flag)
        {
            case "flagged":
                $result = imap_setflag_full ($this->stream(), $msglist, "\\Flagged", ST_UID);
                break;
            case "read":
                $result = imap_setflag_full ($this->stream(), $msglist, "\\Seen", ST_UID);
                break;
            case "answered":
                $result = imap_setflag_full ($this->stream(), $msglist, "\\Answered", ST_UID);
                break;
            case "unflagged":
                $result = imap_clearflag_full ($this->stream(), $msglist, "\\Flagged", ST_UID);
                break;
            case "unread":
                $result = imap_clearflag_full ($this->stream(), $msglist, "\\Seen", ST_UID);
                $result = imap_clearflag_full ($this->stream(), $msglist, "\\Answered", ST_UID);
                break;
        }
        return $result;
    }
   
    # ------------------------------------------------------------------
    # Ящики (Mailboxes)
    
    function getMailboxes()
    {
        return $this->cache->_sessionCache;
    }
    
    function getMailboxSize($mailbox, $formatted = true)
    {
        $this->changeMailbox( $mailbox );
        $info = @imap_mailboxmsginfo($this->stream());
        if ($info) {
            return ($formatted)
                ? sprintf(_("%.2fМб"), $info->Size / (1024 * 1024))
                : $info->Size;
        } else {
            return 0;
        }
    }
                    
    function setCurrentMailbox($mailbox, $flags = 0)
    {   
        $this->_mailbox = $mailbox;
        $this->_mailboxFlags = $flags;
        
        $_SESSION[IMAP_SESSION]['mailbox'] =  $mailbox;
    }
    
    function getCurrentMailbox()
    {
        if(empty($_SESSION[IMAP_SESSION]['mailbox']))
            $_SESSION[IMAP_SESSION]['mailbox'] = 'INBOX';
            
        return $_SESSION[IMAP_SESSION]['mailbox'];
    }
    
    function encodeMailboxName($mailbox)
    {
        if(empty($mailbox))
            return $mailbox;
        
        return mb_convert_encoding( $mailbox, 'UTF7-IMAP', $this->displayCharset );
    }

    function decodeMailboxName($mailbox)
    {
        if(empty($mailbox))
            return $mailbox;
            
        return mb_convert_encoding( $mailbox, $this->displayCharset, 'UTF7-IMAP');
    }                                                    


    # ------------------------------------------------------------------
    # Вспомогательные функции
    
    // prints the main part of the message
    function _printMessageBody($body, $subtype, $isreply)
    {
        $msgbody = "";
       
        if($subtype == 'HTML' || $subtype == 'text/html') {
            // clean up HTML for displaying within an HTML page
            if($isreply)
                $msgbody = "> " . str_replace("\n", "\n> ", $this->_printTrimHTMLBody($body));
            else
                $msgbody = $this->_printTrimHTMLBody($body);
        } else {
            if($isreply)
                $msgbody = "> " . str_replace("\n", "\n> ", $body);
            else
                $msgbody = nl2br(str_replace("  ", "&nbsp;&nbsp; ", htmlspecialchars($body)));
        }

        # if this is reply text just return it
        # if it is HTML replace <A> tag targets with _blank
        # otherwise hyperlink web addresses and email addresses in the text
        if($isreply) {
            return $msgbody;
        } else if ($subtype == 'HTML' || $subtype == 'text/html') {
            # replace any hyperlinks with local targets with a target _blank so they popup in nice new windows
            return preg_replace_callback("/<a[^>]*>/i", array($this, '_printFixAnchor'), $msgbody);
        } else {
            # replace plain URL's with hyperlinked URL's
            if ($this->_config['linknewwindow'] == 1) {
                    return preg_replace("/\b(http(s)?:\/\/|(www\.))([-\w\.\/]+)(\?[\w,\.*%;-]+=[\w,\.*%;-]+(?:&[\w,\.*%;-]+=[\w,\.*%;-])*)?\b/", "<a href=\"http$2://$3$4$5\" target=\"_blank\">$1$4$5</a>$6", $msgbody);
            } else {
                    return preg_replace("/\b(http(s)?:\/\/|(www\.))([-\w\.\/]+)(\?[\w,\.*%;-]+=[\w,\.*%;-]+(?:&[\w,\.*%;-]+=[\w,\.*%;-])*)?\b/", "<a href=\"http$2://$3$4$5\">$1$4$5</a>$6", $msgbody);
            }
        }  
    }

    // alters <A> tags so that all targets point to _blank
    function _printFixAnchor($tag)
    {
        // is there already a target?
        if ($this->_config['linknewwindow'] == 1) {
            if(preg_match("/ target[ ]*=[ ]*([^ >]*)/i", $tag[0]) > 0) {
                $tag = preg_replace("/ target[ ]*=[ ]*([^ >]*)/i", " target=\"_blank\"", $tag[0]);
            }
            else {
                // there is no target - let's add one
                $tag = preg_replace("/<a /i", "<a target=\"_blank\" ", $tag[0]);
            }
        } else {
            if(preg_match("/ target[ ]*=[ ]*([^ >]*)/i", $tag[0]) > 0) {
                $tag = preg_replace("/ target[ ]*=[ ]*([^ >]*)/i", "", $tag[0]);
            }
            else {
                // there is no target - let's add one
                $tag = preg_replace("/<a /i", "<a ", $tag[0]);
            }
        }
        return $tag;
    }    

    // trims out the body of an HTML message
    function _printTrimHTMLBody($body)
    {
        // We already have a html and a body, we want to ditch any ones that are already in the email
        $body = preg_replace("/(\<html[^\>]*\>)/i","", $body);
        $body = preg_replace("/(\<\/html[^\>]*\>)/i","", $body);

        $body = preg_replace("/(\<body[^\>]*\>)/i","", $body);
        $body = preg_replace("/(\<\/body[^\>]*\>)/i","", $body);
        
        // Strip out the head tags
        $body = preg_replace("/\<head\>.*\<\/head\>/si", "", $body);
        // strip out the script tags if we are supposed to
        if ($this->_config['stripscripttags'] == 1)
            $body = preg_replace("/<\s*script[^>]*>.*<\s*\/script\s[^>]*>/si", "", $body);    

        return $body;
    }

    /* $nReturnType: IMAPMNGR_BODY_ARRAY, IMAPMNGR_BODY_PRINT, IMAPMNGR_BODY_PRINTREPLY */
    function _getMessageBody($uid, $partID, $structure, $nReturnType = IMAPMNGR_BODY_ARRAY)
    {
//        if($partID != false) {
//            $imapPartIDs = explode('.',$partID);
//            foreach($imapPartIDs as $id) {
//                if($structure->type == TYPEMESSAGE && $structure->subtype == 'RFC822') {
//                    $structure = $structure->parts[0]->parts[$id-1];
//                } else {
//                    $structure = $structure->parts[$id-1];
//                }
//            }
//        }
        
        switch($structure->type) 
        {
            case TYPETEXT: //0
            {
                if (($structure->subtype == 'HTML' || $structure->subtype == 'PLAIN') && 
                    ($structure->ifdisposition == 0 || ( $structure->ifdisposition == 1 && isset($structure->disposition) && @$structure->disposition != 'ATTACHMENT')) ) 
                {
                    if($partID == false) { $partID = 1; }
                    
                    $mimePartBody = $this->fetchMessageBody( $uid, $partID, $structure->encoding, $this->getMimePartParameter($structure, 'charset'));
                    
                    if($nReturnType)
                    {
                        return $this->_printMessageBody($mimePartBody, $structure->subtype, $nReturnType == IMAPMNGR_BODY_PRINTREPLY);
                    } else {
                        return array(
                            array(
                                'body'     => $mimePartBody,
                                'mimeType' => $structure->subtype == 'HTML' ? 'text/html' : 'text/plain',
                                'charset'  => $this->getMimePartParameter($structure, 'charset'),
                            )
                        );
                    }
                }
                return ($nReturnType ? '' : array());
            } break;
            case TYPEMULTIPART: //1
            {
                switch($structure->subtype) 
                {
                    case 'ALTERNATIVE': {
                        return $this->_getMultipartAlternative($uid, $partID, $structure, $nReturnType);
                    } break;
                    default: {
                        $i = 1;
                        $parentPartID = ($partID != false) ? $partID.'.' : '';
                        $bodyParts = ($nReturnType ? '' : array());
                        foreach($structure->parts as $part) {
                            if(in_array($part->type, array(TYPETEXT, TYPEMULTIPART, TYPEMESSAGE))) {
                                if($nReturnType)
                                    $bodyParts .=  $this->_getMessageBody($uid, $parentPartID.$i, $part, $nReturnType);
                                else
                                    $bodyParts = array_merge($bodyParts, $this->_getMessageBody($uid, $parentPartID.$i, $part, $nReturnType));
                            }
                            $i++;
                        }
                        return $bodyParts;
                    } break;
                }

            } break;
            case TYPEMESSAGE: { //2
                switch($structure->subtype) 
                {
                    case 'RFC822': {
                        $i = 1;
                        foreach($structure->parts as $part) {
                            if($part->type == TYPEMULTIPART &&
                               in_array($part->subtype, array('RELATED', 'MIXED', 'ALTERNATIVE', 'REPORT')) ) {
                                $bodyParts = $this->_getMessageBody($uid, $partID, $part, $nReturnType);
                            } else {
                                $bodyParts = $this->_getMessageBody($uid, $partID.'.'.$i, $part, $nReturnType);
                            }
                            $i++;
                        }
                        return $bodyParts;

                    } break;
                    case 'DELIVERY-STATUS': {
                        // only text
                        if($partID == false) $partID = 1;
                        $mimePartBody = $this->fetchMessageBody( $uid, $partID, $structure->encoding, $this->getMimePartParameter($structure, 'charset'));
                        
                        if($nReturnType) {
                            return $this->_printMessageBody($mimePartBody, $structure->subtype, $nReturnType == IMAPMNGR_BODY_PRINTREPLY);
                        } else {
                            return array( array(
                                'body'     => $mimePartBody,
                                'mimeType' => 'text/plain',
                                'charset'  => $this->getMimePartParameter($structure, 'charset'),
                            ));
                        }

                    } break;
                }

            } break;
            default:
            {
                if($nReturnType)
                {
                    return '';
                } else {
                return array(
                    array(
                        'body'     => 'The mimeparser can not parse this message.',
                        'mimeType' => 'text/plain',
                        'charset'  => $this->displayCharset,
                    ) );
                }
            } break;
        }
    }

    function fetchMessageBody($uid, $partID, $encoding, $charset = '')
    {
        #// MS-Outlookbug workaround (don't break links)
        #$mimeMessage = preg_replace("!((http(s?)://)|((www|ftp)\.))(([^\n\t\r]+)([=](\r)?\n))+!i",
        #               "$1$7",
        #               $mimeMessage);

        $body = @imap_fetchbody($this->_stream, $uid, $partID, FT_PEEK + FT_UID);
        
        switch ($encoding)
        {
            case ENCBASE64: //3
                // use imap_base64 to decode
                $body = imap_base64( $body );
                break;
            case ENCQUOTEDPRINTABLE: //4
                // use imap_qprint to decode
                $body = imap_qprint( $body );
                break;
//            case 0: $body = imap_7bit( $body ); break;
//            case 1: $body = imap_8bit( $body ); break;
//            case 2: $body = imap_binary( $body ); break;
            default:
                break;
        }
        
        if(!empty($charset) && $charset!='default' && $charset!='unknown')
            $body = mb_convert_encoding($body, $this->displayCharset, $charset);
        
        return $body;
    }
                
    function _getMultipartAlternative($uid, $parentPartID, $structure, $nReturnType, $htmlMode = 'always_display') 
    {
        // a multipart/alternative has exactly 2 parts (text and html  OR  text and something else)
        $i=1;
        $partText;
        $partHTML;
        $parentPartID = ($parentPartID != false) ? $parentPartID.'.' : $parentPartID;
        
        foreach($structure->parts as $part) 
        {
            if($part->type == TYPETEXT && $part->subtype == 'PLAIN' && $part->bytes > 0) {
                $partText = array(
                        'partID'    => $parentPartID.$i ,
                        'charset'   => $this->getMimePartParameter($part, 'charset'),
                        'encoding'  => $part->encoding ,
                );
            } elseif ($part->type == TYPETEXT && $part->subtype == 'HTML' && $part->bytes > 0) {
                $partHTML = array(
                        'partID'        => $parentPartID.$i ,
                        'charset'       => $this->getMimePartParameter($part, 'charset') ,
                        'encoding'      => $part->encoding ,
                );
            } elseif ($part->type == TYPEMULTIPART && $part->subtype == 'RELATED' && is_object($part->parts[0])) {
                $part = $part->parts[0];
                $parentPartID = $parentPartID.$i;
                if($part->type == TYPETEXT && $part->subtype == 'PLAIN' && $part->bytes > 0) {
                    $partText = array(
                            'partID'    => $parentPartID.'.1',
                            'charset'   => $this->getMimePartParameter($part, 'charset') ,
                            'encoding'  => $part->encoding ,
                    );
                } elseif ($part->type == TYPETEXT && $part->subtype == 'HTML' && $part->bytes > 0) {
                    $partHTML = array(
                            'partID'   => $parentPartID.'.1',
                            'charset'  => $this->getMimePartParameter($part, 'charset') ,
                            'encoding' => $part->encoding ,
                    );
                }
            }
            $i++;
        }

        $bodyPart = ($nReturnType ? '' : array());
        
        switch($htmlMode) 
        {
            case 'always_display':
                if(is_array($partHTML)) {
                    
                    $partContent = $this->fetchMessageBody($uid, $partHTML['partID'], $partHTML['encoding'], $partHTML['charset']);
                    if($nReturnType) {
                        $bodyPart .= $this->_printMessageBody($partContent, 'text/html', $nReturnType == IMAPMNGR_BODY_PRINTREPLY);
                    } else {
                        $bodyPart[] = array(
                            'body'      => $partContent,
                            'mimeType'  => 'text/html',
                            'charset'   => $partHTML['charset']
                        );
                    }
                }
                break;
            case 'only_if_no_text': {
                if(is_array($partHTML) && !is_array($partText)) {
                    $partContent = $this->fetchMessageBody($uid, $partHTML['partID'], $partHTML['encoding'], $partHTML['charset']);
                    if($nReturnType) {
                        $bodyPart .= $this->_printMessageBody($partContent, 'text/html', $nReturnType == IMAPMNGR_BODY_PRINTREPLY);
                    } else {                    
                        $bodyPart[] = array(
                            'body'      => $partContent,
                            'mimeType'  => 'text/html',
                            'charset'   => $partHTML['charset']
                        );
                    }
                } elseif (is_array($partText)) {
                    $partContent = $this->fetchMessageBody($uid, $partText['partID'], $partText['encoding'], $partText['charset']);
                    if($nReturnType) {
                        $bodyPart .= $this->_printMessageBody($partContent, 'text/plain', $nReturnType == IMAPMNGR_BODY_PRINTREPLY);
                    } else { 
                        $bodyPart[] = array(
                            'body'      => $partContent,
                            'mimeType'  => 'text/plain',
                            'charset'   => $partText['charset']
                        );
                    }
                }
            } break;

            default: {
                if (is_array($partText)) {
                    $partContent = $this->fetchMessageBody($uid, $partText['partID'], $partText['encoding'], $partText['charset']);
                    if($nReturnType) {
                        $bodyPart .= $this->_printMessageBody($partContent, 'text/plain', $nReturnType == IMAPMNGR_BODY_PRINTREPLY);
                    } else { 
                        $bodyPart[] = array(
                            'body'      => $partContent,
                            'mimeType'  => 'text/plain',
                            'charset'   => $partText['charset']
                        );
                    }
                }
            } break;
        }

        return $bodyPart;
    }

    function _getMessageAttachments($uid, $sections, $isreply = false)
    {
        $arrayData = array();
        if(count($sections) > 0)
        {
            foreach($sections as $key => $value)
            {
                if(isset($value['type']) && $value['type'] == 'attachment' && 
                    (floor($key)!=$key ? $sections[floor($key)]['mimeType'] != 'multipart/alternative' : 1)) //2.1 => 2 не multipart/alternative
                {
                    $arrayData[] = array_merge($value, array('uid'=>$uid));
                    
                    # сохранение приложений перенесено в TicketsAttachments::uploadFromMAIL
                    if(false && $this->_config['attachments_download'] == 1)
                    {
                        # формируем имя файла
                        $filename_saved = $this->_config['attachments_prefix']."-$uid$newpartnumber.".pathinfo($value['filename'], PATHINFO_EXTENSION);
                        $filepath = $this->_config['attachments_path'].$filename_saved;
                        
                        # декодируем вложение и сохраняем на диск
                        $fp = fopen($filepath, "w");
                        fwrite($fp, $this->fetchMessageBody($uid, $value['partID'], $value['encoding']) );
                        fclose($fp);
                        
                        # формируем ссылку, если это изображение, показываем на странице
                        if (!$isreply) {
                            if ($value['image']) {
                                $message['body'] .= '<img src="'.$this->_config['attachments_url'].$filename_saved.'" style="text-align: center"><br />';
                            } else {
                                $message['body'] .= '<a href="'.$filepath.'">'.$value['filename'].' ('.$value['mimeType'].')</a> ('.Func::getfilesize( filesize($filepath), true).')';
                            }
                        }
                    }
                }
            }
            if(count($arrayData) > 0)
            {
                return $arrayData;
            }
        }

        return false;
    }
    
    function _parseMessageStructure(&$sections, $structure, $wantedPartID = '', $currentPartID='')
    {
        switch ($structure->type)
        {
            case TYPETEXT: //0
                if(!preg_match("/^$wantedPartID/i",$currentPartID))
                    break;

                $data['encoding']   = $structure->encoding;
                $data['size']       = $structure->bytes;
                $data['partID']     = $currentPartID;
                $data['mimeType']   = 'text/'. strtolower($structure->subtype);
                $data['name']       = $this->getMimePartParameter($structure, 'name');
                $data['charset']    = $this->getMimePartParameter($structure, 'charset');
                
                // set this to zero, when we have a plaintext message
                // if partID[0] is set, we have no attachments
                if($currentPartID == '') $currentPartID = '0';

                if(  ( $structure->ifdisposition && 
                       (strtolower($structure->disposition) == 'attachment'
                        //|| (strtolower($structure->disposition) == 'inline' && strtolower($structure->subtype) != 'plain')
                       ) 
                     ) 
                     || $data['name'] != 'unknown')
                {
                    //treat it as attachment, must be a attachment
                    $sections[$currentPartID]             = $data;  
                    $sections[$currentPartID]['image']    = 0;
                    $sections[$currentPartID]['filename'] = $this->getMimePartParameter($structure, 'filename', 'name');  
                    $sections[$currentPartID]['type']     = 'attachment';
                }
                else
                {
                    // must be a body part
                    $sections[$currentPartID]         = $data;
                    $sections[$currentPartID]['name'] = 'body part'." $currentPartID";
                    $sections[$currentPartID]['type'] = 'body';
                }

                break;

            case TYPEMULTIPART: //1
                                                    
                // lets cycle trough all parts
                $sections[ ($currentPartID?$currentPartID:'0') ]['mimeType'] = 'multipart/'. strtolower($structure->subtype);
                if($currentPartID != '') $currentPartID .= '.';
                for($i = 0; $i < count($structure->parts); $i++)
                    $this->_parseMessageStructure($sections, $structure->parts[$i], $wantedPartID, $currentPartID.($i+1));

                break;

            case TYPEMESSAGE: //2
            
                if(($wantedPartID < $currentPartID) ||
                        empty($wantedPartID))
                {
                    $sections[$currentPartID]['encoding'] = $structure->encoding;                       
                    $sections[$currentPartID]['size']     = $structure->bytes;
                    $sections[$currentPartID]['partID']   = $currentPartID;
                    $sections[$currentPartID]['mimeType'] = 'message/'. strtolower($structure->subtype);
                    $sections[$currentPartID]['type']     = 'attachment';

                    if($structure->encoding == ENCBASE64 && $structure->bytes>0) { //объем данных закодированный в base64 больше на ~25-30%
                        $sections[$currentPartID]['size'] = $structure->bytes - ($structure->bytes * 0.29);
                    }
                    
                    $sections[$currentPartID]['name'] = $this->getMimePartParameter($structure, 'filename', 'name');
                    if($sections[$currentPartID]['name'] == 'unknown' && !empty($structure->description))
                        $sections[$currentPartID]['name'] = $structure->description;
                }
                else
                {   // recurse in it
                    for($i = 0; $i < count($structure->parts); $i++)
                    {
                        if($structure->parts[$i]->type != TYPEMULTIPART)
                                $currentPartID = $currentPartID.'.'.($i+1);
                        $this->_parseMessageStructure($sections, $structure->parts[$i], $wantedPartID, $currentPartID);
                    }
                }
                
                break;

            case TYPEAPPLICATION: //3
            case TYPEAUDIO: //4
            case TYPEIMAGE: //5
            case TYPEVIDEO: //6
            case TYPEMODEL: //7
             
                if(!preg_match("/^$wantedPartID/i",$currentPartID))
                    break;

                $mimeType = '';
                switch($structure->type)
                {
                    case TYPEAPPLICATION: $mimeType = 'application'; break;
                    case TYPEAUDIO:       $mimeType = 'audio'; break;
                    case TYPEIMAGE:       $mimeType = 'image'; break;
                    case TYPEVIDEO:       $mimeType = 'video'; break;
                    case TYPEMODEL:       $mimeType = 'model'; break;
                }
                            
                $sections[$currentPartID]['image']    = ($structure->type==TYPEIMAGE?1:0);    
                $sections[$currentPartID]['encoding'] = $structure->encoding;
                $sections[$currentPartID]['size']     = $structure->bytes;
                $sections[$currentPartID]['partID']   = $currentPartID;
                $sections[$currentPartID]['mimeType'] = $mimeType.'/'. strtolower($structure->subtype);
                $sections[$currentPartID]['filename'] = $this->getMimePartParameter($structure, 'filename', 'name');  
                $sections[$currentPartID]['type']     = 'attachment';
                
                if($structure->encoding == ENCBASE64 && $structure->bytes>0) { //объем данных закодированный в base64 больше на ~25-30%
                    $sections[$currentPartID]['size'] = $structure->bytes - ($structure->bytes * 0.29);
                }

                break;

            default:
                break;
        }
    }
  
    function decodeHeader($string, $charset = '') 
    {   
        if(empty($charset))
            $charset = $this->displayCharset;
        
        $string = trim( preg_replace('/\?=\s+=\?/', '?= =?', $string) );
        if(empty($string)) return '';
        
        $elements = imap_mime_header_decode($string);
        $string = '';
        for ($i=0; $i<count($elements); $i++) {
            if($elements[$i]->charset!='default')
                 $elements[$i]->text = mb_convert_encoding($elements[$i]->text, $charset, $elements[$i]->charset);
            $string .= $elements[$i]->text;
        }
        return str_replace(array("\n","\r"), '', $string);
    }
    
    function getMimePartParameter($part, $attributeDP, $attributeP = null)
    {
        if(empty($attributeP))
            $attributeP = $attributeDP;
        
        if($part->ifdparameters) {                       
            foreach($part->dparameters as $param) {
                if(strtolower($param->attribute) == $attributeDP) {
                    return $this->decodeHeader($param->value);
                }
            }
        } else if($part->ifparameters) {
            foreach($part->parameters as $param) {
                if(strtolower($param->attribute) == $attributeP) {
                    return $this->decodeHeader($param->value);
                }
            }
        }
        return 'unknown';
    }
    
    //поиск новых писем по фильтру, результат в порядке получения
//    function filterNewMessages($mailbox, $criteria)
//    {
//        $mailbox = $this->encodeMailboxName($mailbox);
//        $criteria = trim( $criteria );
//        $cacheID = serialize($criteria);

//        $where = array("TO","SUBJECT","FROM","BODY");
//        
//        if (!isset($this->_arrival[$mailbox][$cacheID])) {
//            if ($this->changeMailbox($mailbox, OP_READONLY)) 
//            {
//                
//                $res = @imap_search($this->stream(), implode(' ', $search), SE_UID, $charset);
//                if (!isset($this->_arrival[$mailbox])) {
//                    $this->_arrival[$mailbox] = array();
//                }
//            } else {
//                $res = array();
//            }
//            $this->_arrival[$mailbox][$cacheID] = (empty($res)) ? array() : $res;
//        }

//        return $this->_arrival[$mailbox][$cacheID];
//    }    
}
?>