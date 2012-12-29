<?php

require PATH_BASE.'config/db.config.php';
require PATH_CORE.'bff.base.php';  

//bff defines
define('BFF_DEBUG', 0);
define('BFF_COOKIE_PREFIX', 'bff_');  
define('BFF_GENERATE_META_AUTOMATICALY', 0);
define('BFF_EMAIL_SUPPORT', 'support@example.com');
define('BFF_EMAIL_NOREPLY', 'noreply@example.com');
define('BFF_EMAIL_FROM_NAME', 'Example.com'); 

//themes           
define('THEMES_DEFAULTTHEME_ID',  1);
define('THEMES_DEFAULTTHEME_KEY', 'default');

define('GEO_YMAPS_COUNTRY_TITLE', 'Украина');
define('GEO_YMAPS_COUNTRY_COORDS', '31.483768,49.315606');
define('GEO_YMAPS_COUNTRY_BOUNDS', '22.082584,44.369848;40.225768,52.379644');
define('GEO_YMAPS_CITY', 204); //город по-умолчанию (Запорожье)
define('GEO_YMAPS_CITY_COORDS', '35.095867,47.861561'); //координаты города по-умолчанию

/**
 * Ограничение по работе с городами:
 * 0(0) = все города, 1(0) = основные города, 2(id) = города района, 3(id) = один город
 * @see sites::geoCityOptions     
 */
define('GEO_CITY_BOX_TYPE', 2); 
define('GEO_CITY_BOX_ID', 1006);

//contacts types
define('CONTACTS_TYPE_CONTACT',     0); // контакт

class bff extends bffBase
{
    function initApp($isFrontend)
    {
        self::includeJS(array('jquery.min', 'bff.base'), false, true);
        if($isFrontend) {
            self::includeJS('jquery.corner');
            self::includeJS('app');
        } else {
            self::includeJS('fancybox', false, true);    
            self::includeJS('bff.adm');
            self::includeJS('app.adm', false, false);
        }
    }
    
    static function getPublicatePeriods($from = false) 
    {
        if($from === false) {
            $from = time();
        }
        
        $week = (60*60 * 24 * 7);
        
        $periods = array();
        $periods[6] = date('d.m.Y', $from + (60*60 * 24 * 3));  //3 дня    
        $periods[1] = date('d.m.Y', $from + $week);     //1 неделя
        $periods[2] = date('d.m.Y', $from + ($week*2)); //2 неделя
        $periods[3] = date('d.m.Y', $from + ($week*3)); //3 неделя
        $periods[4] = date('d.m.Y', mktime(0,0,0,date('m', $from)+1,date('d', $from),date('Y', $from))); //1 месяц  
        $periods[5] = date('d.m.Y', mktime(0,0,0,date('m', $from)+2,date('d', $from),date('Y', $from))); //2 месяца
        
        $html = '<option value="6">на 3 дня</option>
                 <option value="1" selected="selected">на 1 неделю</option>
                 <option value="2">на 2 недели</option>
                 <option value="3">на 3 недели</option>
                 <option value="4">на 1 месяц</option>
                 <option value="5">на 2 месяца</option>';
        
        return array('data'=>$periods, 'html'=>$html);
    }
}