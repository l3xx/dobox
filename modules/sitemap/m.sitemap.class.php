<?php

class M_Sitemap
{
    function declareAdminMenu()
    {
        global $oMenu, $oSecurity;

        $oMenu->assign('Меню сайта', 'Управление меню',  'sitemap', 'listing', true, 1, 
                    array( 'rlink' => array('event'=>'add') ));
        $oMenu->assign('Меню сайта', 'Добавить меню',  'sitemap', 'add', false, 2);
        $oMenu->assign('Меню сайта', 'Редактирование меню',  'sitemap', 'edit', false, 3);
        
        if(FORDEV) {
            $oMenu->assign('Меню сайта', 'Dev: валидация treeNS',  'sitemap', 'treevalidate', true, 10);    
        }
    }
}
