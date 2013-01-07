<?php

    #config
    define('TABLE_CONFIG',                      DB_PREFIX.'config');  
    
    # users
    define('TABLE_USERS',                       DB_PREFIX.'users');
    define('TABLE_USERS_GROUPS',                DB_PREFIX.'users_groups'); 
    define('TABLE_USER_IN_GROUPS',              DB_PREFIX.'user_in_group');
    define('TABLE_USERS_GROUPS_PERMISSIONS',    DB_PREFIX.'users_groups_permissions');
    define('TABLE_USERS_BANLIST',               DB_PREFIX.'users_banlist');    
    define('TABLE_MODULE_METHODS',              DB_PREFIX.'module_methods');    
    
    #geo
    define('TABLE_CITY',                        DB_PREFIX.'geo_city');
    define('TABLE_REGION',                      DB_PREFIX.'geo_region');     
    
    # pages, sitemap, counters, contacts
    define('TABLE_PAGES',                       DB_PREFIX.'pages');
    define('TABLE_SITEMAP',                     DB_PREFIX.'sitemap');
    define('TABLE_SITEMAP_TREE',                DB_PREFIX.'sitemap_tree');
    define('TABLE_COUNTERS',                    DB_PREFIX.'counters');
    define('TABLE_CONTACTS',                    DB_PREFIX.'contacts');    