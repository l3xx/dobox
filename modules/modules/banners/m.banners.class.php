<?php

class M_Banners
{
    function declareAdminMenu()
    {
        global $oMenu, $oSecurity; 

        $oMenu->assign('Баннеры', 'Список', 'banners', 'listing', true, 1, array(
            'rlink' => array('event'=>'add')
        ));
        
        $oMenu->assign('Баннеры', 'Добавление баннера', 'banners', 'add', false);
        $oMenu->assign('Баннеры', 'Редактирование баннера', 'banners', 'edit', false);
        $oMenu->assign('Баннеры', 'Статистика по баннеру', 'banners', 'statistic', false);  

        $oMenu->assign('Баннеры', 'Позиции', 'banners', 'position_listing', true, 1, (FORDEV ? array(
            'rlink' => array('event'=>'position_add')    
        ) : array() ) );
        
        if(FORDEV)$oMenu->assign('Баннеры', 'Добавление позиции', 'banners', 'position_add', false);
        $oMenu->assign('Баннеры', 'Редактирование позиции', 'banners', 'position_edit', false);
        $oMenu->assign('Баннеры', 'Удаление позиции', 'banners', 'position_confirm_delete', false);
    }
}