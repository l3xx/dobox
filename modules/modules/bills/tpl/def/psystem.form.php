<?php

extract($aData, EXTR_REFS);

switch($psystem)
{
    // ROBOKASSA (http://www.roboxchange.com)
    case Bills::psystemRobox:
    {
        //Подсчёт Robox CRC
        $robox_login = config::get('ps_robox_login');
        $robox_crc = md5($robox_login.':'.$amount.':'.$bill_id.':'.config::get('ps_robox_pass1').':shp_item='.$item_id.':shp_svc='.$svc_id.':shp_svcextra='.$svc_extra);
        
        /**
        * основной сервер:
        * https://merchant.roboxchange.com/Index.aspx
        * 
        * тестовый сервер:
        * http://test.robokassa.ru/Index.aspx
        */
        
        echo '<form action="https://merchant.roboxchange.com/Index.aspx" method="POST">
                <input type="hidden" name="MrchLogin" value="'.$robox_login.'" />
                <input type="hidden" name="OutSum" value="'.$amount.'" />
                <input type="hidden" name="InvId" value="'.$bill_id.'" />
                <input type="hidden" name="Desc" value="Оплата счета #'.$bill_id.'" />
                <input type="hidden" name="SignatureValue" value="'.$robox_crc.'" /> 
                <input type="hidden" name="IncCurrLabel" value="QiwiR" />
                <input type="hidden" name="Culture" value="Ru" />
                <input type="hidden" name="shp_item" value="'.$item_id.'" />
                <input type="hidden" name="shp_svc" value="'.$svc_id.'" />    
                <input type="hidden" name="shp_svcextra" value="'.$svc_extra.'" />            
            </form>';
    } break;
}
