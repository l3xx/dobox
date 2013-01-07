<?php

class Bills extends BillsBase
{
    function listing()
    {
        if(!$this->haveAccessTo('listing'))
            return $this->showAccessDenied();

        switch($this->input->getpost('act', TYPE_STR))
        {
            case 'ubalance-add':
            {
                if(!$this->haveAccessTo('edit')) {
                    return $this->showAccessDenied();
                }
                
                $p = $this->input->postm(array(
                    'uid'         => TYPE_UINT,
                    'amount'      => TYPE_UNUM,
                    'description' => TYPE_STR,
                )); extract($p, EXTR_REFS);
                
                if(!$this->security->isSuperAdmin())
                    return $this->showAccessDenied();
                
                if(!$uid || $amount<=0 || empty($description)) 
                    return $this->showImpossible();
                
                $aUserData = $this->db->one_array('SELECT user_id, balance FROM '.TABLE_USERS.' WHERE user_id = '.$uid);
                if(empty($aUserData)) return $this->showImpossible();
                
                $res = $this->updateBalance($uid, $amount, '+');
                if(empty($res)) return $this->showImpossible();
                
                $nBillID = $this->createBill_InGift($uid, $aUserData['balance'] + $amount, $amount, $description);
                
                $this->adminRedirect( !empty($nBillID) ? Errors::SUCCESS : Errors::IMPOSSIBLE, 'listing&uid='.$uid );
                                
            } break;
        }
        
        $sInputSource = (bff::$isAjax ? 'postm' : 'getm');
        
//        if(!bff::$isAjax && !isset($_GET['type']) && empty($_GET['uid'])) {
//            $_GET['type'] = self::typeInPay;
//        }

        $f = $this->input->$sInputSource(array(
            'id'      => TYPE_UINT,
            'item'    => TYPE_UINT, 
            'uid'     => TYPE_UINT,
            'status'  => TYPE_UINT,
            'type'    => TYPE_UINT,
            'offset'  => TYPE_UINT,
            'p_from'  => TYPE_STR,
            'p_to'    => TYPE_STR,            
        ));
          
        $aData = array();
        $aData['f'] = &$f;
        $aData['orders'] = array('id'=>'desc', 'created'=>'desc');
        
        $sql = array(); 
        $sql[] = '1=1';
        if($f['id']>0) {
            $sql[] = 'B.id = '.$f['id'];
        }
        if($f['item']>0) {
            $sql[] = 'B.item_id = '.$f['item'];
        }        
        if($f['uid']>0) {
            $aData['user'] = $this->db->one_array('SELECT user_id as id, login FROM '.TABLE_USERS.' WHERE user_id = '.$f['uid']);
            if(!empty($aData['user'])) { $sql[] = 'B.user_id = '.$f['uid']; } else { $f['uid'] = 0; }            
        }
        if($f['status']>0) {
            $sql[] = 'B.status = '.$f['status'];
        }
        
        if($f['type']>0) {  
            $sql[] = 'B.type = '.$f['type'];
        } 
        
        if(!empty($f['p_from']) || !empty($f['p_to'])) 
        {
            $from = strtotime($f['p_from']);
            $to = strtotime($f['p_to']);

            if(!empty($from) && $from!=-1) {
                $sql[] = 'B.created >= '.$this->db->str2sql(date('Y-m-d 00:00:00', $from));
            }
            if(!empty($to) && $to!=-1) {
                $sql[] = 'B.created <= '.$this->db->str2sql(date('Y-m-d 23:59:59', $to));
            }
        }
        
        $this->prepareOrder($orderBy, $orderDirection, 'id,desc', $aData['orders']);
        $this->tplAssigned(array('order_by', 'order_dir', 'order_dir_needed'), $f);
        $f['order'] = $orderBy.','.$orderDirection;
        
        $this->generatePagenationPrevNext('SELECT B.*, U.name, U.login FROM '.TABLE_BILLS.' B
                            LEFT JOIN '.TABLE_USERS.' U ON B.user_id = U.user_id
                          WHERE '.join(' AND ', $sql).'
                          ORDER BY B.id DESC', $aData, 'bills', 25, 'jBills');

        if(bff::$isAjax) {
            //echo '<pre>', print_r($sql, true), '</pre>'; exit;
            $this->ajaxResponse( array(
                'list'   => $this->tplFetchPHP($aData, 'admin.listing.ajax.php'), 
                'pgn'    => $aData['pgn'],
                'filter' => $f,
            ) );
        }          
 
        $aData['access_edit'] = $this->haveAccessTo('edit');
 
        $aData['status_options']  = $this->getStatusOptions($f['status']);
        $this->adminCustomCenterArea();
        return $this->tplFetchPHP($aData, 'admin.listing.php');
    }

    function ajax()
    {
        if(!bff::$isAjax)
            $this->ajaxResponse(Errors::ACCESSDENIED);

        $nBillID = $this->input->post('bid', TYPE_UINT);
            
        switch(func::GET('act'))
        {
            case 'user-autocomplete': #autocomplete 
            {               
                $sQ = $this->input->post('q', TYPE_STR);
                //получаем список подходящих по логину пользователей, исключая:
                // - неактивированных пользователей
                $aResult = $this->db->select('SELECT U.user_id as id, U.login FROM '.TABLE_USERS.' U 
                              WHERE U.activated = 1
                                AND U.login LIKE ('.$this->db->str2sql("$sQ%").')                                    
                              ORDER BY U.login
                              LIMIT 12');
                
                $aUsers = array();
                foreach($aResult as $u) {
                    $aUsers[$u['id']] = $u['login'];
                } unset($aResult);
                              
                $this->ajaxResponse($aUsers);
            } break;
            /**
            * Изменение статуса счета:
            * @param integer $nStatus ID статуса, допустимые: завершен, отменен
            */            
            case 'status':
            {
                if(!$this->haveAccessTo('edit')) {
                      $this->ajaxResponse(Errors::ACCESSDENIED);
                }
                
                if(!$nBillID) $this->ajaxResponse(Errors::IMPOSSIBLE);
                                
                $nStatus = $this->input->post('status', TYPE_UINT);
                
                if(!in_array($nStatus, array(self::statusCompleted, self::statusCanceled)))
                    $this->ajaxResponse(Errors::IMPOSSIBLE);
                
                $aBill = $this->db->one_array('SELECT user_id, type, status, amount FROM '.TABLE_BILLS.' WHERE id='.$nBillID.' LIMIT 1');
                if(!$aBill) $this->ajaxResponse(Errors::IMPOSSIBLE);  
                
                $res = $this->changeBillStatus($nBillID, $nStatus, $aBill['status']);
                if($res) {
                    // обновляем баланс пользователя
                    // в случае закрытия счета типа: "пополнение счета"
                    if($aBill['type'] == self::typeInPay && $nStatus == self::statusCompleted) {
                        $this->updateBalance($aBill['user_id'], $aBill['amount'], '+');
                    }
                }
                 
                $this->ajaxResponse( array('status'=>$nStatus) );
            } break;
            /**
            * Проверка состояния счета:
            * 1) webmoney - X18 интерфейс
            */
            case 'check': 
            {
                if(!$this->haveAccessTo('edit')) {
                      $this->ajaxResponse(Errors::ACCESSDENIED);
                }
                
                if(!$nBillID) $this->ajaxResponse(Errors::IMPOSSIBLE);
                
                $aBill = $this->getBill($nBillID);
                if(!$aBill) $this->ajaxResponse(Errors::IMPOSSIBLE);
                
                switch($aBill['psystem'])
                {
                    case self::psystemWMZ:
                    case self::psystemWME:
                    case self::psystemWMR:
                    case self::psystemWMU:
                    {
                        # Интерфейс запроса статуса платежа X18
                        $sResponse = '';
                        $wmid = config::get('ps_webmoney_id');
                        $lmi_payee_purse = $this->getWebmoneyPurse($aBill['psystem']); // кошелек-получатель, на который совершался платеж 
                        
                        $md5 = strtoupper(md5($wmid.$lmi_payee_purse.$nBillID.$this->getWebmoneyPurseSecret($aBill['psystem']))); 
                        # т.к. используется хеш, то 2 других метода авторизации - sign и secret_key - оставляем пустыми 
                        $request = "<merchant.request>  
                                      <wmid>$wmid</wmid>  
                                      <lmi_payee_purse>$lmi_payee_purse</lmi_payee_purse>  
                                      <lmi_payment_no>$nBillID</lmi_payment_no>  
                                      <sign></sign><md5>$md5</md5><secret_key></secret_key> 
                                    </merchant.request>";                                    
                                    
                        $ch = curl_init("https://merchant.webmoney.ru/conf/xml/XMLTransGet.asp"); 
                        curl_setopt($ch, CURLOPT_HEADER, 0); 
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                        curl_setopt($ch, CURLOPT_POST, 1); 
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $request); 
//                        curl_setopt($ch, CURLOPT_CAINFO, "/path/to/verisign.cer"); 
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
                        $result = curl_exec($ch); curl_close($ch); 
                        
                        $xmlres = simplexml_load_string($result); // смотрим результат выполнения запроса 
                        $retval = strval($xmlres->retval);                            
                        if ($retval == -8)    { $sResponse = "Платеж №<b>$nBillID</b> не проводился"; } 
                        elseif ($retval != 0) { 
                            // если результат не равен -8 и не равен 0, то возникла ошибка при обработке запроса 
                            $sResponse = "Запрос составлен некорректно ($retval)";  
                        } 
                        else { // если результат равен 0, то платеж с таким номером проведен 
                            $wmtranid = strval($xmlres->operation->attributes()->wmtransid); 
                            $date     = strval($xmlres->operation->operdate); 
                            $payer    = strval($xmlres->operation->pursefrom); 
                            $ip       = strval($xmlres->operation->IPAddress); 
                            $sResponse = "Платеж №<b>$nBillID</b> завершился успешно.<br /> 
                                   Он был произведен $date с кошелька $payer.<br /> 
                                   Плательщик использовал IP-адрес $ip.<br /> 
                                   WM-транзакции присвоен идентификатор $wmtranid."; 
                        }
                        $this->ajaxResponse($sResponse);                        
                    } break;
                    case self::psystemRobox:
                    {
                        if(!config::get('ps_robox_on',0)) {
                            $this->ajaxResponse(Errors::IMPOSSIBLE); 
                        }   

                        $robox_login = config::get('ps_robox_login');
                        $robox_pass2 = config::get('ps_robox_pass2');
                        $request = 'https://merchant.roboxchange.com/WebService/Service.asmx/OpState?MerchantLogin='.$robox_login.'&InvoiceID='.$nBillID.'&Signature='.md5($robox_login.':'.$nBillID.':'.$robox_pass2);
                        
                        $ch = curl_init($request); 
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        $result = curl_exec($ch); curl_close($ch);     

                        /**
                        
                            <?xml version="1.0" encoding="utf-8" ?> 
                            <OperationStateResponse xmlns="http://merchant.roboxchange.com/WebService/"> 
                                <Result> 
                                    <Code>integer</Code> 
                                    <Description>string</Description> 
                                </Result> 
                                <State> 
                                    <Code>integer</Code> 
                                    <RequestDate>datetime</RequestDate> 
                                    <StateDate>datetime</StateDate> 
                                </State> 
                                <Info> 
                                    <IncCurrLabel>string</IncCurrLabel> 
                                    <IncSum>decimal</IncSum> 
                                    <IncAccount>string</IncAccount> 
                                    <PaymentMethod> 
                                        <Code>string</Code> 
                                        <Description>string</Description> 
                                    </PaymentMethod> 
                                    <OutCurrLabel>string</OutCurrLabel> 
                                    <OutSum>decimal</OutSum> 
                                </Info> 
                            </OperationStateResponse>                        
                        */
                        
                        $xml = simplexml_load_string($result); // смотрим результат выполнения запрос
                        $sResponse = '';
                        if(empty($result)) {
                            $sResponse = 'Ошибка ответа сервера Robox';
                        } elseif(intval($xml->Result->Code)!=0) {
                            $sResponse = strval($xml->Result->Description);
                        } else {
                            $sResponse = '';
                            
                            // состояние счета
                            $sState = '?';
                            switch(intval($xml->State->Code))
                            {
                                case 5:  { $sState = 'Операция только инициализирована, деньги от покупателя не получены'; } break;
                                case 10: { $sState = 'Операция отменена, деньги от покупателя не были получены'; } break;
                                case 50: { $sState = 'Деньги от покупателя получены, производится зачисление денег на счет магазина'; } break;
                                case 60: { $sState = 'Деньги после получения были возвращены покупателю'; } break;
                                case 80: { $sState = 'Исполнение операции приостановлено'; } break;
                                case 100:{ $sState = 'Операция выполнена, завершена успешно'; } break;
                            }
                            $sResponse = 'Состояние: '.$sState.' ('.date('d.m.Y H:i:s', strtotime( strval($xml->State->StateDate) )).')<br/>';
                            
                            //информация об операции
                            $sResponse .= ' Способ оплаты: <b>'.strval($xml->Info->PaymentMethod->Description).'</b>, <br/> 
                                            Сумма уплаченная клиентом: <b>'.strval($xml->Info->IncSum).' '.strval($xml->Info->IncCurrLabel).'</b>, <br/>
                                            Аккаунт клиента в системе оплаты: <b>'.strval($xml->Info->IncAccount).'</b>, <br/>
                                            Сумма отправленная '.SITEHOST.': <b>'.strval($xml->Info->OutSum).' '.strval($xml->Info->OutCurrLabel).'</b>';
                                          
                        }
                        
                        $this->ajaxResponse($sResponse);                        
                    } break;
                }
            } break;
            case 'extra':
            {
                if(!$nBillID) $this->ajaxResponse(Errors::IMPOSSIBLE);
                
                $aResponse = array('extra'=>$this->db->one_data('SELECT details FROM '.TABLE_BILLS.' WHERE id='.$nBillID.' LIMIT 1'));
                $this->ajaxResponse($aResponse);
            } break;
        }            

        $this->ajaxResponse(Errors::IMPOSSIBLE);
    }
                             
}
