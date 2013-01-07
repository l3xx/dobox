<?php

abstract class BillsBase extends Module
{
    // статус счета
    const statusWaiting    = 1; //ожидает
    const statusCompleted  = 2; //завершен
    const statusCanceled   = 3; //отменен
    const statusProcessing = 4; //обрабатывается (принят на обработку)

    // тип счета
    const typeInPay      = 1;
    const typeInGift     = 2;
    const typeOutService = 5;
    
    // способ оплаты
    const psystemNone   = 0;
    const psystemWMR    = 1;
    const psystemWMZ    = 2;
    const psystemWME    = 3;
    const psystemWMU    = 4;
    const psystemRobox  = 5;
    const psystemZPay   = 6;
    const psystemRBK    = 7;
    
    function init()
    {                  
        parent::init();
        
        config::set(array(
            'robox_on'    => true, 
            'robox_login' => '123567',
            'robox_pass1' => 'xxxcccvvv',
            'robox_pass2' => 'xxccvvbbnn',
            'webmoney_on'     => false,
//            'webmoney_id'     => '000', //webmoney xml интерфейсы
//            'webmoney_secret' => '000', //webmoney xml интерфейсы
//            'webmoney_wmz'        => '000',
//            'webmoney_wmz_secret' => '000',
//            'webmoney_wmr'        => '000',
//            'webmoney_wmr_secret' => '000',
//            'webmoney_wme'        => '000',
//            'webmoney_wme_secret' => '000',
//            'webmoney_wmu'        => '000',
//            'webmoney_wmu_secret' => '000',
            'rbkmoney_on'  => false,
//            'rbkmoney_id'  => '000',
//            'rbkmoney_key' => '000',
            'zpay_on'  => false,
//            'zpay_id'  => '000',
//            'zpay_key' => '000',
            ), false, 'ps_');
        
    }
    
    /**
    * Конвертер валют по курсам
    * @param string $fAmount сумма
    * @param string $sCurrency валюта, в которой указана сумма: rur, usd, eur, uah
    * @param float $usdkurs курс USD
    * @param float $eurkurs курс EUR
    * @param float $uahkurs курс UAH
    * @return array массив сконвертированной по курсам суммы
    */
    protected function exchangeArray ($fAmount, $sCurrency, $usdkurs = false, $eurkurs = false, $uahkurs = false) 
    {
        $v = array ();
        
        if (!$usdkurs)
            $usdkurs = config::get('exchange_usd');
        
        if (!$eurkurs)
            $eurkurs = config::get('exchange_eur');
        
        if (!$uahkurs)
            $uahkurs = config::get('exchange_uah');
        
        //Начинаем отбор по типу валюту
        switch ($sCurrency) 
        {
            case 'rur':
            {
                $v['rur'] = $fAmount;
                $v['usd'] = round ($v['rur']/$usdkurs,2);
                $v['eur'] = round ($v['rur']/$eurkurs,2);
                $v['uah'] = round ($v['rur']/$uahkurs,2);
            }
            break;
            case 'usd':
            {
                $v['usd'] = $fAmount;
                $v['rur'] = round ($v['usd']*$usdkurs,2);
                $v['eur'] = round ($v['rur']/$eurkurs,2);
                $v['uah'] = round ($v['rur']/$uahkurs,2);
            }
            break;
            case 'eur':
            {
                $v['eur'] = $fAmount;
                $v['rur'] = round ($v['eur']*$eurkurs,2);
                $v['usd'] = round ($v['rur']/$usdkurs,2);
                $v['uah'] = round ($v['rur']/$uahkurs,2);
            }
            break;
            case 'uah':
            {
                $v['uah'] = $fAmount;
                $v['rur'] = round ($v['uah']*$uahkurs,2);
                $v['eur'] = round ($v['rur']/$eurkurs,2);
                $v['usd'] = round ($v['rur']/$usdkurs,2);
            }
            break;
        }
        return $v;  
    }
    
