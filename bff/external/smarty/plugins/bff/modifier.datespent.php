<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

require_once $smarty->_get_plugin_filepath('modifier','declension');
/**
 * Smarty datespent modifier plugin
 *
 * Type:     modifier<br>
 * Name:     datespent<br>
 * Purpose:  datespent
 * @author battazo
 * @param string
 * @param string
 * @param interger
 * @param interger
 * @param string
 * @param string
 */
function smarty_modifier_datespent($sDatetime, $getTime = false)
{                        
    //get datetime
    if(!$sDatetime) return false;
    $date = Func::parse_datetime($sDatetime);
   
    //get now datetime 
    static $now;
    if(!isset($now)) 
        $now = date('Y,m,d,H,i,s');
           
    $nowdt = array();
    list($nowdt['year'],$nowdt['month'],$nowdt['day'],$nowdt['hour'],$nowdt['min'],$nowdt['sec']) = explode(',', $now);

    //дата позже текущей
    if($nowdt['year'] < $date['year'])
        return '';
                      
    //since
    $since = array();
    foreach($nowdt as $k=>$v)
        $since[$k] = $nowdt[$k] - $date[$k];
        
    $tmp = array(0=>array('sec',60,'секунда;секунды;секунд'),1=>array('min',60,'минута;минуты;минут'),2=>array('hour',24,'час;часа;часов'),
                 3=>array('day',30,'день;дня;дней'),4=>array('month',12,'месяц;месяца;месяцев'),5=>array('year',0,'год;года;лет'));
//    if(!function_exists('calcsince'))
//    {
//        function calcsince($i, $minus = false)
//        {   
//            global $tmp, $date, $nowdt, $since;
//            $key = $tmp[$i][0];
//            if ($minus) {
//                --$nowdt[$key];
//                if ($nowdt[$key] == $date[$key]) {
//                    $since[$key] = $nowdt[$key] - $date[$key];
//                }
//            }
//            $minus = false;
//            if ($nowdt[$key] < $date[$key]) {
//                $since[$key] =  $tmp[$i][1] + $nowdt[$key] - $date[$key];
//                $minus = true;
//            }

//            if(++$i > 5) return;
//            calcsince($tmp, $date, $nowdt, $since, $i, $minus);    
//        }        
//    }
//    calcsince(0);  

    $minus = false;
    
    //seconds    
    if ($minus) {
        $nowdt['sec']--;
        if ($nowdt['sec'] == $date['sec']) {
            $since['sec'] = $nowdt['sec'] - $date['sec'];
        }
    }
    $minus = false;
    if ($nowdt['sec'] < $date['sec']) {
        $since['sec'] =  60 + $nowdt['sec'] - $date['sec'];
        $minus = true;
    }
    //minutes    
    if ($minus) {
        $nowdt['min']--;
        if ($nowdt['min'] == $date['min']) {
            $since['min'] = $nowdt['min'] - $date['min'];
        } else $since['min']--;
    }
    $minus = false;
    if ($nowdt['min'] < $date['min']) {
        $since['min'] =  (60 + $nowdt['min']) - $date['min'];
        $minus = true;
    }
    //hours --------------------------------------  
    if ($minus) {
        $nowdt['hour']--;
        if ($nowdt['hour'] == $date['hour']) {
            $since['hour'] = $nowdt['hour'] - $date['hour'];
        } 
    }
    $minus = false;
    if ($nowdt['hour'] < $date['hour']) {
        $since['hour'] =  (24 + $nowdt['hour']) - $date['hour'];
        $minus = true;
    }
    //days --------------------------------------- 
    if ($minus) {
        $nowdt['day']--; 
        if ($nowdt['day'] == $date['day']) {
            $since['day'] = $nowdt['day'] - $date['day'];
        } else $since['day']--;
    }
    $minus = false;
    if ($nowdt['day'] < $date['day']) {
        $since['day'] =  (30 + $nowdt['day']) - $date['day'];
        $minus = true;
    }
    //months -------------------------------------  
    if ($minus) {
        $nowdt['month']--;
        if ($nowdt['month'] == $date['month']) {
            $since['month'] = $nowdt['month'] - $date['month'];
        }
    }
    $minus = false;
    if ($nowdt['month'] < $date['month']) {
        $since['month'] =  30 + $nowdt['month'] - $date['month'];
        $minus = true;
    }
    //years   
    if ($minus) {
        $nowdt['year']--;
        if ($nowdt['year'] == $date['year']) {
            $since['year'] = $nowdt['year'] - $date['year'];
        }
    }
    $minus = false;
    if ($nowdt['year'] < $date['year']) {
        $since['year'] =  0 + $nowdt['year'] - $date['year'];
        $minus = true;
    }     
    
//    debug($since);
//    
//    $dateDiff = time() - strtotime($sDatetime, 0);   
//    $since2 = array();
//    $since2['day']  = floor($dateDiff/86400); $dateDiff = $dateDiff % 86400;
//    $since2['hour'] = floor($dateDiff/3600);  $dateDiff = $dateDiff % 3600;
//    $since2['min']  = floor($dateDiff/60);    $dateDiff = $dateDiff % 60;
//    $since2['sec']  = $dateDiff;
//        
//    debug($since2);   
    
    $sResult = '';
    do{
        //разница в год и более (5лет [5месяцев])
        if($since['year'])
        {
            $sResult .= $since['year'].' '.smarty_modifier_declension($since['year'],$tmp[5][2], false);
            if($since['month'])
                $sResult .= ' '.$since['month'].' '.smarty_modifier_declension($since['month'],$tmp[4][2], false);
            break;
        }
        //разница в месяц и больше (5месяцев [5дней])
        if($since['month'])
        {
            $sResult .= $since['month'].' '.smarty_modifier_declension($since['month'],$tmp[4][2], false);
            if($since['day'])
                $sResult .= ' '.$since['day'].' '.smarty_modifier_declension($since['day'],$tmp[3][2], false);
            break;
        }
        //разница в день и больше  (5дней [5часов] [5минут])
        if($since['day'])
        {
            $sResult .= $since['day'].' '.smarty_modifier_declension($since['day'],$tmp[3][2], false);
            if($since['hour']>0) {
                $sResult .= ' '.$since['hour'].' '.smarty_modifier_declension($since['hour'],$tmp[2][2], false);    
                
                //if($since['min'])
                //    $sResult .= ' '.$since['min'].' '.smarty_modifier_declension($since['min'],$tmp[1][2], false);
            }
            break;
        }        
        //разница в час и больше  (5часов [5минут])
        if($since['hour'])
        {
            $sResult .= $since['hour'].' '.smarty_modifier_declension($since['hour'],$tmp[2][2], false);
            if($since['min']) {
                 $sResult .= ' '.$since['min'].' '.smarty_modifier_declension($since['min'],$tmp[1][2], false);
            }
            break;
        }

        //разница в минуту и меньше (5минут 5секунд)
        if($since['min']>3) 
            $sResult = $since['min'].' '.smarty_modifier_declension($since['min'],$tmp[1][2], false);
        else
            $sResult = 'сейчас';
        
    }while(false);    
    return $sResult;
}   

?>