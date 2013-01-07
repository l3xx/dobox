<?php

class M_Pages
{
    function declareAdminMenu()
    {
        global $oMenu, $oSecurity;

        $oMenu->assign('Страницы', 'Список страниц', 'pages', 'listing', true, 1, 
                    array( 'rlink' => array('event'=>'add') ));
        $oMenu->assign('Страницы', 'Добавить cтраницу',  'pages', 'add', false, 2);
        $oMenu->assign('Страницы', 'Редактирование cтраницы', 'pages', 'edit', false, 3);
    }

}
