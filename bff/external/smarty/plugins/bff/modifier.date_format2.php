<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty date_format2 modifier plugin
 *
 * Type:     modifier<br>
 * Name:     date_format2<br>
 * Purpose:  date_format2
 * @author battazo
 * @param string
 * @param string
 * @param interger
 * @param interger
 * @param string
 * @param string
 */
function smarty_modifier_date_format2($sDatetime, $getTime = false, $bSkipYearIfCurrent = false, $glue1=' ', $glue2=' в ')
{
    if(!$sDatetime){ if(is_string($bSkipYearIfCurrent)) return $bSkipYearIfCurrent; return false; }
    $res = Func::parse_datetime($sDatetime);
    switch(intval($res['month']))
    {
        case '1': $res['month'] = 'Января'; break;
        case '2': $res['month'] = 'Февраля'; break;
        case '3': $res['month'] = 'Марта'; break;
        case '4': $res['month'] = 'Апреля'; break;
        case '5': $res['month'] = 'Мая'; break;
        case '6': $res['month'] = 'Июня'; break;
        case '7': $res['month'] = 'Июля'; break;
        case '8': $res['month'] = 'Августа'; break;
        case '9': $res['month'] = 'Сентября'; break;
        case '10': $res['month'] = 'Октября'; break;
        case '11': $res['month'] = 'Ноября'; break;
        case '12': $res['month'] = 'Декабря'; break;
        default: break;
    }
    if($getTime) {
        return intval($res['day']).' '.$res['month'].($bSkipYearIfCurrent===true && date('Y',time())==$res['year']?'':$glue1.$res['year']).(!(int)$res['hour'] && !(int)$res['min']?'':$glue2.$res['hour'].':'.$res['min']);
    }
    else 
        return  intval($res['day']).' '.$res['month'].($bSkipYearIfCurrent===true && date('Y',time())==$res['year']?'':$glue1.$res['year']);   
}   

?>