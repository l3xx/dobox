<?php
              
abstract class ServicesBase extends Module
{
    var $securityKey = '727773e8de5306e77cc8bd3dafe2ef3c';
    
    const typeUp        = 1; //поднятое
    const typeMark      = 2; //выделенное (зеленое)
    const typePremium   = 3; //премиум (оранжевое)
    const typePress     = 4; //в прессу
    const typePublicate = 5; //платная публикация 
    
    function getItemsSvcFilterOptions($nSelectedID = 0, $mEmptyOpt = false, $aExcludeSvc = array())
    {   
        $aSvc = array(
                self::typeUp       => array('t'=>'Поднятие',  'c'=>'#000'), 
                self::typeMark     => array('t'=>'Выделение', 'c'=>'green'),
                self::typePremium  => array('t'=>'Премиум',   'c'=>'#000'), 
                self::typePress    => array('t'=>'В прессу',  'c'=>'#000'),
            );
            
            
        if(!empty($aExcludeSvc)) {
            $aSvc = array_diff_key($aSvc, $aExcludeSvc); 
        }
                   
        $sOptions = '';
        foreach($aSvc as $k=>$v) {
            $sOptions .= '<option value="'.$k.'" style="color:'.$v['c'].'"'.
                            ($k == $nSelectedID?' selected':'').
                            '>'.$v['t'].'</option>';
        }
        
        if($mEmptyOpt!==false) {
            $nValue = 0;
            if(is_array($mEmptyOpt)) {
                $nValue = key($mEmptyOpt);
                $mEmptyOpt = current($mEmptyOpt);
            }
            $sOptions = '<option value="'.$nValue.'" class="bold">'.$mEmptyOpt.'</option>'.$sOptions;
        }
        
        return $sOptions;
    }
    
