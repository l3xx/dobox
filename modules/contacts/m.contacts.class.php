<?php

class M_Contacts
{
    function declareAdminMenu()
    {
        global $oMenu, $oSecurity;

        $oMenu->assign('Связь с редактором', 'Список сообщений', 'contacts', 'listing', true);

        $newCounter = config::get('contacts_new', 0);        
        $newCounter = ($newCounter>0 ? '<b class=\'clr-error\' id=\'contacts-counter\'>(<span>'.$newCounter.'</span>)</b>' : false);
        $oMenu->setMainTabParam('Связь с редактором', 'counter', $newCounter);
    }
}
