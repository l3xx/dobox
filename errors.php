<?php

require_once 'general.config.php';
require_once 'bff/errors.php';

if(!isset($errno))
    $errno = (integer)$_GET['errno'];
                  
$err = array();
$oErrors = new Errors( $err );
$oErrors->showHttpError( $errno );
