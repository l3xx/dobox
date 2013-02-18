<?php

if($_SERVER['argc'] > 0) 
{                                 
    for ($i=1;$i < $_SERVER['argc'];$i++) {
        parse_str($_SERVER['argv'][$i],$tmp);
        $_GET = array_merge($_GET, $tmp);
    }      
                           
    $_GET['c'] = 2235;
    require(dirname(__FILE__).'/'.$_GET['act'].'.php');
}       

exit(0);    