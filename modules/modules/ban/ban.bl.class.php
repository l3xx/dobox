<?php

abstract class BanBase extends Module
{    
    var $securityKey = '770c61bd4a53a7d2cable06fe9fe2a60';
    
    /**
    * Проверяет забанен ли пользователь по ip,id,email. Если параметры не переданы, берем из текущей сессии.
    * Если параметр $return - false, тогда ничего не возвращаем, выводим сообщение и останавливаем выполнение скрипта.
    * @param string|array   $mUserIPs   строка с одним IP или массив IPшников
    */
    function checkBan($mUserIPs = false, $nUserID = false, $sUserEmail = false, $return = false)
    {
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
        $aResult = $this->db->select($sQuery.' ORDER BY exclude ASC');
        
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
                                                 
            $message = sprintf($aMessageLang[ ($banData['finished'] ? 'BAN_TIME' : 'BAN_PERMISSION') ], $tillDate, '<a href="mailto:' . config::get('mail_admin') . '">', '</a>');
            $message .= ($banData['reason'] ? '<br /><br /> Причина: <strong>'.$banData['reason'].'</strong>' : '');
            $message .= '<br /><br /><em>'.$aMessageLang['bannedby_'.$banTriggeredBy].'</em>';

            session_destroy();
            
            echo Errors::i()->showError('Доступ закрыт', $message, 'ban');
            exit;
        }

