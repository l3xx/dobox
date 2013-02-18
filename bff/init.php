<?php

//инициализируем класс БД
require PATH_CORE.'database/database.php';
$oDb = new CDatabase('mysql', 'mysql:host='.DB_HOST_SYSGEN.';dbname='.DB_NAME_SYSGEN, DB_USER_SYSGEN, DB_PASS_SYSGEN);
//$oDb = new CDatabase('pgsql', 'pgsql:port=5432 dbname='.DB_NAME_SYSGEN, DB_USER_SYSGEN, DB_PASS_SYSGEN);
$oDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$oDb->connect();

//инициализируем Config
config::load(); 
$oConfig = & config::$data;

if(BFF_DEBUG || FORDEV) {
    include (PATH_CORE.'vardump.php');
    $oDb->bShowStatistic = true;
    $oDb->bMakeStatistic = true;   
}

//инициализируем Smarty
require PATH_CORE.'external/smarty/smarty.class.php'; 
$oSm = new Smarty();                             
$oSm->force_compile = false;
$oSm->compile_check = true;
$oSm->debugging     = false;
$oSm->compile_dir   = PATH_BASE.'tpl_c';
$oSm->config_dir    = PATH_BASE.'config';
$oSm->plugins_dir   = array('plugins','plugins/bff');
       
Module::adminCustomCenterArea(false); 

$oSm->assign('fordev', FORDEV);
$oSm->assign('site_url', SITEURL);
$oSm->assign_by_ref('config', $oConfig);        
