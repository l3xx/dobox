<?php

//print_r($oSecurity->sessionData); exit;

/**
 * can storage session accounts data into Database
 * CREATE TABLE _sessions (
  id CHAR (100) NOT NULL, 
  session_id CHAR (100), 
  session_name CHAR (50),
  session_data TEXT, 
  created_dt DATETIME, 
  UNIQUE(id), 
  INDEX(id,session_id,session_name)
  )
 */

class Security          
{
    public $sessionData = array();
    private $sSerializeVarname = 'MYSESSION';
    public  $sCurrentDBSessionID = null;             
        
    const USEDB = false; 
        
	function __construct($sessionKey = '')
	{
		$this->sessionStart($sessionKey, false); 

		$this->sessionData['curr_session_id']    = session_id();
        $this->sessionData['start_session_time'] = date('Y-m-d H:i:s');

		$sCurrentDBSessionID = Func::POSTGET('curdbsessid');
		if($sCurrentDBSessionID)
		    $this->sCurrentDBSessionID = $sCurrentDBSessionID;
        
		$this->restoreSession();
        
        //check access to fordev mode 
        $this->checkFORDEV();                     
        
        // уязвимость 'session fixation'
        if(!empty( $this->sessionData['curr_session_id'] )) //если сессия стартовала
        {
            # много динамических ip, часто слетает сессия, пока закомментим
            //$ip = func::getRemoteAddress(false);
            //$ip = substr($ip, 0, strrpos($ip, '.') - 1);
            
            $useragent = func::getSERVER('HTTP_USER_AGENT', 'no user agent');
            $charset   = func::getSERVER('HTTP_ACCEPT_CHARSET', 'hello from IE');
            $fixHash   = md5($useragent.$charset);
            if (!isset($this->sessionData['hash'])) {
                $this->sessionData['hash'] = $fixHash;
            } elseif ($this->sessionData['hash'] != $fixHash) {
                if( !empty($_POST['sessid']) && 
                        (strpos(strtolower($useragent), 'adobe flash')!==false || 
                         in_array(strtolower($useragent), array('shockwave flash','adobe flash player 10')) )
                   ) 
                {
                    /*
                      swfupload:
                      HTTP_USER_AGENT = 'Shockwave Flash';
                      HTTP_ACCEPT_CHARSET = '';
                     */
                } else {    
                    session_regenerate_id();
                    $this->sessionData = array();
                    $this->sessionData['hash'] = $fixHash;
                }
            }    
        } 
    }

    function sessionNameByKey($key)
    {
        return 'bffss'.$key;
    }
    
    function sessionStart($key, $restart = false)
    {
        $name = $this->sessionNameByKey($key);         
        
        if($restart) {
            if(($sess_id = func::POSTGET('sessid'))) {
                session_id($sess_id);
                session_start();
            }
            //рестартуем сессию если есть сессионные куки                    
            else if( isset($_COOKIE[$name]) ) {
                session_name( $name );
                session_start();   
            }
        } else {
            //стартуем сессию если еще не стартовали                    
            $cur_session_id = session_id();
            if( empty( $cur_session_id ) ) {
                if(($sess_id = func::POSTGET('sessid'))) {
                    session_id($sess_id);
                } else {
                    session_name( $name );
                }
                session_start();   
            }
        }
    }
    
    function isLogined( $mGroupKeyword = null )
    {
        if(isset($mGroupKeyword))
        {
            if(!is_array($mGroupKeyword))
                $mGroupKeyword = array($mGroupKeyword);
            
            foreach($mGroupKeyword as $group) {
                if( $this->isUserInGroup($group) )
                    return TRUE;
            }            
            
            return FALSE;
        }
        
        return ($this->getUserID()!=0);
    }

