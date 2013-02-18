<?php

require '../general.config.php';
bff::sessionStart('a'); 
if(FORDEV) func::tt_start($tt, $tt_mem);

require(PATH_CORE.'init.php');
$oBff = bff::i()->init(false);
        
if(bff::$class) {
    $htmlCenterArea = $oBff->callModule(bff::$class.'_'.bff::$event, array());
    Errors::i()->assign();   
}

if(!$oSecurity->haveAccessToAdminPanel()) {   
    func::JSRedirect('index.php?s=users&ev=login');  
}
                                                                                                             
# Формируем меню
require (PATH_CORE.'menu.php'); 
$oMenu = new CMenu(array('Объявления', 'Пользователи', 'Счета', 'Баннеры', 
                          'Страницы', 'FAQ', 'Работа с почтой', 'Регионы', 'Связь с редактором', 'Меню сайта', 'Настройка сайта'));
$firstUrl = $oMenu->build('declareadminmenu', true);
if(!bff::$class) {
    func::JSRedirect($firstUrl);
}

$oSm->assign('user_login', $oSecurity->getUserLogin());
$oSm->assign_by_ref('center_area', $htmlCenterArea);     

$oSm->display('template.tpl', __FILE__, __FILE__);

if(FORDEV) {
    func::tt_finish($tt, $tt_mem); 
    echo $oDb->getStatistic();
}

exit();
