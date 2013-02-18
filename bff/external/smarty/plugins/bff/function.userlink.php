<?php

function smarty_function_userlink($p, &$smarty)
{
    if(empty($p['id'])) return '';
    if(!isset($p['nopopup'])) {
        return (isset($p['name']) ? $p['name']:'').(isset($p['surname']) ? ' '.$p['surname']:'').' <a class="'.(@$p['admin']?'admin':'').' userlink" 
            href="index.php?s=users&ev=user_ajax&action=user-info&rec='.$p['id'].'">['.$p['login'].']</a>';
    } else {
       return (isset($p['name']) ? $p['name']:'').(isset($p['surname']) ? ' '.$p['surname']:'').' <span class="'.(@$p['admin']?'admin':'').'">['.$p['login'].']</span>'; 
    }
}

?>
