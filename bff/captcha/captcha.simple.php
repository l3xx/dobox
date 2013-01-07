<?php

$img_x          = 100;      //Ширина изображения, по умолчанию-100
$img_y          = 28;       //Высота изображения, по умолчанию-28
$num_n          = mt_rand(4,5); //Число символов, по умолчанию-5
$font_min_size  = 12;       //Минимальный размер шрифта, по умолчанию-12
$lines_n_max    = mt_rand(3,5); //Максимальное число шумовых линий, по умолчанию-3
$nois_percent   = 2;        //Зашумленность цветами фона и текста, в процентах, по умолчанию-2
$angle_max      = 27;       //Максимальный угол отклонения от горизонтали по часовой стрелке и против, по умолчанию-20
$backColor      = 0xFFFFFF; //цвет фона. по умолчанию - 0xFFFFFF(белый)
$foreColor1     = 0x2040A0; //цвет текста 1. по умолчанию - 0x2040A0(синий)
$foreColor2     = 0x5E5E5E; //цвет текста 2. по умолчанию - 0x2040A0(синий)
$noisColor      = 0x2040A0; //цвет зашумляющих точек и линий. по умолчанию - 0x2040A0(синий)

if(isset($_GET['bg']) && strlen($_GET['bg']) == 6) {
    $color_background_custom = hexdec($_GET['bg']);
    if(!empty($color_background_custom))
        $backColor = $color_background_custom;
}
                                
$im = @ImageCreateTrueColor($img_x, $img_y) or die("Cannot Initialize new GD image stream");
 
//создаем необходимые цвета
$text_color1 = ImageColorAllocate($im, (int)($foreColor1%0x1000000/0x10000), (int)($foreColor1%0x10000/0x100), $foreColor1%0x100);
$text_color2 = ImageColorAllocate($im, (int)($foreColor2%0x1000000/0x10000), (int)($foreColor2%0x10000/0x100), $foreColor2%0x100);
$nois_color  = ImageColorAllocate($im, (int)($noisColor%0x1000000/0x10000), (int)($noisColor%0x10000/0x100), $noisColor%0x100);
$img_color   = ImageColorAllocate($im, (int)($backColor%0x1000000/0x10000), (int)($backColor%0x10000/0x100), $backColor%0x100);
            
            
//заливаем изображение фоновым цветом
ImageFill($im, 0, 0, $img_color);
//В переменной $number будет храниться число, показанное на изображении
$number='';
 
$font_cur = './bff/fonts/duality.ttf';  
                      
for ($n=0; $n<$num_n; $n++){
    $num    = rand(0,9);
    $number.= $num;
    $font_size = rand($font_min_size, $img_y/2);
    $angle     = rand(360-$angle_max,360+$angle_max);

    //вычисление координат для каждой цифры, формулы обеспечивают нормальное расположние
    //при любых значениях размеров цифры и изображения
    $y = rand(($img_y-$font_size)/4+$font_size, ($img_y-$font_size)/2+$font_size);
    $x = rand(($img_x/$num_n-$font_size)/2, $img_x/$num_n-$font_size)+$n*$img_x/$num_n;

    imagettftext($im, $font_size, $angle, $x, $y, (rand(0,1) ? $text_color1 : $text_color2), $font_cur, $num);
}
//Вычисляем число "зашумленных" пикселов
$nois_n_pix = round($img_x*$img_y*$nois_percent/100);
//зашумляем изображение пикселами цвета текста
for ($n=0; $n<$nois_n_pix; $n++){
    $x=rand(0, $img_x);
    $y=rand(0, $img_y);
    ImageSetPixel($im, $x, $y, $nois_color);
}
//зашумляем изображение пикселами фонового цвета
for ($n=0; $n<$nois_n_pix; $n++){
    $x=rand(0, $img_x);
    $y=rand(0, $img_y);
    ImageSetPixel($im, $x, $y, $img_color);
}

$lines_n = rand(0,$lines_n_max);
//проводим "зашумляющие" линии цвета текста
for ($n=0; $n<$lines_n; $n++){
    $x1=mt_rand(0, $img_x);
    $y1=mt_rand(0, $img_y);
    $x2=mt_rand(0, $img_x);
    $y2=mt_rand(0, $img_y);
    ImageLine($im, $x1, $y1, $x2, $y2, $nois_color);
}

//В переменной $number хранится число, показанное на изображении  
//$_SESSION['robot_number'] = $number;    
require './bff/captcha/captcha.protection.php';        
$oProtection = new CCaptchaProtection();    
setcookie('c1', $oProtection->generate_hash($number, date('j')), time() +(1*60*10), '/'); //10 минут

//deallocate colors
imagecolordeallocate($im, $text_color);
imagecolordeallocate($im, $nois_color);
imagecolordeallocate($im, $img_color);

header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Expires: " . date("r"));
header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");   
header("Pragma: no-cache"); 
header('Content-Transfer-Encoding: binary');   
header('Content-type: '.image_type_to_mime_type(IMAGETYPE_JPEG) ); 
 
ImageJPEG($im, '', 85); 
ImageDestroy($im);