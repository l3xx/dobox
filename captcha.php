<?php
  
  include './bff/captcha/captcha.simple.php';
  
/*
    <img src="/captcha.php?bg=cccccc" onclick="$(this).attr('src', '/captcha.php?bg=cccccc&r='+Math.random(1));" />
    
    для проверки:                                           
    $oProtection = new CCaptchaProtection();
    if( !$oProtection->valid(func::getCOOKIE('c1'), func::GET('captcha1')) ) {
        $this->errors->set('wrong_captcha');
    }

*/