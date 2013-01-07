<?php

class M_Dev
{
    function declareAdminMenu()
    {
        global $oMenu;
        
        if(!FORDEV) return;
                
        $oMenu->assign('Development', 'Настройки системы', 'dev', 'sys', true, 2);
        $oMenu->assign('Development', 'Доступ к папкам', 'dev', 'dirs_listing', true, 3); 
        $oMenu->assign('Development', 'Создать модуль', 'dev', 'module_create', true, 4);
        $oMenu->assign('Development', 'PHPInfo', 'dev', 'phpinfo1', true, 5);  
        $oMenu->assign('Development', 'Системное слово?', 'dev', 'sysword', true, 6);         
        
        $oMenu->assign('Development', 'Права: модули/методы', 'dev', 'mm_listing', true, 7, 
                    array( 'rlink' => array('event'=>'mm_add')) );
        $oMenu->assign('Development', 'Права: добавить модуль/метод', 'dev', 'mm_add', false);
    }
}
