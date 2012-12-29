<?php

class M_Services
{
    function declareAdminMenu()
    {
        global $oMenu, $oSecurity;

        if(!$oSecurity->haveAccessToModuleToMethod('services', 'settings'))
            return;
                    
        if(FORDEV) {
            $oMenu->assign('Услуги', 'Управление', 'services', 'settings', true, 1);
            $oMenu->assign('Услуги', 'Dev: добавить услугу', 'services', 'create', true, 2);
        }
    }
    
}