        return ($banned && $banData['reason'] ? $banData['reason'] : $banned);
    }
    
    /**
    * @desc  Создание бан-правила 
    * @param Режим бана: user, ip, email
    * @param Бан-items
    * @param Период бана
    * @param Период бана (дата, до которой банить)
    * @param Исключение
    * @param Описание бана
    * @param Причина бана (показываемая пользователю)
    */
    public function createBan($sMode, $ban, $banPeriod, $banPeriodDate, $nExclude = 0, $sDescription = '', $sReason = '')
    {
        // Удаляем просроченные баны
        $this->db->execute('DELETE FROM '.TABLE_USERS_BANLIST.' WHERE finished<'.time().' AND finished <> 0');

        $ban = (!is_array($ban)) ? array_unique(explode("\n", $ban)) : $ban;
        
        $nCurrentTime = time();

        // Переводим $banEnd в unixtime. 0 - постоянный бан.
        if($banPeriod)
        {
            if($banPeriod != -1 || !$banPeriodDate)
                $banEnd = max($nCurrentTime, $nCurrentTime + ($banPeriod) * 60);
            else {
                $banPeriodDate = explode('-', $banPeriodDate);
                if(sizeof($banPeriodDate) == 3 && ((int)$banPeriodDate[2] < 9999) &&
                   (strlen($banPeriodDate[2]) == 4) && (strlen($banPeriodDate[1]) == 2) && (strlen($banPeriodDate[0]) == 2))
                {
                    $banEnd = max($nCurrentTime, gmmktime(0, 0, 0, (int)$banPeriodDate[1], (int)$banPeriodDate[0], (int)$banPeriodDate[2]));
                }
                else
                {
                    trigger_error('Дата должна быть в формате <kbd>ДД-ММ-ГГГГ</kbd>.', E_USER_WARNING);
                }
            }
        }
        else
        {
            $banEnd = 0;
        }

        $aBanlistResult = array();

        switch ($sMode)
        {
            case 'user':
            {
                $type = 'uid';

                // Не поддеживается бан пользователей по групповому символу(*,?)

                $aUserlogins = array();

                foreach($ban as $userlogin)
                {
                    $userlogin = trim($userlogin);
                    if($userlogin != '')
                    {
                        if($userlogin == $this->security->getUserLogin())
                            trigger_error('Вы не можете закрыть доступ самому себе.', E_USER_WARNING);

                        $aUserlogins[] = $userlogin;
                    }
                }

                // Make sure we have been given someone to ban
                if(!sizeof($aUserlogins))
                    trigger_error('Имя пользователя не определено.', E_USER_WARNING);

                $aResult = $this->db->select_one_column('SELECT id FROM '.TABLE_USERS.' 
                                WHERE '.$this->db->prepareIN('login', $aUserlogins, false, false).' 
                                 AND id <> '.$this->security->getUserID());

                if(!empty($aResult))
                {
                    foreach($aResult as $uid)
                        $aBanlistResult[] = (integer)$uid;
                }
                else
                {
                    trigger_error('Запрашиваемых пользователей не существует.', E_USER_WARNING);
                }
                unset($aResult);
            } break;
            case 'ip':
            {
                $type = 'ip';
                
                foreach($ban as $banItem)
                {
                    if(preg_match('#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})[ ]*\-[ ]*([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#', trim($banItem), $ip_range_explode))
                    {    
                        // This is an IP range
                        $ip_1_counter = $ip_range_explode[1];
                        $ip_1_end = $ip_range_explode[5];

                        while($ip_1_counter <= $ip_1_end)
                        {
                            $ip_2_counter = ($ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[2] : 0;
                            $ip_2_end = ($ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[6];

                            if($ip_2_counter == 0 && $ip_2_end == 254)
                            {
                                $ip_2_counter = 256;
                                $ip_2_fragment = 256;

                                $aBanlistResult[] = "$ip_1_counter.*";
                            }

                            while($ip_2_counter <= $ip_2_end)
                            {
                                $ip_3_counter = ($ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[3] : 0;
                                $ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

                                if($ip_3_counter == 0 && $ip_3_end == 254)
                                {
                                    $ip_3_counter = 256;
                                    $ip_3_fragment = 256;

                                    $aBanlistResult[] = "$ip_1_counter.$ip_2_counter.*";
                                }

                                while($ip_3_counter <= $ip_3_end)
                                {
                                    $ip_4_counter = ($ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[4] : 0;
                                    $ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

                                    if($ip_4_counter == 0 && $ip_4_end == 254)
                                    {
                                        $ip_4_counter = 256;
                                        $ip_4_fragment = 256;

                                        $aBanlistResult[] = "$ip_1_counter.$ip_2_counter.$ip_3_counter.*";
                                    }

                                    while($ip_4_counter <= $ip_4_end)
                                    {
                                        $aBanlistResult[] = "$ip_1_counter.$ip_2_counter.$ip_3_counter.$ip_4_counter";
                                        $ip_4_counter++;
                                    }
                                    $ip_3_counter++;
                                }
                                $ip_2_counter++;
                            }
                            $ip_1_counter++;
                        }
                    }
                    else if(preg_match('#^([0-9]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})$#', trim($banItem)) || preg_match('#^[a-f0-9:]+\*?$#i', trim($banItem)))
                    {   
                        //Нормальный IP адрес
                        $aBanlistResult[] = trim($banItem);
                    }
                    else if(preg_match('#^\*$#', trim($banItem)))
                    {
                        //Баним все IP адреса
                        $aBanlistResult[] = '*';
                    }
                    else if(preg_match('#^([\w\-_]\.?){2,}$#is', trim($banItem)))
                    {
                        //Имя хоста
                        $ip_ary = gethostbynamel(trim($banItem));

                        if(!empty($ip_ary)) {
                            foreach($ip_ary as $ip) {
                                if($ip) {
                                    if(mb_strlen($ip) > 40)
                                        continue;

                                    $aBanlistResult[] = $ip;
                                }
                            }
                        }
                        trigger_error('Не удалось определить IP-адрес указанного хоста', E_USER_WARNING);
                    }
                    else
                    {
                        trigger_error('Не определён IP-адрес или имя хоста', E_USER_WARNING);
                    }
                }
            } break;
            case 'email':
            {
                $type = 'email';

                foreach($ban as $banItem)
                {
                    $banItem = trim($banItem);
                    if(preg_match('#^.*?@*|(([a-z0-9\-]+\.)+([a-z]{2,3}))$#i', $banItem))
                    {
                        if(strlen($banItem) > 100)
                            continue;

                        $aBanlistResult[] = $banItem;
                    }
                }

                if(sizeof($ban) == 0)
                    trigger_error('Не найдено правильных адресов электронной почты.', E_USER_WARNING);

            } break;
            default: {
                trigger_error('Не указан режим.', E_USER_WARNING);
            } break;
        }

        
        // Fetch currently set bans of the specified type and exclude state. Prevent duplicate bans.
        $aResult = $this->db->select_one_column("SELECT $type
            FROM ".TABLE_USERS_BANLIST.'
            WHERE '.( $type == 'uid' ? 'uid <> 0' : "$type <> ''").'
                AND exclude = '.(integer)$nExclude);

        if(!empty($aResult))
        {
            $aBanlistResultTemp = array();
            foreach($aResult as $item)
                $aBanlistResultTemp[] = $item;

            $aBanlistResult = array_unique(array_diff($aBanlistResult, $aBanlistResultTemp));
            unset($aBanlistResultTemp);
        }
        unset($aResult);


        //Есть что банить
        if(sizeof($aBanlistResult))
        {
            $nExclude     = (integer) $nExclude;
            $banEnd       = (integer) $banEnd;
            $sDescription = (string)  $sDescription;
            $sReason      = (string)  $sReason;
            
            $aEntries = array();
            foreach($aBanlistResult as $banItem)
            {
                $aEntries[] = array(
                    $type         => $banItem,
                    'started'     => $nCurrentTime,
                    'finished'    => $banEnd,
                    'exclude'     => $nExclude,
                    'description' => $sDescription,
                    'reason'      => $sReason
                );
            }
 

            $this->db->multiInsert(TABLE_USERS_BANLIST, $aEntries);

            // Если мы баним, тогда необходимо разлогинить пользователей подпадающих под бан
            if(!$nExclude && false)
            {
                $sQueryWhere = '';
                switch($sMode)
                {
                    case 'user': { $sQueryWhere = 'WHERE '.$this->db->prepareIN('session_user_id', $aBanlistResult, false, false); }break;
                    case 'ip':   { $sQueryWhere = 'WHERE '.$this->db->prepareIN('session_ip', $aBanlistResult, false, false); }break;
                    case 'email':{
                        $aQueryBanlist = array();
                        foreach ($aBanlistResult as $banEntry)
                            $aQueryBanlist[] = (string) str_replace('*', '%', $banEntry);

                        $aUsersIn = $this->db->select_one_column('SELECT id
                            FROM '.TABLE_USERS.'
                            WHERE '.$this->db->prepareIN('email', $aQueryBanlist, false, false));
                            
                        $sQueryWhere = 'WHERE '.$this->db->prepareIN('session_user_id', $aUsersIn, false, false);

                        unset($aUsersIn);
                    } break;
                }

                if (!empty($sQueryWhere))
                {
//                    $this->db->execute('DELETE FROM '.SESSIONS_TABLE.' '.$sQueryWhere);
//                    if ($sMode == 'user')
//                    {
//                        $this->db->execute( 'DELETE FROM '.SESSIONS_KEYS_TABLE.' 
//                            '.((in_array('*', $aBanlistResult)) ? '' : 'WHERE ' . $this->db->prepareIN('user_id', $aBanlistResult)) );
//                    }
                }
            }

            return true;
        }

        return false;
    }
    
    public function removeBan($mBanID)
    {
        // Удаляем просроченные баны
        $this->db->execute('DELETE FROM '.TABLE_USERS_BANLIST.' WHERE finished<'.time().' AND finished <> 0');

        if (!is_array($mBanID))
            $mBanID = array($mBanID);

        $aUnbanID = array_map('intval', $mBanID);
        if (sizeof($aUnbanID))
            $this->db->execute('DELETE FROM '.TABLE_USERS_BANLIST.' WHERE '.$this->db->prepareIN('id', $aUnbanID));

        return false;
    }
    
} 
