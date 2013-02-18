<?php

#настройки
$width      = 100;
$height     = 30;
$position_x = 13;
$position_y = 22;
$font       = './bff/fonts/duality.ttf'; //шрифт 
$font_size        = 16; //Размер шрифта
$font_size_spaces = 2;  //Размер шрифта для пробелов
$angle_max        = 10;  //Максимальный угол отклонения от горизонтали по часовой стрелке и против 
$color_digits     = 0x2E9CFE; //синий
$color_question   = 0xF9051E; //красный
$color_background = 0xFFFFFF; //белый

if(isset($_GET['bg']) && strlen($_GET['bg']) == 6) {        
    $color_background_custom = hexdec($_GET['bg']);
    if(!empty($color_background_custom))
        $color_background = $color_background_custom;
}

#generate number
require './bff/captcha/captcha.protection.php';        
$oProtection = new CCaptchaProtection();
//setcookie('c2', $oProtection->generateMath(false), time() +(1*60*10), '/'); //10 минут
require './bff/func.php'; 
require './bff/security.php';
new Security('u');
$_SESSION['c2'] = $oProtection->generateMath(false);
$text = $oProtection->gendata['text'];
session_write_close();

/* для проверки 
    $oProtection = new CaptchaProtection(); 
    //$oProtection->valid($_SESSION['captcha-math'], $value);
    $oProtection->valid(func::getCOOKIE('c2'), $value); 
*/
                                                                     

#drawing
$im = @ImageCreateTrueColor($width, $height);
$im_clr_background = @ImageColorAllocate($im, (integer)($color_background%0x1000000/0x10000), (integer)($color_background%0x10000/0x100), $color_background%0x100);
$im_clr_digits     = @ImageColorAllocate($im, (integer)($color_digits%0x1000000/0x10000), (integer)($color_digits%0x10000/0x100), $color_digits%0x100);
$im_clr_question   = @ImageColorAllocate($im, (integer)($color_question%0x1000000/0x10000), (integer)($color_question%0x10000/0x100), $color_question%0x100);
ImageFill($im, 0, 0, $im_clr_background);

#draw digits [5 + 3 =]
for($i = 0, $count = mb_strlen($text); $i < $count; $i++) {
    $angle = ($angle_max?rand(360-$angle_max,360+$angle_max):0);
    if($text{$i}!=' ')
        imagettftext($im, $font_size, $angle, $position_x, $position_y, $im_clr_digits, $font, $text{$i});
    $position_x  += ($text{$i}!=' '?$font_size + 1:$font_size_spaces);
}
#draw question sign [?] 
imagettftext($im, $font_size, 0, $position_x, $position_y, $color_question, $font, '?');

#deallocate colors
ImageColorDeallocate($im, $im_clr_background);
ImageColorDeallocate($im, $im_clr_digits);
ImageColorDeallocate($im, $im_clr_question);
    
#show image
header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Expires: " . date("r"));
header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");   
header("Pragma: no-cache"); 
header('Content-Transfer-Encoding: binary');   
header('Content-type: '.image_type_to_mime_type(IMAGETYPE_PNG) );        

ImagePNG($im);    
ImageDestroy($im);
exit(0);
