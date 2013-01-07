<?php

class M_Users
{
    function declareAdminMenu()
    {
        global $oMenu, $oSecurity;
       
        if(!$oSecurity->haveAccessToModuleToMethod('users'))
            return;
       
        $oMenu->assign('Пользователи', 'Список пользователей', 'users', 'listing', true, 1, 
                    array( 'rlink' => array('event'=>'member_add') ));
        $oMenu->assign('Пользователи', 'Добавить пользователя', 'users', 'member_add', false);   
        $oMenu->assign('Пользователи', 'Редактирование пользователя', 'users', 'member_edit', false);
        
        $oMenu->assign('Пользователи', 'Список модераторов', 'users', 'admin_listing', true, 2,
                    array( 'rlink' => array('event'=>'mod_add') )); 
        $oMenu->assign('Пользователи', 'Добавить модератора', 'users', 'mod_add', false); 
        $oMenu->assign('Пользователи', 'Редактирование модератора', 'users', 'mod_edit', false);
        
        $oMenu->assign('Пользователи', 'Удаление пользователя', 'users', 'user_delete', false);
        $oMenu->assign('Пользователи', 'Ваш профиль', 'users', 'profile', true);
                                                                                                                 
        if($oSecurity->haveAccessToModuleToMethod('users','groups') && FORDEV) {   
		    $oMenu->assign('Пользователи', 'Группы', 'users', 'group_listing', true);
		    $oMenu->assign('Пользователи', 'Создание группы', 'users', 'group_add', false);
		    $oMenu->assign('Пользователи', 'Редактирование группы', 'users', 'group_edit', false);
		    $oMenu->assign('Пользователи', 'Доступ группы', 'users', 'group_permission_listing', false); 
        }
        
        // $oMenu->assign('Пользователи', 'Стоп-лист', 'ban', 'users', true, 1);   
    }
    
}