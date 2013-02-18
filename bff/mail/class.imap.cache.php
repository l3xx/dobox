<?php

/**
 * IMAPCache - кеширование результатов работы PHP-IMAP в текущую сессию
 */ 
 
class IMAPCache
{
    # Указатель на кеш в сессии
    public $_sessionCache = array();
    
    # Кешированные результаты imap_status() в массив
    var $_statusCache = array();

    # Была ли зарегистрирована shutdown функция
    var $_tosave = false;

    # Использовать другой способ кеширование (основанный не на сессиях)
    var $_driver = false;
    
    # Время жизни кешированных данных
    var $_lifetime = 86400; //24 hours

    /**
     * Возвращает ссылку на глобальный объект IMAPCache
     * Вызывать так: $imap_cache = &IMAPCache::singleton();
     * @return IMAPCache  глобальный объект IMAPCache
     */
    static function &singleton($driver = false)
    {
        static $object;
        if (!isset($object)) {
            $object = new IMAPCache($driver);
        }
        return $object;
    }

    function __construct($driver = false)
    {                      
        $this->_driver = $driver;
    }

    function init()
    {
        if(isset($_SESSION[IMAP_SESSION]['cache'])) 
        {
            $this->_sessionCache = &$_SESSION[IMAP_SESSION]['cache'];
            
        } else {
            $this->_sessionCache = &$_SESSION[IMAP_SESSION]['cache'];
            
            //формируем список ящиков            
            $imap_manager = &IMAPManager::singleton();
            $serverString = '{'.$imap_manager->server.'}';
            $bIsGmail = (strpos($serverString, 'gmail')!==FALSE);
            $aSkipUnseenMailboxes = ($bIsGmail ? array('Вся почта', 'Корзина', 'Отправленные', 'Помеченные', 'Черновики') : array());
            
            $offset = strlen($serverString); 
            $mboxes = @imap_list($imap_manager->stream(), $serverString, '*');   
            if (is_array($mboxes)) {
                foreach ($mboxes as $box) {
                    $box = substr($box, $offset); //remove server string
                    $status = @imap_status( $imap_manager->stream(), $serverString.$box, SA_ALL); 
                    $this->_sessionCache[$box] = array();
                    $this->_sessionCache[$box]['k'] = implode('|', array($status->messages, $status->uidnext, $status->uidvalidity)); 
                    $this->_sessionCache[$box]['name'] = ($box == 'INBOX' ? 'Входящие' : $imap_manager->decodeMailboxName( ($bIsGmail ? str_replace('[Gmail]/', '', $box) : $box ) ));
                    $this->_sessionCache[$box]['unseen'] = ($bIsGmail && in_array($this->_sessionCache[$box]['name'],$aSkipUnseenMailboxes)? -1 : $status->unseen);
                    $this->_sessionCache[$box]['d'] = array();
                    $this->_saveCache($box);
                }
            }
        }   
    }
    
    function clear($reinit = true)
    {
        foreach($this->_sessionCache as $mailbox=>$data)
            $this->expire($mailbox, null, 1|2);
            
        if($reinit) {
            unset( $_SESSION[IMAP_SESSION]['cache'] );
            $this->init();    
        }
    }
    
    function expireMessages($uids, $mailbox='')
    {
        if(empty($mailbox))
        {
            $imap_mngr = &IMAPManager::singleton();
            $mailbox = $imap_mngr->getCurrentMailbox();
        }
        if(!empty($mailbox) && isset($this->_sessionCache[$mailbox]) && count($uids)>0) {
            foreach($uids as &$uid)
                $uid = $this->getMessageCacheID($uid);
            $this->expire( $mailbox, $uids );
        }
    }
    
    /**
     * # Получаем данные из кеша
     * @param string $mailbox  полное название ящика '{hostname}mailbox'.
     * @param string $key      ключ.
     * @param boolean $check   проверить актуальность кеша ящика?
     * @return mixed           запрашиваемые данные, или false если таковых нет.
     */
    function get($mailbox, $key = null, $check = true) 
    {
        $res = $this->_getFromSession($mailbox, $key, $check); // пробуем найти в сессионном кеше
        //если не нашли, используем альтернативный вариант кеширования, если он заявлен
        if($this->_driver && ($res === false) && !isset($this->_sessionCache[$mailbox])) 
        {
            require_once PATH_CORE.'cache/Cache.php';
            $cache = &Cache::singleton($this->_driver, array());
            $res = $cache->get($this->_getDriverCacheID($mailbox), $this->_lifetime);
            if (($res === false) || !strval($res)) {
                $this->_sessionCache[$mailbox] = array('k' => null, 'd' => array());
            } else {
                //складываем в сессионный кеш
                $this->_sessionCache[$mailbox] = unserialize($res);
                $res = $this->_getFromSession($mailbox, $key, $check);
            }
        }
        return $res;
    }

    /**
     * Получение данных из кеша
     * @param string $mailbox  полное название ящика '{hostname}mailbox'.
     * @param string $key      ключ данных.
     * @param boolean $check   проверить актуальность кеша ящика?
     * @return mixed           Запрашиваемые данные, или false если таковых нет.
     */
    private function _getFromSession($mailbox, $key = null, $check = true)
    {
        if (isset($this->_sessionCache[$mailbox])) {
            if (!$check || $this->check($mailbox)) {
                $ptr = &$this->_sessionCache[$mailbox];
                if (!is_null($key)) {
                    if (isset($ptr['d'][$key])) {
                        return $ptr['d'][$key];
                    }
                } else {
                    return $ptr['d'];
                }
            }
        }
        return false;
    }
    
