<?php

class Services extends ServicesBase
{
    
    private function showActivationNomoney( $nUserBalance )
    {
        return $this->showForbidden('Активация услуги', 
            'Недостаточно средств.<br /> В данный момент на счету '.$nUserBalance.' $.<br /><a href="/bill">Пополнить счет</a>');
    }
    
    function ajax()
    {
        $nUserID = $this->security->getUserID();
        
        if(!bff::$isAjax) $this->errors->set(Errors::IMPOSSIBLE);

        $aResponse = array();
        switch(func::GET('act'))
        {
            case 'init': 
            {
                $aData = &$aResponse;
                $this->input->postm(array(
                    'type' => TYPE_UINT
                ), $aData);
                
                $aServices = $this->db->select('SELECT id, keyword, settings, description FROM '.TABLE_SERVICES.' ORDER BY id');
                $aServicesData = array();
                foreach($aServices as $v) {
                    $sett = unserialize($v['settings']);
                    $sett['desc'] = $v['description'];
                    $aServicesData[$v['keyword']] = $sett;
                }

                $aData['popup'] = $this->tplFetchPHP($aServicesData, 'items.svc.popup.php');
            } break;
            case 'activate':
            {
                $this->input->postm(array(
                    'item' => TYPE_UINT,
                    'svc'  => TYPE_UINT,
                ), $aResponse);
                    
                $nItemID = $aResponse['item'];
                $nSvcID = $aResponse['svc'];
                
                if(!$nItemID || !$nSvcID) {
                    $this->errors->set(Errors::IMPOSSIBLE); break;
                }
                
                if($nUserID>0) 
                {
                    $aUserData = $this->db->one_array('SELECT blocked, blocked_reason, balance, login as email FROM '.TABLE_USERS.' WHERE user_id = '.$nUserID);
                    if($aUserData['blocked']) {
                        $this->errors->set('Ваш аккаунт заблокирован по причине:<br/>'.$aUserData['blocked_reason']); break;
                    }
                    $balance = &$aUserData['balance'];
                    if($balance<=0) $balance = 0;
                } else {
                    $balance = 0;
                }
                        
                /** @var Bills module */
                $oBills = bff::i()->GetModule('Bills');
                                    
                $svc = $this->getServiceSettings( $nSvcID );
                if(empty($svc) || !$svc['price']) { 
                    $this->errors->set(Errors::IMPOSSIBLE); break;
                }
                
                $price = $svc['price'];
                $sDescription = $this->buildServiceBillDescription($nSvcID, $nItemID);
                
                // денег на счету не хватило(или неавторизованный пользователь), выставляем счет, формируем форму оплаты
                if(!$nUserID || $price > $balance) 
                {
                    $fAmount = round($price - $balance);
                    $nPaymentSystem = Bills::psystemRobox;
                                            
                    $nBillID = $oBills->createBill_InPay($nUserID, $balance, $fAmount, $fAmount, 'rur', $nPaymentSystem, 
                                                      Bills::typeInPay, Bills::statusWaiting, 'Пополнение счета', $nItemID, $nSvcID);                        
                    
                    $aResponse['pay']  = true;
                    $aResponse['form'] = $oBills->buildPayForm( $fAmount, $nPaymentSystem, $nBillID, $nItemID, $nSvcID);
                    break;
                }
                
                // создаем счет
                $nBillID = $oBills->createBill_OutService( $nItemID, $nSvcID, $nUserID, 0, $price, Bills::statusProcessing, $sDescription);
                if(!$nBillID) { 
                    $this->errors->set(Errors::IMPOSSIBLE);
                    break;
                }
                
                // активируем услугу                
                $res = $this->activateItemSvc($nItemID, $nSvcID, 0);
                if(!$res) break; // ^ ошибки выставляются тут 
                
                // списываем с баланса пользователя
                $res = $oBills->updateBalance($nUserID, $price, '-');
                if($res) { 
                    $balance -= $price;
                    $this->security->setBalance( $balance );
                }

                $aResponse['balance'] = $balance;
                
                //актуaлизируем информацию о счете
                $oBills->updateBill($nBillID, $balance, false, Bills::statusCompleted);                        

                if($nSvcID == self::typePress) {
                    // уведомляем о скором размещении в прессе
                    bff::sendMailTemplate(array('item_url'=>SITEURL.'/item/'.$nItemID, 'email'=>$aUserData['email']), 
                        'member_bbs_press_payed', $aUserData['email']);
                }                    

            } break;
            default: $this->errors->set(Errors::IMPOSSIBLE);
        }

        $aResponse['res'] = $this->errors->no();
        $this->ajaxResponse( $aResponse );
    }
    
}