    function logout($sRedirectURL = SITEURL, $bResetSessionID = true, $sessionKey = '', $sessionPath = '/', $sessionDomain = false)
    {
        $nUserID = $this->getUserID();
        if($nUserID){
            $this->clearRememberMe($sessionKey);
            
            if($bResetSessionID) {                            
                global $oDb;
                $oDb->execute('UPDATE '.TABLE_USERS.' SET session_id='.$oDb->str2sql('').' WHERE user_id='.$nUserID);
            }
        }
        
        //clear session data            
        $this->sessionData = array();
        
        $this->saveSession();

        setcookie(session_name(), FALSE, -1, $sessionPath, '.'. str_replace('http://', '', (!empty($sessionDomain) ? $sessionDomain :SITEURL)) ); 
        session_destroy();
        
        if($sRedirectURL!=-1) 
        {
            if(empty($sRedirectURL)) 
                $sRedirectURL = SITEURL;
            
            func::JSRedirect($sRedirectURL);
        }
    }    

    function impersonalizeSession($mSessionID, $aStoreData = null, $bDestroySession = false)
    {
        if(empty($mSessionID))
            return;
        
        # end current session
        $sCurrentSessionID = session_id();
        session_write_close(); 
                               
        # impersonalize session(s)
        if(!is_array($mSessionID))
            $mSessionID = array($mSessionID);
        
        foreach($mSessionID as $sid)
        {    
            session_id($sid);
            session_start();      
            if($bDestroySession) {
                session_destroy();
            }
            else {
                $aSessionData = unserialize( $_SESSION['SESSIONA'][$this->sSerializeVarname] );
                if(!empty($aSessionData) && !empty($aStoreData)) {
                    $aSessionData = array_merge($aSessionData, $aStoreData);
                    $_SESSION['SESSIONA'][$this->sSerializeVarname] = serialize( $aSessionData );
                }                                            
                session_write_close();
            }
        }
        
        # return current session
        session_id($sCurrentSessionID);
        session_start();
        
        return (isset($aSessionData)?$aSessionData:null); 
    }
    
    /**
    *  Управление информацией пользователя
    */
    function setUserInfo($nUserID, $sLogin='', $sEmail='', $aGroups=array(), $aAdditionalInfo = array()) 
    {                     
        if(!is_array($aGroups)) {
            $aGroups = array( $aGroups );
        }
        
        $this->sessionData['id'] = $nUserID;    
        $this->sessionData['ulogin'] = $sLogin;
        $this->sessionData['email'] = $sEmail;
        $this->sessionData['ugrps'] = $aGroups;  
        
        $this->sessionData['curr_session_id'] = session_id();
        $this->sessionData['login_time'] = time();     
        $this->sessionData['ip'] = htmlspecialchars( Func::getRemoteAddress() );
        $this->sessionData['expired'] = 0;
        
        if(!empty($aAdditionalInfo)) {
            foreach($aAdditionalInfo as $k=>$v)
                $this->sessionData[$k] = $v;
        }

        $this->saveSession();    
    }
    
    function expire()
    {
        $this->sessionData['expired'] = 1;
        $this->saveSession();
    }

