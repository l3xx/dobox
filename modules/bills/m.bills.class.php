<?php

class M_Bills
{
    function declareAdminMenu()
    {      
        global $oMenu, $oSecurity;

        $oMenu->assign('Счета', 'Список счетов', 'bills', 'listing', true, 1);
    }
    
}