    function activateItemSvc($nItemID, $nSvcID, $mSvcExtra = 0)
    {
        /** @var Bbs module */
        $oBbs = bff::i()->GetModule('Bbs');
        
        $sqlNow = $this->db->getNOW();
        $aItem = $this->db->one_array('SELECT status, press, publicated, publicated_to, DATEDIFF(publicated_to, '.$sqlNow.') as days_left, svc
                        FROM '.TABLE_BBS_ITEMS.'
                        WHERE id = '.$nItemID.'
                    ');
        if(empty($aItem)) {
            return false;
        }
        if($nSvcID == self::typePublicate)
        {
            if($aItem['status'] != BBS_STATUS_NEW) {
                func::log('Объявление #'.$nItemID.' уже опубликовано, невозможно выполнить платную публикацию');
                $this->errors->set('Объявление уже опубликовано');    
                return false;
            }
        } else if($aItem['status'] != BBS_STATUS_PUBLICATED) {
            func::log('Объявление #'.$nItemID.' должно быть опубликовано');
            $this->errors->set('Объявление должно быть опубликовано');
            return false;
        }
        
        // у объявления уже активирована данная услуга
        if($aItem['svc'] == $nSvcID) 
        {
            switch($nSvcID) {
                case self::typeMark:    $this->errors->set( 'Объявление уже выделено' ); return false;
                case self::typePremium: $this->errors->set( 'Объявление уже находится в статусе <b>Премиум</b>' ); return false;
            }
        }

        $sql = '';       
        switch($nSvcID)
        {
            case self::typePublicate: // платное размещение
            { 
                $nPeriod = intval($mSvcExtra);
                if(!($nPeriod >= 1 && $nPeriod <= 4)) {
                    $nPeriod = 1;
                }
                
                $toStr = $oBbs->preparePublicatePeriodTo( $nPeriod, time() );
                   
                $sql = 'publicated = '.$sqlNow.',
                        publicated_to = '.$this->db->str2sql($toStr).',
                        publicated_order = '.$sqlNow.',
                        status_prev = status,
                        status = '.BBS_STATUS_PUBLICATED.',
                        moderated = 0';
            } break;            
            case self::typeUp: // поднять
            { 
                if($aItem['svc']==self::typePremium) {
                    $this->errors->set('Объявление не может быть поднято, поскольку находится в статусе <b>Премиум</b>');
                    return false;
                }
                if($aItem['svc']!=self::typeMark) {
                    $sql .= ' svc = '.$nSvcID.', ';
                }
                $sql .= ' publicated_order = '.$sqlNow;
            } break;
            case self::typeMark: // выделить
            {
                if($aItem['svc']==self::typePremium) {
                    $this->errors->set('Объявление не может выделено, поскольку находится в статусе <b>Премиум</b>');
                    return false;
                }            
                $sql .= ' svc = '.$nSvcID.', marked_to = '.$this->db->str2sql($aItem['publicated_to']);
                $sDescription = 'Выделено до '.$aItem['publicated_to'];
            } break;
            case self::typePremium: // премиум
            { 
                $sql .= ' svc = '.$nSvcID;
                if( $aItem['days_left']<7 ) 
                {
                    // в случае если дата публикация объявления завешается менее чем через 7 дней:
                    // продлеваем публикацию, на недостающее кол-во дней
                    $to = strtotime($aItem['publicated_to']);
                    $to+= (60*60*24*(7-$aItem['days_left'])); //прибавляем недостающие дни
                    $toStr = date('Y-m-d H:i:s', $to);
                    $sDescription = 'Премиум до '.$toStr;
                    $to = $this->db->str2sql( $toStr );
                    $sql .= ', publicated_to = '.$to;
                    $sql .= ', premium_to = '.$to;
                    $sql .= ', marked_to = '.$to; // помечаем дату "выделения", такой же датой как действие "премиум"
                } else {
                    // помечаем срок действия премиум на период: 1 неделя
                    // помечаем срок действия "выделения" на период до окончания публикации, 
                    //  т.к. после окончания действия "премиум" объявление переходит в статус "выделено".
                    $to = date('Y-m-d H:i:s', strtotime('+1 week'));
                    $sDescription = 'Премиум до '.$to;
                    $sql .= ', premium_to = '.$this->db->str2sql( $to ).'
                             , marked_to = '.$this->db->str2sql($aItem['publicated_to']);
                }
            } break;
            case self::typePress: // в прессу
            {
                if($aItem['press']>0) {
                    switch($aItem['press']) {
                        case BBS_PRESS_PAYED: $this->errors->set( 'Объявление будет опубликовано в ближайшее время' ); return false;
                        case BBS_PRESS_PUBLICATED: $this->errors->set( 'Объявление уже опубликовано прессе' ); return false;
                    }  
                }
                
                $sql .= ' press = '.BBS_PRESS_PAYED;
            } break;
        }

        $res = $this->db->execute('UPDATE '.TABLE_BBS_ITEMS.' SET '.$sql.' WHERE id = '.$nItemID);
        return !empty($res);
    }
    
    function buildServiceBillDescription($nSvcID, $nItemID)
    {
        $link = '<a href="'.SITEURL.'/item/'.$nItemID.'" target="_blank">'.$nItemID.'</a>';
        $sItemURL = 'Объявление № '.$link;
        switch($nSvcID)
        {
            case self::typeUp:        { return '<span class="putUp">'.$sItemURL.'</span>'; } break;
            case self::typeMark:      { return '<span class="mark">'.$sItemURL.'</span>'; } break;
            case self::typePremium:   { return '<span class="premium">'.$sItemURL.'</span>'; } break;
            case self::typePress:     { return '<span class="public">'.$sItemURL.'</span>'; } break;
            case self::typePublicate: default: { return '<span>Публикация объявления № '.$link.'</span>'; } break;
        }
    }
    
    /**
    * Получение натроек услуги
    * @param integer $nSvcID ID услуги
    * @return array
    */
    function getServiceSettings( $nSvcID )
    {
        static $cache;
        if(isset($cache[$nSvcID])) {
            return $cache[$nSvcID];
        }
        
        $aResult = false;
        
        do {
            $aService = $this->db->one_array('SELECT id, type, keyword, settings, description FROM '.TABLE_SERVICES.' 
                            WHERE id = '.$nSvcID );
            if(empty($aService)) {
                func::log('Ошибка получения информации об услуге #'.$nSvcID);
                $aResult = false; break;
            }
            
            $aSettings = ( !empty($aService['settings']) && is_string($aService['settings']) ? unserialize($aService['settings']) : array() );

            unset($aService['settings']);
            
            $aResult = array_merge($aService, $aSettings);
            if(isset($aResult['price'])) {
                $aResult['price'] = floatval($aResult['price']);
            }
        } while(false);
        
        return ($cache[$nSvcID] = $aResult);
    }    
}
