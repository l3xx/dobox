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
* Период выполнения: ежедневно, 2 раза в сутки
*/

$log = new CFileLogger(PATH_BASE.'files/logs/', 'cron.log');
$log->log('bbs.items.counters: started...');

$oBff->GetModule('bbs');

$oDb->execute('START TRANSACTION');

# Выполняем пересчет счетчиков:

// Категории первого уровня
$oDb->execute('UPDATE '.TABLE_BBS_CATEGORIES.' C 
       LEFT JOIN (SELECT I.cat1_id as id, COUNT(I.id) as items
                    FROM '.TABLE_BBS_ITEMS.' I
                        LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id
                    WHERE I.status = '.BBS_STATUS_PUBLICATED.' AND (U.user_id IS NULL OR U.blocked = 0) AND I.cat1_id!=0
                    GROUP BY I.cat1_id) as X ON C.id = X.id
        SET C.items = IF(X.id IS NOT NULL, X.items, 0)
    WHERE C.numlevel = 1
');

// Категории второго уровня
$oDb->execute('UPDATE '.TABLE_BBS_CATEGORIES.' C 
       LEFT JOIN (SELECT I.cat2_id as id, COUNT(I.id) as items
                    FROM '.TABLE_BBS_ITEMS.' I
                        LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id
                    WHERE I.status = '.BBS_STATUS_PUBLICATED.' AND (U.user_id IS NULL OR U.blocked = 0) AND I.cat2_id!=0
                    GROUP BY I.cat2_id) as X ON C.id = X.id
        SET C.items = IF(X.id IS NOT NULL, X.items, 0)
    WHERE C.numlevel = 2
');

// Категории третьего уровня
$oDb->execute('UPDATE '.TABLE_BBS_CATEGORIES.' C 
       LEFT JOIN (SELECT I.cat_id as id, COUNT(I.id) as items
                    FROM '.TABLE_BBS_ITEMS.' I
                        LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id
                    WHERE I.status = '.BBS_STATUS_PUBLICATED.' AND (U.user_id IS NULL OR U.blocked = 0) 
                        AND I.cat_id NOT IN(0,I.cat1_id,I.cat2_id)        
                    GROUP BY I.cat_id) as X ON C.id = X.id
        SET C.items = IF(X.id IS NOT NULL, X.items, 0)
    WHERE C.numlevel = 3
');

// Типы категорий
$oDb->execute('UPDATE '.TABLE_BBS_CATEGORIES_TYPES.' T 
       LEFT JOIN (SELECT I.cat_type as id, COUNT(I.id) as items
                    FROM '.TABLE_BBS_ITEMS.' I
                        LEFT JOIN '.TABLE_USERS.' U ON I.user_id = U.user_id
                    WHERE I.status = '.BBS_STATUS_PUBLICATED.' AND (U.user_id IS NULL OR U.blocked = 0) 
                        AND I.cat_type !=0        
                    GROUP BY I.cat_type) as X ON T.id = X.id
        SET T.items = IF(X.id IS NOT NULL, X.items, 0)
');
               
$oDb->execute('COMMIT'); 

$err = Errors::i()->get();
if(!empty($err)) {
    $log->log( print_r($err, true) );
}

echo "OK\n";
exit;