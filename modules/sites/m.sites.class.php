<?php

class M_Sites
{       
    function declareAdminMenu()
    {
        global $oMenu, $oSecurity;
        
//        $oMenu->assign('Главная', 'Статистика', 'sites', 'main', true, 1);    
                
        #настройки сайта
        if($oSecurity->haveAccessToModuleToMethod('sites','settings')) {
            $oMenu->assign('Настройки сайта', 'Общие настройки',  'sites', 'siteconfig', true, 2);
        }
        
        #счетчики
        if($oSecurity->haveAccessToModuleToMethod('sites','counters')) {
            $oMenu->assign('Настройки сайта', 'Счетчики',  'sites', 'counters_listing', true, 5);
            $oMenu->assign('Настройки сайта', 'Редактировать счетчик',  'sites', 'counters_edit', false, 6);
        }
        
        #города
//        $oMenu->assign('Города', 'Основные города',  'sites', 'cities_listing_main', true, 10);
//        $oMenu->assign('Города', 'Все города',  'sites', 'cities_listing', true, 11, 
//                    array( 'rlink' => array('event'=>'cities_add') ));
//        $oMenu->assign('Города', 'Добавить город',  'sites', 'cities_add', false, 12);
//        $oMenu->assign('Города', 'Редактировать город',  'sites', 'cities_edit', false, 13); 
              
    }
}