<?php
include('inc/cool-php-captcha/captcha.php');
$captcha = new SimpleCaptcha();
$captcha->imageFormat = 'png';
$captcha->blur = true;
$captcha->session_var = 'captcha';
$captcha->resourcesPath = 'inc/cool-php-captcha/resources';
$captcha->CreateImage();
