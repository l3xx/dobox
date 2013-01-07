<?php

require(dirname(dirname(__FILE__)).'/general.config.php');
bff::sessionStart('u');
require(PATH_CORE.'init.php');
$oBff = bff::i()->init(true);

error_reporting(E_ALL & ~E_DEPRICATED);
ini_set('display_errors', 1);

set_time_limit(0); 
ignore_user_abort(true);

/*
* Период выполнения: ежедневно, каждые 3 часа
*/

$log = new CFileLogger(PATH_BASE.'files/logs/', 'cron.log');
$log->log('bbs.items.status: started...');

$oBff->GetModule('bbs', false);
$oBff->GetModule('services');

$oDb->execute('START TRANSACTION');

$sqlNOW = $oDb->getNOW();
$sqlEmptyDate = $oDb->str2sql('0000-00-00 00:00:00');

//снимаем с публикации ОБ, у которых закончился срок публикации
$oDb->execute('UPDATE '.TABLE_BBS_ITEMS.' SET 
                    status_prev = status, status = '.BBS_STATUS_PUBLICATED_OUT.',
                    svc = 0, premium_to = '.$sqlEmptyDate.', marked_to = '.$sqlEmptyDate.' 
               WHERE status = '.BBS_STATUS_PUBLICATED.' AND publicated_to<='.$sqlNOW);

// снимаем статус "Премиум", и изменяем на "Выделенные" (+ изменяем порядок публикации, чтобы не упали вниз)
$oDb->execute('UPDATE '.TABLE_BBS_ITEMS.' SET 
                    svc = '.Services::typeMark.', publicated_order = '.$sqlNOW.', premium_to = '.$sqlEmptyDate.'
               WHERE svc = '.Services::typePremium.' AND premium_to<='.$sqlNOW);

// снимаем статус "Выделенных"
// выполняем после снятия "Премиум" ^, т.к. "Выделенные", получившиеся из "Премиум", также возможно необходимо снять с публикации
$oDb->execute('UPDATE '.TABLE_BBS_ITEMS.' SET 
                    svc = 0, marked_to = '.$sqlEmptyDate.' 
               WHERE svc = '.Services::typeMark.' AND marked_to<='.$sqlNOW);

               
$oDb->execute('COMMIT'); 

$err = Errors::i()->get();
if(!empty($err)) {
    $log->log( "bbs.items.status errors:\n".print_r($err, true) );
}

echo "OK\n";
exit;