    /**
    * Изменение статуса счета
    * @param integer $nBillID ID счета
    * @param integer $nStatusNew новый статус
    * @param integer $nStatusPrev предыдущий статус
    * @return boolean
    */
    protected function changeBillStatus($nBillID, $nStatusNew, $nStatusPrev = null)
    {
        if(!in_array($nStatusNew, array(self::statusWaiting, self::statusCompleted, self::statusCanceled, self::statusProcessing)))
            return false;

        //Если статус совпадает, то ничего не меняем
        if (isset($nStatusPrev) && $nStatusNew == $nStatusPrev) {
            return false;
        }
            
        $res = $this->db->execute('UPDATE '.TABLE_BILLS.' SET status = '.$nStatusNew.' WHERE id='.$nBillID);
        return !empty($res);
    }
    
    /**
    * Фиксируем дату оплаты счета
    * @param integer $nBillID ID счета
    * @param integer $nStatus новый статус
    * @param string $sDescription описание
    * @return boolean
    */
    protected function payBill($nBillID, $sDetails = false, $nStatus = false)
    {
        $res = $this->db->execute('UPDATE '.TABLE_BILLS.' 
                       SET payed = '.$this->db->getNOW().'
                            '.($nStatus!==false ? ', status = '.$nStatus : '').'
                            '.($sDetails!==false ? ', details = '.$this->db->str2sql($sDetails) : '').'
                       WHERE id='.$nBillID);
        
        $success = !empty($res);
        if(!$success) {
            $this->log('Неудалось обновить дату оплаты счета #'.$nBillID);
        }
        return $success;
    }

    /**
    * Создание счета, тип: пополнение счета
    * @param integer $nUserID ID пользователя
    * @param float $fUserBalance текущий баланс пользователя
    * @param float $fAmount сумма счета
    * @param float $fMoney сумма счета в основной валюте
    * @param string $sCurrency валюта: rur,usd,eur,uah 
    * @param integer $nPaymentSystem системы оплаты: Bills::psystem
    * @param integer $nType тип счета: Bills::type
    * @param integer $nStatus статус счета: Bills::status
    * @param string $sDescription описание
    * @param integer $nItemID id объявления
    * @param integer $nSvcID id услуги
    * @return integer ID счета
    */
    function createBill_InPay( $nUserID, $fUserBalance, $fAmount, $fMoney, $sCurrency, $nPaymentSystem, $nType, $nStatus, $sDescription = '', $nItemID = 0, $nSvcID = 0 ) 
    {
        $this->db->execute('INSERT INTO '.TABLE_BILLS.' (user_id, item_id, svc_id, user_balance, psystem, type, status, amount, money, currency, description, created, ip) 
            VALUES( '.$nUserID.', '.$nItemID.', '.$nSvcID.', '.$fUserBalance.', '.$nPaymentSystem.', '.$nType.', '.$nStatus.', '.$fAmount.', '.floatval($fMoney).', 
                   :curr, :desc, '.$this->db->getNOW().', :ip)', 
            array(':curr'=>$sCurrency, ':desc'=>$sDescription, ':ip'=>func::getRemoteAddress() ));

        $nBillID = $this->db->insert_id(TABLE_BILLS, 'id');
        if(empty($nBillID)) {
            $this->log('Неудалось создать счет: пополнение счета');
            $nBillID = 0;
        }
            
        return $nBillID;
    }
    
    /**
    * Создание счета, тип: активация услуги
    * @param integer $nItemID ID объявления
    * @param integer $nSvcID ID услуги 
    * @param integer $nUserID ID пользователя
    * @param float $fUserBalance текущий баланс пользователя
    * @param float $fAmount сумма счета
    * @param integer $nStatus статус счета: Bills::status_
    * @param string $sDescription описание
    * @return integer ID счета
    */
    function createBill_OutService( $nItemID, $nSvcID, $nUserID, $fUserBalance, $fAmount, $nStatus, $sDescription = '' ) 
    {
        $now = $this->db->getNOW();
        $this->db->execute('INSERT INTO '.TABLE_BILLS.' (user_id, user_balance, item_id, svc_id, type, status, amount, description, created, payed, ip) 
            VALUES( '.$nUserID.', '.$fUserBalance.', '.$nItemID.', '.$nSvcID.', '.(self::typeOutService).', '.$nStatus.', '.$fAmount.', 
                   :desc, '.$now.', '.$now.', :ip)', 
            array(':desc'=>$sDescription, ':ip'=>func::getRemoteAddress()));

        $nBillID = $this->db->insert_id(TABLE_BILLS, 'id');
        if(empty($nBillID)) {
            $this->log('Неудалось создать счет: активация услуги');
            $nBillID = 0;
        }
        
        return $nBillID;
    }
    
    
    /**
    * Создание счета, тип: подарок
    * @param integer $nUserID ID пользователя
    * @param float $fUserBalance текущий баланс пользователя
    * @param float $fAmount сумма счета
    * @param string $sDescription описание
    * @return integer ID счета
    */
    function createBill_InGift($nUserID, $fUserBalance, $fAmount, $sDescription = '')
    {
        $now = $this->db->getNOW();
        $this->db->execute('INSERT INTO '.TABLE_BILLS.' (user_id, user_balance, type, status, amount, description, created, payed, ip) 
            VALUES( '.$nUserID.', '.$fUserBalance.', '.(self::typeInGift).', '.self::statusCompleted.', '.$fAmount.', 
                    :desc, '.$now.', '.$now.', :ip)', 
            array( ':desc'=>$sDescription, ':ip'=>func::getRemoteAddress() ));

        $nBillID = $this->db->insert_id(TABLE_BILLS, 'id');
        if(empty($nBillID)) {
            $this->log('Неудалось создать счет: подарок');
            $nBillID = 0;
        }
        
        return $nBillID;
    }
    
    /**
    * Обновление счета
    * @param integer $nBillID ID счета
    * @param float $fUserBalance текущий баланс пользователя
    * @param string $sDetails подробности     
    * @param integer $nStatus статус счета
    * @param string $sDescription описание
    * @return boolean
    */
    function updateBill( $nBillID, $fUserBalance, $sDetails = false, $nStatus = false, $sDescription = false ) 
    {
        $res = $this->db->execute('UPDATE '.TABLE_BILLS.' 
            SET user_balance = '.$fUserBalance.'
                '.(!empty($sDetails) ? ', details = '.$this->db->str2sql($sDetails) : '').'
                '.($nStatus!==false ? ', status = '.$nStatus : '').'
                '.($sDescription!==false ? ', description = '.$this->db->str2sql($sDescription) : '').'
            WHERE id = '.$nBillID);

        $success = !empty($res);
        if(!$success) {
            $this->log('Неудалось обновить счет #'.$nBillID);
        }
            
        return $success;
    }
    
    protected function getPaymentSystemTitle( &$mPaymentSystem )
    {
        if(is_string($mPaymentSystem)) 
        {
            switch($sPaymentSystem)
            {
                case 'wmr':     { $mPaymentSystem = self::psystemWMR;   }break;
                case 'wmz':     { $mPaymentSystem = self::psystemWMZ;   }break; 
                case 'wme':     { $mPaymentSystem = self::psystemWME;   }break;
                case 'wmu':     { $mPaymentSystem = self::psystemWMU;   }break;
                case 'robox':   { $mPaymentSystem = self::psystemRobox; }break; 
                case 'zpay':    { $mPaymentSystem = self::psystemZPay;  }break; 
                case 'rbkmoney':{ $mPaymentSystem = self::psystemRBK;   }break;     
                default:        { $mPaymentSystem = self::psystemNone;  }break;
            }
        }
        
        $sTitle = '';
        switch( $mPaymentSystem )
        {
            case self::psystemWMR:   { $sTitle ='Webmoney R'; }break;
            case self::psystemWMZ:   { $sTitle ='Webmoney Z'; }break; 
            case self::psystemWME:   { $sTitle ='Webmoney E'; }break;
            case self::psystemWMU:   { $sTitle ='Webmoney U'; }break; 
            case self::psystemRobox: { $sTitle ='Robox';      }break; 
            case self::psystemZPay:  { $sTitle ='Z-Pay';      }break; 
            case self::psystemRBK:   { $sTitle ='RBKMoney';   }break;  
            default: { $sTitle = '?'; }break;
        }
        
        return $sTitle;        
    }
    
    protected function getStatusOptions($nSelectedStatusID = null, $aSkipStatus = array())
    {
        $aStatus = array(self::statusWaiting    => 'незавершен',
                         self::statusCompleted  => 'завершен',
                         self::statusCanceled   => 'отменен', 
                         self::statusProcessing => 'обрабатывается');
        
        $sHTML = '';
        foreach($aStatus as $key=>$value)
        {
            if(in_array($key, $aSkipStatus)) continue;
                $sHTML .= '<option value="'.$key.'"'.(isset($nSelectedStatusID) && $nSelectedStatusID == $key?' selected':'').'>'.$value.'</option>';
        }
        
        return $sHTML;
    }
    
    /**
    * Обновление состояния счета
    * @param integer $nUserID ID пользователя
    * @param integer $fMoney сумма
    * @param string $sAction "-" расход, "+" приход
    * @param boolean $bUpdateSpentStatistic обновлять суммарную статистику расходов
    * @param boolean $bAddToRating прибавлять к рейтингу
    */
    function updateBalance($nUserID, $fMoney, $sAction)
    {
        if(!$nUserID) return true;
        
        if($sAction!='-' && $sAction!='+')
            return false;
        
        $fMoney = floatval($fMoney);    
        
        $res = $this->db->execute('UPDATE '.TABLE_USERS." SET balance = balance $sAction $fMoney WHERE user_id = $nUserID");
        $success = !empty($res);
        if(!$success) {
            $this->log('Неудалось обновить баланс пользователя #'.$nUserID." ($sAction $fMoney)");
        }
        
        return $success;        
    }

    protected function getBill($nBillID, $nUserID = false)
    {
        return $this->db->one_array('SELECT * FROM '.TABLE_BILLS.' WHERE id = '.$nBillID.(!empty($nUserID) ? ' AND user_id = '.$nUserID : '').' LIMIT 1');
    }

    protected function getWebmoneyPurse($nPaymentSystem)
    {
        switch($nPaymentSystem) {
            case self::psystemWMR: return config::get('ps_webmoney_wmr'); break;
            case self::psystemWMZ: return config::get('ps_webmoney_wmz'); break;
            case self::psystemWME: return config::get('ps_webmoney_wme'); break;
            case self::psystemWMU: return config::get('ps_webmoney_wmu'); break;
        }  
    }       
    
    protected function getWebmoneyPurseSecret($nPaymentSystem)
    {
        switch($nPaymentSystem) {
            case self::psystemWMR: return config::get('ps_webmoney_wmr_secret'); break;
            case self::psystemWMZ: return config::get('ps_webmoney_wmz_secret'); break;
            case self::psystemWME: return config::get('ps_webmoney_wme_secret'); break;
            case self::psystemWMU: return config::get('ps_webmoney_wmu_secret'); break;
        }  
    }
    
    protected function wmError($sMessage = 'Неизвестная ошибка')
    {
        echo 'ERR: '.$sMessage;
        exit;
    }
    
    protected function wmNoError()
    {
        echo 'YES';
        exit;
    }
    
    protected function log($sMessage)
    {
        func::log($sMessage, 'bill.log');
    }
    
    /**
    * Формирование формы инициализации оплаты
    * @param float $fAmout сумма
    * @param integer $nPaymentSystem способ оплаты
    * @param integer $nBillID id счета
    * @param integer $nItemID id объявления
    * @param integer $nSvcID id услуги
    */
    public function buildPayForm($fAmout, $nPaymentSystem, $nBillID, $nItemID = 0, $nSvcID = 0, $mSvcExtra = 0)
    {
        $aData = array(
            'amount'    => $fAmout, 
            'psystem'   => $nPaymentSystem, 
            'bill_id'   => $nBillID,
            'item_id'   => $nItemID,
            'svc_id'    => $nSvcID,
            'svc_extra' => $mSvcExtra,
        );
        return $this->tplFetchPHP( $aData, 'psystem.form.php' );        
    }

}
