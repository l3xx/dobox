<?php
    require('../general.config.inc.php');
    require_once PATH_2_GLOBALS.'inc/common.inc.php';
    $oDb->execute('UPDATE '.TABLE_BANNERS.' SET enabled = 0 WHERE  DATE_FORMAT(show_finish,"%d.%m.%Y %H:%M") <= DATE_FORMAT(NOW(),"%d.%m.%Y %H:%M")');
?>