    function checkExpired()
    {
        /*
        * проверяем выставлен ли флаг expired=1
        * если выставлен, тогда необходимо обновить информацию в сессии
        */       
        
        // выполняем проверку, только после коннекта к базе
        if(!empty($this->sessionData['expired']) && ($nUserID = $this->getUserID()))
        {                     
            global $oDb;

            # update 'users' => admin, moderator, member...
            $aData = $oDb->one_array('SELECT user_id, login, email, name, surname, avatar, member, admin, balance 
                                      FROM '.TABLE_USERS.' 
                                      WHERE user_id='.$nUserID.' LIMIT 1');
            if($aData)
            {   
                $aUserGroups = bff::i()->Users_getUserGroups($nUserID, true);
                if(empty($aUserGroups) && $aData['member']==1)
                    $aUserGroups = array(USERS_GROUPS_MEMBER);
                
                $this->setUserInfo($nUserID, $aData['login'], $aData['email'], 
                        $aUserGroups,
                        array('admin'=>$aData['admin'], 'avatar'=>$aData['avatar'], 'name'=>$aData['name'], 'surname'=>$aData['surname'])
                    );
            }  
        }
    }
    
    function updateUserInfo($aData = null) 
    {
        if(isset($aData) && is_array($aData) && count($aData)>0  && $this->sessionData['id']>0)
        {       
            if(isset($aData['login']))
                $this->sessionData['ulogin'] = $aData['login'];
                
            if(isset($aData['groups']))
                $this->sessionData['ugrps'] = $aData['groups'];

            foreach($aData as $k=>$v)
            {
                if(isset($this->sessionData[$k]))
                    $this->sessionData[$k] = $v;                
            }   
                         
            $this->saveSession();
            
            return true;
        }
        return false;
    }

    function setBalance($nBalance)
    {
        return $this->sessionData['balance'] = $nBalance;
    }
    
    function getBalance($bRefresh = true)
    {
        if($bRefresh) {
            global $oDb;
            $this->sessionData['balance'] = $oDb->one_data('SELECT balance FROM '.TABLE_USERS.' WHERE user_id = '.$this->getUserID());
        }
        return $this->sessionData['balance'];
    }
    
    function getUserInfo($userInfoType = null) {     
        if(isset($userInfoType)) {
            if(is_array($userInfoType))
            {
                if(!sizeof($userInfoType))
                    return $this->sessionData; 
                else {
                    $aResult = array();
                    foreach($userInfoType as $key) {
                        if(isset($this->sessionData[$key]))
                            $aResult[$key] = $this->sessionData[$key];
                    }
                    return $aResult;
                }
            }
            elseif(isset($this->sessionData[$userInfoType]))
                return $this->sessionData[$userInfoType];
        }
        return FALSE;
    }
    
	function getUserLogin() {
        return $this->getUserInfo('ulogin');
	}

    function getUserEmail() {
        return $this->getUserInfo('email');
    }
    
	function getUserID() {
        return (integer)$this->getUserInfo('id');
	}

    function getUserGroups() {     
        return $this->getUserInfo('ugrps');
    }

    /**
    * Получаем группы пользователя
    */
    function isUserInGroup() {
        if(empty($this->sessionData['id']) || empty($this->sessionData['ugrps']))
            return false;
            
        if(!func_num_args())
            return false;
        
        $groupKeywords = func_get_args();
        foreach($groupKeywords as $keyword) {
            if(in_array($keyword, $this->sessionData['ugrps']))
                return true;
        }
        return false;
    }

    function isCurrentUser($nUserID) {
        return ($this->getUserID() == $nUserID);
    }
    
    function isOnlyMember() {  
        if(!empty($this->sessionData['ugrps']) &&
           in_array(USERS_GROUPS_MEMBER, $this->sessionData['ugrps']) &&
           count($this->sessionData['ugrps'])==1
           ) {
            return TRUE; 
           }
       return FALSE;
    }
    
	function isMember() {
        return (integer)$this->isUserInGroup(USERS_GROUPS_MEMBER);
	}
    
    function isModerator() {
        return ($this->isUserInGroup(USERS_GROUPS_MODERATOR));
    }
    
    function isSuperAdmin() {
        return ($this->isUserInGroup(USERS_GROUPS_SUPERADMIN));
    }
    
    function isAdmin() {
        return $this->getUserInfo('admin');
    }

    /**
    *  Manage Sessions - Save
    */   
	function saveSession()
	{
		if(self::USEDB) {
			$this->setSESSION('curr_db_session_id',  $this->saveIntoDB());
		}
		else {
			$this->setSESSION('curr_db_session_id',  $this->saveIntoSession());
		}
	}

	function doSerialize()
	{
		if(isset($this->sCurrentDBSessionID)) {
		    $this->sessionData['curr_db_session_id'] = $this->sCurrentDBSessionID;
        }
		else {
		    $this->sessionData['curr_db_session_id'] = $this->getDatabaseSessionMD5();
        }

		return serialize($this->sessionData);
	}

	function saveIntoSession()
	{
		$this->setSESSION($this->sSerializeVarname, $this->doSerialize());
	}
    
	function saveIntoDB()
	{
		global $oDb;
        
		$bInsertNew = true;  

		if(isset($this->sCurrentDBSessionID) && $this->sCurrentDBSessionID)
		{
			$nCount = $oDb->one_data('SELECT COUNT(id) FROM '.DB_PREFIX.'sessions WHERE id = '.$oDb->str2sql($this->sCurrentDBSessionID).' LIMIT 1');
			if($nCount)
			{
				$bInsertNew = false;
				$oDb->execute('UPDATE '.DB_PREFIX.'sessions
                                SET session_data = '.$oDb->str2sql($this->doSerialize()).'
                                WHERE id = '.$oDb->str2sql($this->sCurrentDBSessionID));
			}
			else {
			    $this->sCurrentDBSessionID = $this->getDatabaseSessionMD5();
            }
		}

		if($bInsertNew)
		{
			$nInsertID = $this->getDatabaseSessionMD5();

			$oDb->execute('REPLACE INTO '.DB_PREFIX."sessions (id, session_data, datetime)
                           VALUES ( '$nInsertID', ".$oDb->str2sql($this->doSerialize()).", {$oDb->getNOW()} )");
                      
			$this->sCurrentDBSessionID = $nInsertID;
            $this->setSESSION('curr_db_session_id', $this->sCurrentDBSessionID);
		}

		return $this->sCurrentDBSessionID;
	}

	function getDatabaseSessionMD5()
	{
		return md5(session_name().session_id().'&^$$__@@*^');
	}

	/**
    *  Manage Sessions - Restore
    */
	function restoreSession()
	{   
		$res = false;

		if(isset($this->sCurrentDBSessionID))  {
			$res = (self::USEDB ? $this->restoreFromDB($this->sCurrentDBSessionID) : $this->restoreFromSession());
		}

		if(!$res && $this->getSESSION('curr_db_session_id')) {
			$res = (self::USEDB ? $this->restoreFromDB($this->getSESSION('curr_db_session_id')) : $this->restoreFromSession());
		}

		if(!$res)
		{
			$this->sCurrentDBSessionID = $this->getDatabaseSessionMD5();
			$res = (self::USEDB ? $this->restoreFromDB($this->sCurrentDBSessionID) : $this->restoreFromSession());
		}
                
		$this->setSESSION('curr_db_session_id', $this->sCurrentDBSessionID);
        
		return $res;
	}

	function restoreFromDB($sCurrentDBSessionID)
	{
		global $oDb;

		$serializeData = stripslashes( $oDb->one_data('SELECT session_data FROM '.DB_PREFIX.'sessions
                                                       WHERE id = '.$oDb->str2sql($sCurrentDBSessionID).'
                                                       LIMIT 1') );
		if($serializeData)
		{
			$this->sessionData = unserialize($serializeData);

			if(isset($this->sessionData['curr_db_session_id']))
			    $this->sCurrentDBSessionID = $this->sessionData['curr_db_session_id'];

			return TRUE;
		}
		return FALSE;
	}

	function restoreFromSession()
	{
		$serializeData = stripslashes( $this->getSESSION($this->sSerializeVarname) );
		if($serializeData)
		{
			$this->sessionData = unserialize($serializeData);

			if(isset($this->sessionData['curr_db_session_id']))
			    $this->sCurrentDBSessionID = $this->sessionData['curr_db_session_id'];

			return TRUE;
		}
		return FALSE;
	}

    function getSESSION($var) 
    {
        return (isset($_SESSION['SESSIONA'][$var]) ?
                    $_SESSION['SESSIONA'][$var] : false);
    }
    
    function setSESSION($var, $value) 
    {
        $_SESSION['SESSIONA'][$var] = $value;
    }

    function haveAccessToAdminPanel($nUserID = null)
    {
        global $oDb;
        
        if(!isset($nUserID)) {                
            #не залогинен
            if(!($nUserID = $this->getUserID())) return false;        
                  
            #состоит ли в группе SUPERADMIN или MODERATOR
            if($this->isUserInGroup(USERS_GROUPS_SUPERADMIN, USERS_GROUPS_MODERATOR)) 
                return true;
        }
        else {
            $nUserID = intval($nUserID);
        }
            
        //является ли пользователь членом группы, у которой есть доступ к админ панели.
        $nRes = (int)$oDb->one_data('SELECT count(G.group_id)
                  FROM '.TABLE_USER_IN_GROUPS.' UIG, '.TABLE_USERS_GROUPS.' G
                  WHERE UIG.user_id = '.$nUserID.' AND UIG.group_id = G.group_id AND G.adminpanel = 1
                  GROUP BY G.group_id
                  ORDER BY G.group_id ASC');
        if($nRes) return true;
        
        return false;
    }
    
	function haveAccessTo($sKeyword='')
	{
        list($sModule, $sMethod) = explode('::', $sKeyword);
        return $this->haveAccessToModuleToMethod($sModule, $sMethod);
	}

	function haveAccessToModuleToMethod($modulename = null, $methodname = null)
	{
        /** check @param $nUserID **/
        $nUserID = $this->getUserID();
        if(!$nUserID) return FALSE;
        
        //if user is in group(SUPERADMIN) [OK]
        if($this->isUserInGroup(USERS_GROUPS_SUPERADMIN))
            return TRUE;                                 
        
        /** check @param $modulename **/
		if(!empty($modulename)) {
			$modulename = mb_strtolower($modulename);
		}
		else {
			$modulename = Func::POSTGET('s', true);
            $modulename = mb_strtolower($modulename);
		}
        
        /** check @param $methodname **/
        if(!empty($methodname)) {
            $methodname = mb_strtolower($methodname);
        }
        
        //define opened modules and methods
		$aOpenMethods = array('users'=>array( 'login', 'logout', 'forgot' ));
       
        //if this module::method is opened [OK] 
		if(isset($aOpenMethods[$modulename]) && 
            is_array($aOpenMethods[$modulename]) && 
            in_array($methodname, $aOpenMethods[$modulename])) {
		    return TRUE;
        }

        
        #
        global $oDb;
        if(!isset($this->sessionData['ugrps_permissions'])) {
            //get user groups ids
            if(!isset($this->sessionData['ugrps_id'])) {
                $sQuery = 'SELECT UIG.group_id 
                           FROM '.TABLE_USER_IN_GROUPS.' UIG 
                           WHERE UIG.user_id='.$nUserID;
                $this->sessionData['ugrps_id'] = $oDb->select_one_column($sQuery); 
            }
            
            $sQuery = 'SELECT M.module, M.method
                        FROM '.TABLE_USERS_GROUPS_PERMISSIONS.' P,
                             '.TABLE_MODULE_METHODS.' M
                        WHERE P.unit_id in ('.implode(',', $this->sessionData['ugrps_id']).')
                            AND P.unit_type = '.$oDb->str2sql('group').'
                            AND P.item_type = '.$oDb->str2sql('module').'
                            AND P.item_id = M.id';
            $aMethodsTmp = $oDb->select($sQuery);
            
            $aMethods = array();
            foreach($aMethodsTmp as $v)
                $aMethods[$v['module']][] = $v['method'];
            
            $this->sessionData['ugrps_permissions'] = $aMethods;
            $this->saveSession();
        }
        else {
            $aMethods = & $this->sessionData['ugrps_permissions'][$modulename];
        }
        
		if(!$aMethods) {
            return FALSE;
        }

		//if module is opened (full access) [OK]
		if($modulename && in_array($modulename, $aMethods)) {
            return TRUE;
        }

        //if method is opened [OK]
		if($methodname && in_array($methodname, $aMethods)) {
            return TRUE;
        }
        
        if(!isset($methodname)) {
            return TRUE;
        }
        
		return FALSE;
	}    
    
    function isBBSCategoryAllowed( $nCategoryID )
    {
        if($this->isSuperAdmin()) return true;
        if($this->isModerator()) {
            $aCats = $this->getAllowedBBSCategories();    
            return in_array($nCategoryID, $aCats);
        }
        return false;
    }

    
    function getUID($json = true, $checkInput = false)
    {
        $key = 'bff_table';
        $uid_sess = $this->getSESSION($key);

        //берем из входящих данных если необходимо и не пусто
        if($checkInput!==false) {
            $uid_input = CInputCleaner::i()->$checkInput('uid', TYPE_STR);
            if(!empty($uid_input)) {
                $uid = $uid_input;
                //если в сессии не пусто и переданное не соответствует тому что в сессии, берем из сессии
                if(!empty($uid_sess) && $uid_sess!=$uid) $uid = $uid_sess;
                return  ($json ? func::php2js( array('r'=>$uid,'ce'=>false) ) : $uid);
            }
        }
        
        $uid = 0;
        $uid_cookie = func::getCOOKIE($key);
        if(empty($uid_cookie)) // куки пустые
        {
            if(!empty($uid_sess)) {
                $uid = $uid_sess; // берем из сессии, если там есть
                $uid_cookie = 1;  // помечаем что куки не пустые    
            } else {
                // генерируем новый UID
                $uid = md5(uniqid(mt_rand(),true)).'_'.substr(md5( (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : mt_rand(1,10000)) ), 0, 8);
                $this->setSESSION($key, $uid);                
            }
            func::setCOOKIE($key, $uid, (time() + (3600 * 24 * 730))); // 2 года
        } else { // берем из куков
            $uid = $uid_cookie;
            // если в сессии пусто или в куках не то, что в сессии => перезаписываем в сессии, то что взяли из куков
            if(empty($uid_sess) || $uid != $uid_sess) $this->setSESSION($key, $uid);
        }
        
        return ($json ? func::php2js( array('r'=>$uid,'ce'=>empty($uid_cookie)) ) : $uid);
    }    
    
    function getAllowedBBSCategories()
    {
        return $this->getUserInfo('cat');
    }

    function encodeBBSEditPass($sPass)
    {
        global $oDb;
        return 'AES_ENCRYPT('.$oDb->str2sql($sPass).', @bbs_editpass_encode_key)';
    }
    
    function decodeBBSEditPass($sPassField)
    {
        return 'AES_DECRYPT('.$sPassField.', @bbs_editpass_encode_key) as pass';
    }
    
    
    //------------------------------------------------------------------------------------
    /** Режим FORDEV */

    function isFORDEV()
    {
        $fordev = Func::POSTGET('fordev');

        if(!$fordev)
        $fordev = Func::SESSION('fordev'); 

        if ($fordev) {
            if ($fordev==='off') {
                $fordev = false;
                Func::setSESSION('fordev', false); 
            }
            else {
                Func::setSESSION('fordev', true); 
            }
        }

        if($fordev) 
            return TRUE;

        return FALSE;
    }
    
    function setFORDEV($bEnable = true)
    {
        Func::setSESSION('fordev', $bEnable);  
        $_POST['fordev'] = $_GET['fordev'] = $bEnable; 
    }
    
    function checkFORDEV()
    {
        if(BFF_DEBUG == 1 || $this->isFORDEV())
        {
            if(!$this->isSuperAdmin()) {
                $this->setFORDEV(false);
                ini_set('display_errors', '0');
                defined('FORDEV') or define('FORDEV', 0);
            }
            else {
                defined('FORDEV') or define('FORDEV', 1);
            }
        } 
        else 
        {
            defined('FORDEV') or define('FORDEV', 0);
        }
    }
        
    //------------------------------------------------------------------------------------
    
    function checkAuthorization(&$nUserID, $sGroupKeyword = null)
    {
        $nUserID = $this->getUserID();
        
        if($nUserID)
        {
            if(!empty($sGroupKeyword) && !$this->isUserInGroup($sGroupKeyword))
                return FALSE;

            return TRUE;
        }
        return FALSE;
    }


    /**
    * Проверяет забанен ли пользователь по id,ip,email. Если параметры не переданы, берем из текущей сессии.
    * Если параметр $return - false, тогда ничего не возвращаем, выводим сообщение и останавливаем выполнение скрипта.
    * @param string|array   $mUserIPs   строка с одним IP или массив IPшников
    */
    function checkBan($nUserID = false, $mUserIPs = false, $sUserEmail = false, $return = false)
    {
        global $oDb;

        $banned = false;
        $aQueryWhere = array();

        $sQuery = 'SELECT ip, uid, email, exclude, reason, finished FROM '.TABLE_USERS_BANLIST.' WHERE ';

        if($sUserEmail === false)
            $aQueryWhere[] = "email = ''";

        if($mUserIPs === false)
            $aQueryWhere[] = "(ip = '' OR exclude = 1)";

        if($nUserID === false)
            $aQueryWhere[] = '(uid = 0 OR exclude = 1)';
        else
        {
            $sql = '(uid = '.$nUserID;

            if($sUserEmail !== false)
                $sql .= " OR email <> ''";

            if($mUserIPs !== false)
                $sql .= " OR ip <> ''";

            $aQueryWhere[] = $sql.')';
        }

        $sQuery .= (sizeof($aQueryWhere) ? implode(' AND ', $aQueryWhere) : '');
        if(defined('CID')) $sQuery .= ' AND cid IN (0,'.CID.')';
        $aResult = $oDb->select($sQuery.' ORDER BY exclude ASC');
        
        $banTriggeredBy = 'user';
        foreach($aResult as $ban)
        {
            if($ban['finished'] && $ban['finished'] < time())
                continue;

            $ip_banned = false;
            if(!empty($ban['ip']))
            {
                if (!is_array($mUserIPs)) {
                    $ip_banned = preg_match('#^'.str_replace('\*', '.*?', preg_quote($ban['ip'], '#')).'$#i', $mUserIPs);
                }
                else
                {
                    foreach ($mUserIPs as $sUserIP) {
                        if (preg_match('#^'.str_replace('\*', '.*?', preg_quote($ban['ip'], '#')).'$#i', $sUserIP))
                        {
                            $ip_banned = true;
                            break;
                        }
                    }
                }
                if($ip_banned && !empty($ban['exclude'])) {
                    $ip_banned = false;
                    #echo 'unbanned by <b>ip</b><br />'; //debug
                }
            }

            if ((!empty($ban['uid']) && intval($ban['uid']) == $nUserID) ||
                $ip_banned ||
                (!empty($ban['email']) && preg_match('#^'.str_replace('\*', '.*?', preg_quote($ban['email'], '#')).'$#i', $sUserEmail)))
            {
                if (!empty($ban['exclude'])) {
                    $banned = false;
                    break;
                }
                else {
                    $banned  = true;
                    $banData = $ban;
                    
                    if (!empty($ban['uid']) && intval($ban['uid']) == $nUserID)
                        $banTriggeredBy = 'user';
                    else if ($ip_banned)
                        $banTriggeredBy = 'ip';
                    else
                        $banTriggeredBy = 'email';
                    # Не делаем break, т.к. возможно есть exclude правило для этого юзера
                    # echo 'banned by <b>'.$banTriggeredBy.'</b><br />'; //debug
                }
            }
        }

        //echo '<u>banned result: '.($banned?1:0).'</u>'; exit; //debug
        
        if($banned && !$return)
        {
            $aMessageLang = array(
                'BAN_PERMISSION' => 'Для получения дополнительной информации %2$sсвяжитесь с администратором%3$s.',
                'BAN_TIME'       => 'Доступ до <strong>%1$s</strong>.<br /><br />Для получения дополнительной информации %2$sсвяжитесь с администратором%3$s.', 
                'bannedby_email' => 'Доступ закрыт для вашего адреса email.',
                'bannedby_ip'    => 'Доступ закрыт для вашего IP-адреса.',
                'bannedby_user'  => 'Доступ закрыт для вашей учётной записи.', 
            );
            
            $tillDate = ($banData['finished'] ? date('Y-m-d H:i', $banData['finished']) : '');

            $message = sprintf($aMessageLang[ ($banData['finished'] ? 'BAN_TIME' : 'BAN_PERMISSION') ], $tillDate, '<a href="mailto:' . config::get('mail_admin', BFF_EMAIL_SUPPORT) . '">', '</a>');
            $message .= ($banData['reason'] ? '<br /><br /> Причина: <strong>'.$banData['reason'].'</strong>' : '');
            $message .= '<br /><br /><em>'.$aMessageLang['bannedby_'.$banTriggeredBy].'</em>';

            session_destroy();
            
            echo Errors::i()->showError('Доступ закрыт', $message, 'ban');
            exit;
        }

        return ($banned && $banData['reason'] ? $banData['reason'] : $banned);
    }
    
    # ------------------------------------------------------------------------------------
    # запомнить меня (remember me)
    
    function checkRememberMe($cookiePrefix = 'u')
    {
        if(!$this->isLogined() && $this->isRememberMe($cookiePrefix, $sLogin, $sPasswordMD5))
        {                
            $sPassword = bff::i()->Users_getUserPassword($sLogin);
            if($sPasswordMD5 === $this->getRememberMePasswordMD5($sPassword))
            {
                if( bff::i()->Users_userAUTH($sLogin, $sPassword)!=1)
                    $this->clearRememberMe($cookiePrefix);
                else {
                    $this->setRememberMe($cookiePrefix, $sLogin, $sPassword);
                }
            }
        }
    }
    
    function setRememberMe($cookiePrefix = '', $sLogin, $sPassword, $nDaysCount = 30) {
        func::setCOOKIE($cookiePrefix.'rmlgn', $sLogin, $nDaysCount);
        func::setCOOKIE($cookiePrefix.'rmpwd', $this->getRememberMePasswordMD5($sPassword), $nDaysCount);
    }

    function getRememberMePasswordMD5($sPassword) {
        return (md5(':324%^@(;95^&(478__?:)*'.md5($sPassword).';[[;!9%*S__#d$%'));
    }
    
    function isRememberMe($cookiePrefix = '', &$sLogin, &$sPassword) {
       $sLogin = func::getCOOKIE($cookiePrefix.'rmlgn');
       $sPassword = func::getCOOKIE($cookiePrefix.'rmpwd');
       return ($sLogin === false || $sPassword === false ? false : true);
    }
    
    function clearRememberMe($cookiePrefix = '') {
        func::setCOOKIE($cookiePrefix.'rmlgn', FALSE, -1);
        func::setCOOKIE($cookiePrefix.'rmpwd', FALSE, -1);
    }
    
    function getUserPasswordMD5($sPassword)
    {
        return md5(':324%^@(;9074__:)*'.md5($sPassword).';:;!%*__#$%');
    }
    
    # ------------------------------------------------------------------------------------
    # Шифрование (Mcrypt)
    
    /**
     * Шифрует данные по ключу.
     * @param string данные которые необходимо зашифровать.
     * @param mixed ключ шифрования.
     * @param boolen в формате base64.
     * @return string зашифрованные данные.
     * @throws Exception если PHP Mcrypt расширение не запущено.
     */
    public function encrypt($data, $key, $base64 = true)
    {                    
        if(func::extensionLoaded('mcrypt'))
        {
            $module=mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
            $key=substr(md5($key),0,mcrypt_enc_get_key_size($module));
            srand();
            $iv=mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
            mcrypt_generic_init($module,$key,$iv);
            $encrypted=$iv.mcrypt_generic($module,$data);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
            return ($base64 ? base64_encode($encrypted) : $encrypted);
        }
        else
            throw new Exception('SecurityClass requires PHP mcrypt extension to be loaded in order to use data encryption feature.');
    }

    /**
     * Расшифровывает данные по ключу.
     * @param string данные для расшифровки.
     * @param mixed ключ шифрования.  
     * @param boolen в формате base64.    
     * @return string расшифрованные данные
     * @throws Exception если PHP Mcrypt расширение не запущено
     */
    public function decrypt($data, $key, $base64 = true)
    {
        if(func::extensionLoaded('mcrypt'))
        {
            if($base64)$data=base64_decode($data);
            $module=mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
            $key=substr(md5($key),0,mcrypt_enc_get_key_size($module));
            $ivSize=mcrypt_enc_get_iv_size($module);
            $iv=substr($data,0,$ivSize);
            mcrypt_generic_init($module,$key,$iv);
            $decrypted=mdecrypt_generic($module,substr($data,$ivSize));
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
            return rtrim($decrypted,"\0");
        }
        else
            throw new Exception('SecurityClass requires PHP mcrypt extension to be loaded in order to use data encryption feature.');
    }
    
}
