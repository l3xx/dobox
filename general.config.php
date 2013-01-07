<?php

//path defines
$sMAINDIR = dirname(__FILE__);
define('PATH_BASE', $sMAINDIR.DIRECTORY_SEPARATOR);                                    
define('PATH_CORE', $sMAINDIR.DIRECTORY_SEPARATOR.'bff'.DIRECTORY_SEPARATOR);
define('PATH_MODULES', PATH_BASE.'modules'.DIRECTORY_SEPARATOR);

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 1);     

ini_set('upload_tmp_dir', PATH_BASE.'temp');
header('Content-type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Minsk'); //+2

define('BFF_LOCALHOST', 0); 
define('SITEHOST',  'dobox.local');
define('DB_HOST_SYSGEN', 'localhost');
define('DB_NAME_SYSGEN', 'dobox');
define('DB_USER_SYSGEN', 'dobox');
define('DB_PASS_SYSGEN', 'dobox');
define('DB_PREFIX', 'bff_'); 

define('BBS_EDITPASS_ENCODE_KEY', '__[asdDs75fsSCFc]&^689[765*&^%$Fh85dd&gFGDfg(^FR__');

define('SITEURL',   'http://'.SITEHOST);   
                                           
require PATH_BASE.'bff.php';
  