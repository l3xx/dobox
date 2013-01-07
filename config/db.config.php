<?php

    include PATH_CORE.'database/tables.php';
    
#Конфиг модуля "search"   
    define('SEARCH_ENTITY_PREFIX','');
    define('SEARCH_SPHINX_HOST','localhost');
    define('SEARCH_SPHINX_PORT','3312');
                                                                          
    define('TABLE_USERS_FAVS',                  DB_PREFIX.'users_favs');      

    define('TABLE_INTERNALMAIL',                DB_PREFIX.'internalmail'); 
    define('TABLE_INTERNALMAIL_FOLDERS',        DB_PREFIX.'internalmail_folders');  
    define('TABLE_INTERNALMAIL_FOLDERS_USERS',  DB_PREFIX.'internalmail_folders_users');
     
    define('TABLE_BILLS',                       DB_PREFIX.'bills');          

    define('TABLE_FAQ',                         DB_PREFIX.'faq');
    define('TABLE_FAQ_CATEGORIES',              DB_PREFIX.'faq_categories');
    
    define('TABLE_SERVICES',                    DB_PREFIX.'services');