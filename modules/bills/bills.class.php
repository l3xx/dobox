<?php

class Bills extends BillsBase
{
    var $errorTemplate = '';
    
    function init()
    {
        parent::init();
        config::set('ps_robox_on', 1);
        $this->errorTemplate = PATH_BASE.'tpl/error_ps.php';                  
    }

    function manage()
    {
        $nUserID = $this->security->getUserID();

        $sqlHistory = array(); 
        $sqlHistory[] = 'user_id = '.$nUserID;
        $sqlHistory[] = 'status != '.self::statusWaiting;
        $sqlHistory = join(' AND ', $sqlHistory);
        
        if(bff::$isAjax)
        {
            if(!$nUserID) {
                $this->ajaxResponse(Errors::ACCESSDENIED);
            }

            switch($this->input->get('act'))
            {
                case 'pay':
                {
                    $nPaymentSystem = $this->input->post('way',      TYPE_UINT); // способ оплаты
                    $sCurrency      = $this->input->post('currency', TYPE_STR);  // валюта суммы
                    $fAmount        = $this->input->post('amount',   TYPE_UNUM); // сумма
                    $nBillID        = $this->input->post('bill',     TYPE_UINT); // ID счета
                     
                    // проверяем сумму
                    if($fAmount < 1) {
                        $this->errors->set( _t('bills', 'Некорректная сумма заказа') );
                        $this->ajaxResponse( false );
                    }
                    
                    $fAmount = round($fAmount, 2);
                        
                    // способ оплаты только robox, валюта только rur
                    $fAmmountRUR = $fAmount;
                    $nPaymentSystem = self::psystemRobox;  
                    $sCurrency = 'rur';
                    $sDescription  = 'Пополнение счета';
                    
                    /*
                        if( $nPaymentSystem == self::psystemNone ) {
                            $this->ajaxResponse(Errors::IMPOSSIBLE);
                        }
                        
                        $exch = $this->exchangeArray($fAmount, $sCurrency);
                        $fAmmountRUR = $exch['rur'];
                        if(!in_array($sCurrency, array('rur', 'usd', 'eur', 'uah')))
                            $sCurrency = 'rur';
                        
                        $sDescription  = 'Пополнения счета системой '.$this->getPaymentSystemTitle( $nPaymentSystem );          
                    */     
                    
                    if( $nBillID ) { // проверяем переданный номер счета
                        $aBillData = $this->getBill($nBillID, $nUserID);
                        if(empty($aBillData) || $aBillData['status']!=self::statusWaiting) { 
                            $nBillID = 0; // указанный номер счета некорректный, или счет уже процессился
                        } else {
                            $fAmount = $aBillData['amount'];
                        }
                    }
                    if(!$nBillID) {
                        // создаем новый счет "пополнение счета"
                        $nBillID = $this->createBill_InPay($nUserID, $this->security->getUserInfo('balance'), $fAmount, $fAmmountRUR, $sCurrency, $nPaymentSystem, 
                                                          self::typeInPay, self::statusWaiting, $sDescription, 0, 0);
                        if(empty($nBillID)) $nBillID = 0;
                    }
                    
                    $aResponse = array();
                    if($nBillID) {
                        // формируем форму инициализации оплаты
                        $aResponse['form'] = $this->buildPayForm($fAmount, $nPaymentSystem, $nBillID, 0, 0);
                    } else {
                        $this->errors->set( _t('bills', 'Ошибка создания счета, обновите страницу и повторите попытку') );
                    }
                    $aResponse['res'] = $this->errors->no();
                    $this->ajaxResponse( $aResponse );
                } break;
                case 'history':
                {
                    $this->generatePagenationPrevNext('SELECT * FROM '.TABLE_BILLS.' 
                        WHERE '.$sqlHistory.' 
                        ORDER BY created DESC', $aData, 'bills', 5);
                                        
                    $this->ajaxResponse(array(
                        'list' => $this->tplFetchPHP($aData, 'history.ajax.php'),
                        'pgn'  => $aData['pgn'],
                    ));
                } break;
            }

        }
        
        if(!$nUserID) $this->redirect('/');
        
        $aData = array();

        $this->generatePagenationPrevNext('SELECT * FROM '.TABLE_BILLS.' 
                    WHERE '.$sqlHistory.' 
                    ORDER BY created DESC', $aData, 'bills', 5);
  
        $aData['balance'] = $this->security->getUserInfo('balance');
        
        config::set('userMenuCurrent', 3);
        config::set('title', 'Мой счет - '.config::get('title', ''));
        return $this->tplFetchPHP($aData, 'manage.php'); 
    }
    
    # ---------------------------------------------------------------------------------
    
    # Уведомление для WebMoney [OK]
    function webmoney() 
    {
        if( !config::get('ps_webmoney_on',0) ) {
            $this->log('Webmoney: способ оплаты отключен');
            return $this->payError('off');
        }
        
        extract ($_POST);
        
        $nBillID = intval($LMI_PAYMENT_NO);
        if($LMI_PREREQUEST==1) // предварительный запрос
        {
            if(!$nBillID)
                $this->wmError();
                                                                       
            $aBill = $this->getBill($nBillID);
            if(!$aBill) $this->wmError();                        
          
            # Проверка суммы
            $LMI_PAYMENT_AMOUNT = floatval(trim($LMI_PAYMENT_AMOUNT));
            if($aBill['money'] != $LMI_PAYMENT_AMOUNT) //сумма, которую пытается заплатить не равна указанной в счете 
                $this->wmError('Неверная сумма '.$LMI_PAYMENT_AMOUNT);
            
            $sPurseCorrect = $this->getWebmoneyPurse($aBill['psystem']);
            
            if($sPurseCorrect != trim($LMI_PAYEE_PURSE))
                $this->wmError('Неверный кошелек получателя '.$LMI_PAYEE_PURSE);
            
            $this->wmNoError();
            
        } else {
        
            if(empty($LMI_HASH)) {
                $this->log('Webmoney: параметр LMI_HASH пустой: "'.$LMI_HASH.'"');
                return $this->payError('no_params');
            }
                                       
            $str = $LMI_PAYEE_PURSE.$LMI_PAYMENT_AMOUNT.$LMI_PAYMENT_NO.$LMI_MODE.$LMI_SYS_INVS_NO.$LMI_SYS_TRANS_NO.
                   $LMI_SYS_TRANS_DATE.$this->getWebmoneyPurseSecret($aBill['psystem']).$LMI_PAYER_PURSE.$LMI_PAYER_WM;
            
//            $str_test = "{$LMI_PAYEE_PURSE}-{$LMI_PAYMENT_AMOUNT}-{$LMI_PAYMENT_NO}-{$LMI_MODE}-{$LMI_SYS_INVS_NO}-".
//                   "{$LMI_SYS_TRANS_NO}-{$LMI_SYS_TRANS_DATE}-".$this->getWebmoneyPurseSecret($aBill['psystem'])."-{$LMI_PAYER_PURSE}-{$LMI_PAYER_WM}";
//            CDir::putFileContent(PATH_BASE.'files/wmtest.txt', 'wm: bill('.$nBillID.'); '.$LMI_HASH.'=='.print_r($_POST,true).',   '.$str_test.', '.md5($str).',amount='.$fAmount); 
        
            if( mb_strtolower($LMI_HASH) === mb_strtolower(md5($str)) ) {
                $this->resultURL($nBillID, $LMI_PAYMENT_AMOUNT, $aBill['psystem']);            
            }
            else {   
                $this->log('Webmoney: неверная контрольная сумма "'.mb_strtolower($LMI_HASH).'" !== "'.mb_strtolower(md5($str)).'"');
                return $this->payError('crc_error');  
            }   
        
        }
    }
    
    # Уведомление для RBKMoney [OK] 
    function rbkmoney() 
    {
        if( !config::get('ps_rbkmoney_on',0) ) {
            $this->log('RBKMoney: способ оплаты отключен');
            return $this->payError('off');
        }

        extract($_POST);   

        if(empty($hash)) 
        {
            $this->log('RBKMoney: параметр hash пустой: "'.$hash.'"');
            return $this->payError('no_params');
        }
        
        if($orderId<=0) {
            $this->log('RBKMoney: Некорректный номер счета, (#'.$orderId.')');
            return $this->payError('wrong_bill_id');
        }

        if($paymentStatus == 3) //Платеж принят на обработку 
        {                                                              
            $this->changeBillStatus($orderId, self::statusProcessing);
            return 'OK';
        } 
        elseif($paymentStatus == 5) //Платеж зачислен
        {                                                               
            $str = config::get('ps_rbkmoney_id')."::$orderId::$serviceName::$eshopAccount::$recipientAmount::$recipientCurrency::$paymentStatus::$userName::$userEmail::$paymentData::".config::get('ps_rbkmoney_key');
            if( mb_strtolower($hash) === mb_strtolower(md5($str)) ) {
                $this->resultURL($orderId, $recipientAmount, self::psystemRBK);
                return 'OK';            
            }
            else {
                $this->log('RBKMoney: неверная контрольная сумма "'.mb_strtolower($hash).'" !== "'.mb_strtolower(md5($str)).'"');
                return $this->payError('crc_error');
            }
        }
        return $this->payError('pay_error');
    }    
    
    # Уведомление для Robox [OK]
    function robox1447() 
    {
        if(!config::get('ps_robox_on',0)) {
            $this->log('Robox: способ оплаты отключен');
            return $this->payError('off'); 
        }       
        
        $OutSum = (!empty($_REQUEST['OutSum']) ? $_REQUEST['OutSum'] : '');
        $InvId  = (!empty($_REQUEST['InvId']) ? $_REQUEST['InvId'] : '');
        $crc    = (!empty($_REQUEST['SignatureValue']) ? $_REQUEST['SignatureValue'] : '');       
        $shp_item = (isset($_REQUEST['shp_item']) ? $_REQUEST['shp_item'] : '');
        $shp_svc  = (isset($_REQUEST['shp_svc']) ? $_REQUEST['shp_svc'] : '');
        $shp_svcextra  = (isset($_REQUEST['shp_svcextra']) ? $_REQUEST['shp_svcextra'] : '');
        
        if(empty($crc)) {
            $this->log('Robox: параметр SignatureValue пустой: "'.$crc.'"');
            return $this->payError('no_params');
        }
            
        if(!is_numeric ($InvId)) {
            $this->log('Robox: Некорректный номер счета, (#'.$orderId.')');
            return $this->payError('wrong_bill_id');
        }
        
        $pass = config::get('ps_robox_pass2');
        $crc2 = strtoupper(md5("$OutSum:$InvId:$pass:shp_item=$shp_item:shp_svc=$shp_svc:shp_svcextra=$shp_svcextra"));
        
        if( strtoupper($crc) === $crc2 ) {
            if( $this->resultURL($InvId, $OutSum, self::psystemRobox, $shp_item, $shp_svc, $shp_svcextra) ) {
                echo "OK$InvId\n"; exit;
            }
        }
        else {
            $this->log('Robox: неверная контрольная сумма "'.strtoupper($crc).'" !== "'.$crc2.'"');
            return $this->payError('crc_error');
        }
    }    
    
    # Уведомление для Z-Payment 
    function zpay() 
    {
        if(!config::get('ps_zpay_on',0)) {
            $this->log('Z-Payment: способ оплаты отключен');
            return $this->payError('off');
        }
        
        extract($_POST);
    
        $zpayid = config::get('ps_zpay_id');  //$LMI_PAYEE_PURSE
        $zpkey  = config::get('ps_zpay_key'); //$LMI_SECRET_KEY       
        
        if(empty($LMI_HASH)) {    
            $this->log('Z-Payment: параметр LMI_HASH пустой: "'.$LMI_HASH.'"');
            return $this->payError('no_params');
        }
       
        /*
        ID магазина (LMI_PAYEE_PURSE); 
        Сумма платежа (LMI_PAYMENT_AMOUNT); 
        Внутренний номер покупки продавца (LMI_PAYMENT_NO); 
        Флаг тестового режима (LMI_MODE); 
        Внутренний номер счета в системе Z-PAYMENT (LMI_SYS_INVS_NO); 
        Внутренний номер платежа в системе Z-PAYMENT (LMI_SYS_TRANS_NO); 
        Дата и время выполнения платежа (LMI_SYS_TRANS_DATE); 
        Merchant Key (LMI_SECRET_KEY); 
        Кошелек покупателя в системе Z-PAYMENT или его e-mail (LMI_PAYER_PURSE); 
        Кошелек покупателя в системе Z-PAYMENT или его e-mail (LMI_PAYER_WM). 
        */
       
        $str = "{$LMI_PAYEE_PURSE}{$LMI_PAYMENT_AMOUNT}{$LMI_PAYMENT_NO}{$LMI_MODE}{$LMI_SYS_INVS_NO}".
               "{$LMI_SYS_TRANS_NO}{$LMI_SYS_TRANS_DATE}{$LMI_SECRET_KEY}{$LMI_PAYER_PURSE}{$LMI_PAYER_WM}";
        
        if( mb_strtolower($LMI_HASH) === mb_strtolower(md5($str)) ) {
            $this->resultURL($LMI_PAYMENT_NO, $LMI_PAYMENT_AMOUNT, self::psystemZPay);            
        }
        else {
            $this->log('Z-Payment: неверная контрольная сумма "'.mb_strtolower($LMI_HASH).'" !== "'.mb_strtolower(md5($str)).'"');
            return $this->payError('crc_error');
        }
    }
 
    # Оплата счёта на основе данных с платёжных систем
    private function resultURL($nBillID, $fAmount = 0, $nPaymentSystem = 0, $nItemID = 0, $nSvcID = 0, $mSvcExtra = 0)
    {
        $sPaymentSystem = $this->getPaymentSystemTitle( $nPaymentSystem ); 
        
        #Проверяем формат номера счета
        if (!is_numeric($nBillID)) {
            $this->log($sPaymentSystem.': некорректный номер счета, (#'.$nBillID.')');
            return $this->payError('wrong_bill_id', 2);
        }
        
        $aBill = $this->getBill($nBillID);
        
        if(empty($aBill)) {
            $this->log($sPaymentSystem.': Оплачен несуществующий счёт #'.$nBillID);
            return $this->payError('pay_error', 2); 
        }
        
        if($aBill['status'] == self::statusCanceled || $aBill['status'] == self::statusCompleted) 
        {
            $this->log($sPaymentSystem.': Оплачен уже ранее оплаченный счёт или счёт с другим статусом, #'.$nBillID);
            return $this->payError('pay_error', 2);
        }
        
        # Проверка суммы
        if($fAmount < $aBill['amount']) {
            $this->log("$sPaymentSystem: Сумма оплаты($fAmount) счета#$nBillID меньше выставленной ранее({$aBill['amount']})");
            return $this->payError('amount_error', 2);
        }
        
        if($this->changeBillStatus($nBillID, self::statusCompleted, $aBill['status']) !== false)
        {
            //Помечаем дату оплаты
            $res = $this->payBill($nBillID, false);
            if($res) 
            {
                $nUserID = $aBill['user_id'];
                
                if($nUserID > 0) {
                    // обновляем баланс пользователя (если такой есть)
                    $this->updateBalance($aBill['user_id'], $fAmount, '+');
                    
                    // помечаем текущий баланс в информации о счете
                    $aUserData = $this->db->one_array('SELECT login as email, balance FROM '.TABLE_USERS.' WHERE user_id = '.$nUserID);
                    $balance = (float)$aUserData['balance'];
                    $this->db->execute('UPDATE '.TABLE_BILLS.' SET user_balance = '.$balance.' WHERE id = '.$nBillID);
                } else {
                    $balance = $fAmount;
                }
                
                // активируем услугу, если такая была заявлена
                if($nItemID > 0 && $nSvcID > 0)
                {
                    /** @var Services module */
                    $oServices = bff::i()->GetModule('Services');
                    do {
                        $svc = $oServices->getServiceSettings( $nSvcID );
                        if(empty($svc)) break;
                            
                        $price = $svc['price'];
                        $sDescription = $oServices->buildServiceBillDescription($nSvcID, $nItemID);
                        
                        if($price > $balance) {
                            $this->log('Ошибка активации услуги в момент пополнения счета, недостаточно средств для активации ('.$balance.' из '.$price.')');
                            break;
                        }
                        
                        // создаем счет "активации услуги"
                        $nBillID = $this->createBill_OutService( $nItemID, $nSvcID, $nUserID, 0, $price, Bills::statusProcessing, $sDescription);
                        if(!$nBillID) break;
                        
                        // активируем услугу                
                        $res = $oServices->activateItemSvc($nItemID, $nSvcID, $mSvcExtra);
                        if(!$res) break; // ^ ошибки выставляются тут 
                        
                        // списываем с баланса пользователя
                        if($nUserID > 0) {
                            $res = $this->updateBalance($nUserID, $price, '-');
                            if($res) $balance -= $price;
                        } else {
                            $balance = 0;
                        }

                        //актуaлизируем информацию о счете
                        $this->updateBill($nBillID, $balance, false, self::statusCompleted);                        

                        if($nSvcID == Services::typePress && $nUserID > 0) {
                            // уведомляем о скором размещении в прессе
                            bff::sendMailTemplate(array('item_url'=>SITEURL.'/item/'.$nItemID, 'email'=>$aUserData['email']), 
                                'member_bbs_press_payed', $aUserData['email']);
                        }                    
                        
                    } while(false);
                }
            }
            return true;
        }
        return false;
    }

    private function payError( $sType, $mPrint = false )
    {
        $sMessage = '';
        switch( $sType ) 
        {
            case 'off':           $sMessage = 'Cпособ отключен';              break;  
            case 'no_params':     $sMessage = 'Не переданы параметры';        break;
            case 'crc_error':     $sMessage = 'Неверная контрольная сумма';   break;
            case 'amount_error':  $sMessage = 'Неверная сумма';               break;
            case 'wrong_bill_id': $sMessage = 'Неверный формат номера счёта'; break;  
            case 'pay_error':     $sMessage = 'Ошибка оплаты счёта';          break;
            case 'demo_fobidden': $sMessage = 'Демо режим запрещён';          break;
        }
        if($mPrint === true) {
           echo $this->errors->showError('Ошибка оплаты счёта', $sMessage, $this->errorTemplate);
           exit;
        } elseif($mPrint === 2) {
           echo $sMessage; exit;
        }
        
        return false;
    }
    
    # ---------------------------------------------------------------------------------
    function success() 
    {
        // пополнение и активация услуги
        $nItemID = (!empty($_REQUEST['shp_item']) ? $_REQUEST['shp_item'] : '');
        $nSvcID  = (!empty($_REQUEST['shp_svc']) ? $_REQUEST['shp_svc'] : '');
        if($nItemID > 0 && $nSvcID > 0) {
            /** @var Services module */
            $oServices = bff::i()->GetModule('Services'); 
            /** @var Bbs module */
            $oBbs = bff::i()->GetModule('Bbs');             
            $sTitle = 'Активация услуги';
            $sDesc  = 'Вы успешно активировали услугу';
            switch($nSvcID)
            {
                case Services::typePublicate: { 
                    $sTitle = 'Публикация'; 
                    $sDesc = 'Вы успешно опубликовали объявление.'; 
                    if($oBbs->isEditPassGranted($nItemID)) {
                        $aItemData = $this->db->one_array('SELECT id, '.$this->security->decodeBBSEditPass('pass').' FROM '.TABLE_BBS_ITEMS.' WHERE id = '.$nItemID);
                        $sDesc .= '<br/>Пароль к вашему объявлению: <b>'.$aItemData['pass'].'</b>';
                    }
                } break;
                case Services::typeUp:        { $sDesc = 'Вы успешно активировали услугу "Поднять объявление".';  } break;
                case Services::typeMark:      { $sDesc = 'Вы успешно активировали услугу "Выделить объявление".'; } break;
                case Services::typePremium:   { $sDesc = 'Вы успешно активировали услугу "Премиум".';             } break;
                case Services::typePress:     { $sDesc = 'Вы успешно активировали услугу "Публикация в прессе".'; } break;
            }
            return $this->errors->showSuccess($sTitle, $sDesc.'<br/><a href="'.SITEURL.'/item/'.$nItemID.'">перейти к объявлению № '.$nItemID.'</a>');
        }
        
        // только пополнение
        $nBalance = $this->security->getBalance(true); // актуализируем информацию о балансе в сессии
        return $this->errors->showSuccess('Пополнение счета', 'Вы успешно пополнили счет<br/>
            На вашем счету: <a href="'.SITEURL.'/bill">'.$nBalance.' $.</a>');
    }

    function fail() 
    {
        $InvId  = (!empty($_REQUEST['InvId']) ? $_REQUEST['InvId'] : '');
        return $this->errors->showSuccess('Пополнение счета', 'Не удалось выполнить оплату счета <b>№'.$InvId.'</b>.'); 
    }
    
}