    /**
     * # Сохраняем/обновляем данные в сессионном кеше (и обновляем альтернативный кеш), 
     * сохраняя при этом уже существующие данные и текущий ключ. Если не было в кеше - создаем.
     * @param string $mailbox  полное название ящика '{hostname}mailbox'.
     * @param array $values    данные (ключ=>значение, ключ=>значение).
     */
    function set($mailbox, $values = array())
    {   
        //создаем, если не было
        if (!isset($this->_sessionCache[$mailbox])) {
            $this->_sessionCache[$mailbox] = array('k'=>$this->_getCacheID($mailbox), 'd' => array());
        }
        
        //складываем в сессионный кеш
        $ptr = &$this->_sessionCache[$mailbox];
        $ptr['d'] = array_merge($ptr['d'], $values);

        //откладываем в альтернативный вариант кеширования, если он заявлен
        $this->_saveCache($mailbox);
    }

    /**
     * # Проверяем актульность кеша ящика
     * @param string $mailbox  полное название ящика '{hostname}mailbox'.
     * @param boolean $update  нужно ли обновить cacheID и обнулить данные?
     * @return boolean         true - актуальный, false - нет.
     */
    function check($mailbox, $update = false)
    {
        if (isset($this->_sessionCache[$mailbox])) {
            $id = $this->_getCacheID($mailbox);
            if ($this->_sessionCache[$mailbox]['k'] == $id) {
                return true;
            } elseif ($update) {
                $this->set($mailbox);
            }
        } elseif ($update) {
            $this->set($mailbox);
        }
        return false;
    }
    
    /**
     * # Удаляем сессионный кеш (и обновляем альтернативный кеш).
     * @param string $mailbox  полное название ящика '{hostname}mailbox'.
     * @param mixed $key       ключ данных.
     * @param integer $mask    битовая маска для следующих апдейтов:
     * 1 = _sessionCache
     * 2 = кеш getStatus() 
     */
    function expire($mailbox, $key=null, $mask = 0)
    {
        if (!is_null($key) && isset($this->_sessionCache[$mailbox])) {
            $ptr = &$this->_sessionCache[$mailbox];
            if(is_array($key))
            {
                foreach($key as $k)
                  unset($ptr['d'][$k]);
            } else {
                unset($ptr['d'][$key]);
            }

            $this->_saveCache($mailbox);
            return true;
        }
        
        if ($mask & 1) {
            unset($this->_sessionCache[$mailbox]);
            $this->_saveCache($mailbox);
        }

        if ($mask & 2) {
            unset($this->_statusCache[$mailbox]);
        }
    }

    /**
     * # Получаем статус ящика (_statusCache, imap_status).
     * @param string $mailbox  полное название ящика '{hostname}mailbox'.
     * @return stdClass        объект imap_status() или пустая строка.
     */
    function getStatus($mailbox) //open stream if not cached!
    {              
        if(!isset($this->_statusCache[$mailbox])) {
            $imap_manager = &IMAPManager::singleton();
            $this->_statusCache[$mailbox] = @imap_status($imap_manager->stream(), $imap_manager->getServerString($mailbox), SA_ALL);
            if(!$this->_statusCache[$mailbox]) {
                unset($this->_statusCache[$mailbox]);
                if($err = imap_last_error()) {
                    //Horde::logMessage($err, __FILE__, __LINE__, PEAR_LOG_NOTICE);
                }
            }
        }
        return (empty($this->_statusCache[$mailbox]) ? '' : $this->_statusCache[$mailbox]);
    }

    /**
     * # Регистрируем shutdown функцию сохранения в альтернативный кеш.
     * @var string $mailbox  Записываем название ящика для сохранения.
     */
    private function _saveCache($mailbox)
    {
        if ($this->_tosave === false) {
            register_shutdown_function(array(&$this, '_shutdown'));
            $this->_tosave = array();
        }
        $this->_tosave[$mailbox] = true;
    }

    /**
     * # Shutdown функция
     * Сохраняет заявленные ящики используя альтернативный способ кеширования
     */
    function _shutdown()
    {
        if (!$this->_driver) {
            return;
        }

        $in_session = array();

        require_once PATH_CORE.'cache/Cache.php';
        $cache = &Cache::singleton($this->_driver, array());
        foreach (array_keys($this->_tosave) as $mailbox) {
            $cacheID = $this->_getDriverCacheID($mailbox);
            if (isset($this->_sessionCache[$mailbox])) {
                if ($cache->set($cacheid, serialize($this->_sessionCache[$mailbox]), $this->_lifetime)) {
                    //Horde::logMessage('Stored ' . $val . ' in cache. [User: ' . $_SESSION['imp']['uniquser'] . ']', __FILE__, __LINE__, PEAR_LOG_DEBUG);
                }
                $in_session[] = $mailbox;
            } else {
                $cache->expire($cacheID);
            }
        }

        # Удаляем сессионный кеш всех сохраненых ящиков
        foreach (array_diff(array_keys($this->_sessionCache), $in_session) as $mailbox) {
            unset($this->_sessionCache[$mailbox]);
        }
    }

    /**
     * # Возвращает driverCacheID.
     * @param string $mailbox
     */
    private function _getDriverCacheID($mailbox)
    {
        return 'imap_cache ' . $_SESSION[IMAP_SESSION]['settings']['user'] . '|' . $mailbox;
    }

    private function _getCacheID($mailbox)
    {           
        $status = $this->getStatus($mailbox);
        if (!empty($status)) {
            return implode('|', array($status->messages, $status->uidnext, $status->uidvalidity));
        } else {
            return $mailbox;
        }
    }
    
    function getMessageCacheID($uid)
    {           
        return 'm-'.$uid;
    }
}
  
?>
