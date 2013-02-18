<?php

class M_Bbs
{
    function declareAdminMenu()
    {
        global $oMenu, $oSecurity;
        
        $sClass = 'bbs';
                                                                                                     
        $oMenu->assign('Объявления', 'Опубликованные объявления',  $sClass, 'items_listing', true, 10);
        $oMenu->assign('Объявления', 'Объявления на модерации',  $sClass, 'items_modlisting', true, 16);
                    //, (FORDEV ? array( 'rlink' => array('event'=>'items_add') ) : array()) );
        $oMenu->assign('Объявления', 'Публикация в прессе',  $sClass, 'items_presslisting', true, 17);
        $oMenu->assign('Объявления', 'Добавить объявление',  $sClass, 'items_add', false, 11);
        $oMenu->assign('Объявления', 'Редактирование объявления',  $sClass, 'items_edit', false, 12);
        $oMenu->assign('Объявления', 'Комментарии к объявлению',  $sClass, 'items_comments', false, 13);
        $oMenu->assign('Объявления', 'Комментарии', $sClass, 'items_comments_all', true, 14);
        
        if($oSecurity->haveAccessToModuleToMethod('bbs','items-complaints-edit')) {
            $claimsCounter = config::get('bbs_items_claims', 0);
            $claimsCounter = ($claimsCounter>0 ? '<b class=\'clr-error\' id=\'bbs-items-claims-counter\'>(<span>'.$claimsCounter.'</span>)</b>' : false);
        } else {
            $claimsCounter = false;
        }
        $oMenu->assign('Объявления', 'Жалобы', $sClass, 'items_complaints', true, 15, array( 'counter' => $claimsCounter ));
        
        $oMenu->assign('Объявления', 'Категории',  $sClass, 'categories_listing', true, 1, 
                    array( 'rlink' => array('event'=>'categories_add') ));
        $oMenu->assign('Объявления', 'Добавить категорию',  $sClass, 'categories_add', false, 2);
        $oMenu->assign('Объявления', 'Редактирование категории',  $sClass, 'categories_edit', false, 3);
        $oMenu->assign('Объявления', 'Типы категорий', $sClass, 'types', false, 4); 
        
        $oMenu->assign('Объявления', 'Настройки', $sClass, 'settings', true, 5);
        $oMenu->assign('Объявления', 'Услуги', 'services', 'settings', true, 6);
        
        $oMenu->assign('Объявления', 'Дин. св-ва категории', $sClass, 'dynprops_listing', false, 20);
        $oMenu->assign('Объявления', 'Дин. св-ва категории', $sClass, 'dynprops_action', false, 21);
        
        if($oSecurity->haveAccessToModuleToMethod('bbs','regions')) {
            $oMenu->assign('Регионы', 'Cтраны',  $sClass, 'regions_country', true, 5);              
            $oMenu->assign('Регионы', 'Регионы',  $sClass, 'regions_region', true, 6);
            $oMenu->assign('Регионы', 'Города/станции', $sClass, 'regions_city', false, 7);
        }
        
        if(FORDEV) {                                                                                                        
            $oMenu->assign('Объявления', 'Dev: валидация treeNS',  $sClass, 'treevalidate', true, 10);    
        }
    }

}
