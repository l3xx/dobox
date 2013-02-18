<?php
  
  include './bff/captcha/captcha.math.php';
  
/*
    <img src="/captcha2.php?bg=cccccc" onclick="$(this).attr('src', '/captcha2.php?bg=cccccc&r='+Math.random(1));" />
    
    для проверки:                                        
    $oProtection = new CCaptchaProtection(); 
    if( !$oProtection->valid(func::getCOOKIE('c2'), func::GET('captcha2')) ) {
        $this->errors->set('wrong_captcha');
    } 
    
*/
