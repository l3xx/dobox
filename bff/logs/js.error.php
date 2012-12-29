<?php  

require '..'.DIRECTORY_SEPARATOR.'func.php';
require '..'.DIRECTORY_SEPARATOR.'input.php';  
require 'file.logs.php';

$aParams = CInputCleaner::i()->postm( array(
        'e' => TYPE_STR, //error message
        'f' => TYPE_STR, //file
        'l' => TYPE_STR, //line
    ) );

$logger = new CFileLogger('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'logs', 'js.log');
$logger->log( join('; ', array_values($aParams)) );
echo 1; exit;