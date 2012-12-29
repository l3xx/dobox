<?php
/**
* Пример использования:
 $arrParams = array( 
                        array(
                                'filename'=>PATH_BASE.'files/thumbs/th_testfirst.png',
                                'width' => 300,
                                'height' => 200,
                                'round_corners' => true,
                                'round_corners_radius'=>40,
                                'round_corners_rate'=>10,          
                                'round_corners_color' => false,
                                'quality'=>100,
                                'watermark'=>true,
                                'watermark_padding_v'=>20,
                                'watermark_padding_h'=>20,
                                'watermark_font_size'=>17,
                                'watermark_font_color'=>0xFF0000,
                                'watermark_pos_x'=>'right',
                                'watermark_pos_y'=>'bottom',
                                'watermark_src'=>'Это будет водяной знак',
                                'autofit'=>false, 
                            ),         
                        array(
                                'filename'=>PATH_BASE.'files/thumbs/th_testsecond.png',
                                'width' => 400,
                                'height' => 200,
                                'round_corners' => true,
                                'round_corners_radius'=>100,
                                'round_corners_rate'=>10,          
                                'round_corners_color' => 0xFFAAAA,
                                'quality'=>100,
                                'crop_v'=>'top',
                                'crop_h'=>'right',
                                'watermark'=>true,
                                'watermark_padding_v'=>20,
                                'watermark_padding_h'=>20,
                                'watermark_pos_x'=>'left',
                                'watermark_pos_y'=>'top',
                                'watermark_src'=>PATH_BASE.'files/thumbs/wmimg.jpg',
                                'autofit'=>true,
                            ),          
                        );
        if(Func::isPostMethod())
        {         
            $oThumb=new CThumbnail(PATH_BASE.'files/thumbs/test.png','myfi');
            $oThumb->setIMagickPath(IMAGEMAGICK_DIR);      
            $oThumb->setSaveMethod('im');
            if(!$oThumb->save($arrParams))
            {
                debug("не удалось сохранить");
            }
        }
*  
*/ 
 class CThumbnail
 {
    public $aParams; 
    public $oImg;
    public $errors = null; 
    private $lang; 
    private $sPath='';
    private $saveMethod = 'gd';
    private $sIMagickPath = '';
    private $sFontDir = '';
    private $aWatermarkSources = array();
    private $bSaveOriginal = false;
    private static $bIsComplete = true;
      
    private $aDef = array( 'filename'=> '',
                           'autofit'=>true,
                           'width'=>100, //integer, false
                           'height'=>false, //integer, false
                           'crop_h'=>'center',
                           'crop_v'=>'center',
                           'src_x'=>0,
                           'src_y'=>0,
                           'round_corners'=>false,
                           'round_corners_color'=>false,
                           'round_corners_radius'=>5,
                           'round_corners_rate'=>5,
                           'watermark'=>false,
                           'watermark_src'=>'',
                           'watermark_pos_x'=>'right',
                           'watermark_pos_y'=>'bottom',
                           'watermark_padding_v'=>10,
                           'watermark_padding_h'=>10,
                           'watermark_font_size'=>12,
                           'watermark_font_color'=>0x000000,
                           'watermark_font'=>'arial.ttf',
                           'watermark_on_original'=>false,
                           'watermark_resizeable'=>true,
                           'quality'=>85);
    /**
    * Создание объекта
    * @param string $imgfile - путь к исходному изображению, если изображение нужно загрузить, то указывается путь, как его сохранять
    * @param bool $bSaveOrig - сохранять ли оригинал
    * @param mixed $sInputName  - имя file input'a на html форме, если изображение загружается
    * @return CThumbnail
    */
    function CThumbnail($imgfile, $bSaveOrig, $sInputName = false)
    {  
        register_shutdown_function('CThumbnail::testIsSuccess');                                        
        $this->initErrors(); 
        $this->bSaveOriginal = $bSaveOrig;
        if($sInputName!==false)                                     
        {
            if(!isset($_FILES[$sInputName]) || $_FILES[$sInputName]['error'])
            {   
                $this->errors->set('no_file');
            }
            if($this->errors->no())
            {
                if($bSaveOrig)
                {
                    $slash1  = strrpos($imgfile,'/');
                    $slash2  = strrpos($imgfile,'\\');
                    $nLenght = ($slash1>$slash2)?$slash1:$slash2;
                    $path    =  substr($imgfile,0,$nLenght);
                    
                    if(!is_dir($path))
                    {
                       $this->errors->set('wrong_path');
                    }
                    else
                    {
                        $imgfile = rtrim($imgfile, '\\/');
                        if (!move_uploaded_file($_FILES[$sInputName]['tmp_name'], $imgfile ))
                        {
                            $this->errors->set('wrong_path');   
                        }
                        else
                        {
                            @chmod($imgfile, 0777);
                        }
                    }
                }
                else
                {
                     $imgfile = $_FILES[$sInputName]['tmp_name'];
                }
            }
        }
        
        if(!$imgfile)
        {
           $this->errors->set('wrong_path');
           return false; 
        }
        else 
        {
            if($this->checkIsImage($imgfile)) 
            {
                $this->oImg['orig_filename'] = $imgfile;
                if(!file_exists($imgfile)) 
                   $this->errors->set('no_file'); 
                
                if($this->errors->no())
                {
                        $aImageSize = getimagesize($imgfile);
                        $this->oImg['src'] = $imgfile;
                        $this->oImg['format'] = $aImageSize[2];
                        $this->oImg['orig_width']  = $this->oImg['src_width'] = $aImageSize[0]; 
                        $this->oImg['orig_height'] = $this->oImg['src_height'] = $aImageSize[1];
                        $this->aDef['type']=$aImageSize[2];
                        $this->sFontDir = PATH_CORE.'fonts/';
                }
            }
            else
            {
                $this->errors->set('file_isnt_image');
            }
        }   
    }
    /*заполняет массив ошибок*/
    private function initErrors()
    {
        $this->lang = array(
            'no_init_watermark' => 'Инициализируйте водяной знак',
            'no_watermark_src' => 'Не указан источник водяного знака',
            'gd_not_loaded' => 'Библиотека GD не загружена',
            'no_file' => 'Файла не существует',
            'wrong_path' => 'Неверный путь',
            'no_language_support' => 'Шрифт не поддерживает данный язык',
            'file_isnt_image' => 'Файл не является изображением',
            'wrong_file_format' => 'Неверный формат файла',
            'no_th_size' => 'Не указаны размеры thumbnaila',
            'watermark_src_isnt_image' => 'Источник водяного знака не является изображением',
            'no_imagick_path'  => 'Не указан путь к ImageMagick',
            'no_rouncorners'   => 'Невозможно выполнить закругление углов',
            //ошибки imagemagick
            'im_prop_w_err'    => 'ImageMagick:Невозможно пропорционально уменишить изображение по ширине', 
            'im_prop_h_err'    => 'ImageMagick:Невозможно пропорционально уменишить изображение по высоте', 
            'im_croping_err'   => 'ImageMagick:Невозможно обрезать изображение до заданных размеров',
            'im_unprop_h_err'  => 'ImageMagick:Невозможно непропорционально уменишить изображение по высоте',
            'im_unprop_w_err'  => 'ImageMagick:Невозможно непропорционально уменишить изображение по ширине',
            'im_unprop_err'    => 'ImageMagick:Невозможно непропорционально уменишить изображение',
            'im_wmorig_err'    => 'ImageMagick:Невозможно нанести wmark на оригинал изображения',
            'im_wmcreate_err'  => 'ImageMagick:Невозможно создать изображение для wmark',
            'im_wmadd_err'     => 'ImageMagick:Невозможно добавить wmark на изображение',
            'im_wmresize_err'  => 'ImageMagick:Невозможно изменить размер wmark',
            'im_wmanimate_err' => 'ImageMagick:Невозможно взять первый кадр анимированного gif изображения',
        );
        
        $this->errors = &Errors::i( $this->lang );
    }
    
    /**
    * Устанавливает с помощью чего сохранять thumbnail, GD или Image Magick
    * @param $sMethod string метод сохранения gd или im 
    */
    
    public function setSaveMethod($sMethod)
    {
        if($sMethod ==  'gd' || $sMethod == 'im')
            $this->saveMethod = $sMethod;   
    }
    /**
    * заполняет массив $this->aParams переданными значениями (если не передан какой либо из параметров, он заполняется из массива $this->aDef)
    * далее происходит сохранение thumbnail-ов с помощью GD или Image Magik (в зависимости от $this->saveMethod default(gd))
    * Назначение параметров
    * @param array $aParams опции:
    * @param filename string имя thumbnail
    * @param width integer ширина thumbnail, booelan(false) - сжимать пропорционально
    * @param height int высота  thumbnail, booelan(false) - сжимать пропорционально
    * @param autofit bool если true то возможны 2 варианта использования
    * @param   1) не указана размерность одной из сторон thumbnailа - сторона для которой размер указан сожмется до указанных размеров, а вторая сторона сожмется пропорционально
    * @param   2) указаны оба размера тогда изображение пропорционально уменьшается (исходя из стороны которая ужимается меньше) а вторая обрезается до нужного размера(для указания какую часть изображения надо оставить используются параметры crop_v и crop_h)
    * @param   если false :
    * @param   1) не указанна размерность одной из сторон thumbnailа - то по этой стороне изображение сожмется не сохраняя пропорций
    * @param   2) указаны оба размера - исображение сжимается до нужных размеров не сохраняя пропорции
    * @param crop_h string позиционирование обрезки по X (left, center,right)
    * @param crop_v string позиционирование обрезки по Y (top,center,bottom) 
    * @param round_corners bool делать ли закругленные углы если true, то добавляются 3 следующих параметра:
    * @param   round_corners_color mixed цвет закругленных углов 0xFFFFFF если false - то прозрачные
    * @param   round_corners_radius int радиус закругления указывается в процентах (0-100)
    * @param   round_corners_rate int уровень сглаживания краев закругленных углов (от 0 до 20), чем больше - тем больше памяти требует скрипт
    * @param watermark bool наносить ли водяной знак если true, то добавляются 5 следующих параметров: 
    * @param   watermark_src string источник водяного знака, если это не путь к файлу, то наносится введенная там строка
    * @param   watermark_pos_x string позиционирование водяного знака по X(left, center,right)
    * @param   watermark_pos_y string позиционирование водяного знака по Y(top,center,bottom)
    * @param   watermark_padding ing Отступ водяного знака от края изображения
    * @param   watermark_on_original bool наносить ли ВЗ на оригинал изображения
    * @param   watermark_resizeable - сохранять ли пропорции водяного знака    * 
    * @param quality string качество jpg изображения
    */
    public function save($aParams)
    {
        foreach($aParams as $k=>$v)
        {
            foreach($this->aDef as $key=>$val)
            {
                 $this->aParams[$k][$key] =(isset($v[$key]) ? $v[$key]: $this->aDef[$key]);
            }
            if($this->aParams[$k]['watermark'] && $this->aParams[$k]['watermark_src']=='')
                $this->errors->set('no_watermark_src');
        }
                
        if(!$this->errors->no())
            return false;
               
        CThumbnail::$bIsComplete = false;
        
        $bReturn = false;
        if($this->saveMethod == 'gd')
        {
            $bReturn = $this->saveGD();
        }
        if($this->saveMethod == 'im')
        {
           $bReturn = $this->saveIM();
        }    
        CThumbnail::$bIsComplete = true;
        
        return $bReturn;
    }
    
    /**
    * сохранение тумбнейлов с помощью библиотеки GD
    * 
    */
    private function saveGD()
    {
        if(!$this->setImageHandlerGD())
            return false;
            
        foreach($this->aParams as $key=>$v)
        {
            foreach($v as $key=>$val)
            {
               $this->oImg[$key]=$val;
            }
            $this->oImg['src_x'] = 0;
            $this->oImg['src_y'] = 0;
            $this->oImg['orig_width']  = $this->oImg['src_width']; 
            $this->oImg['orig_height'] = $this->oImg['src_height'];
            $this->oImg['coef_height'] = $this->oImg['height']/$this->oImg['orig_height'];
            $this->oImg['coef_width'] = $this->oImg['width']/$this->oImg['orig_width']; 
            if($this->oImg['coef_height'] == 0)
            {
               $this->oImg['coef_height'] = $this->oImg['coef_width'];
            }
            else if($this->oImg['coef_width'] ==0)
            {
                $this->oImg['coef_width'] = $this->oImg['coef_height'];
            }
            $this->calcSizesGD();
            $this->saveFileGD();
        }
        
        imagedestroy($this->oImg['src']);
        //Уничтожение кэшированных watermark'ов
        if(!empty($this->aWatermarkSources))
        {
            foreach($this->aWatermarkSources as $k=>$v)
            {
                imagedestroy($v['src']);
            }
        }
        if(!$this->bSaveOriginal)
            unlink($this->oImg['orig_filename']);
        if($this->errors->no())
        {
            return true;
        }
        return false;  
    }
    
    /** Проверяем подключена ли библиотека GD 
    */
    private function setImageHandlerGD()
    {
        if (extension_loaded('gd') || extension_loaded('gd2') )
        {                      
            set_time_limit('6000');           
            
            switch($this->oImg['format'])     
            {
                case IMAGETYPE_GIF:  { $this->oImg['src'] = ImageCreateFromGIF($this->oImg['src']);  } break;  
                case IMAGETYPE_JPEG: { $this->oImg['src'] = ImageCreateFromJPEG($this->oImg['src']); } break;
                case IMAGETYPE_PNG:  { $this->oImg['src'] = ImageCreateFromPNG($this->oImg['src']);  } break;
                case IMAGETYPE_WBMP: { $this->oImg['src'] = ImageCreateFromWBMP($this->oImg['src']); } break; 
                default: {  $this->errors->set('wrong_file_format'); }
            }
            if($this->errors->no())
            {
                return true;
            }
        }
        else
        {
            $this->errors->set('gd_not_loaded');
        }
        return false;
    }
    
    /**
    * Расчет величины сторон, коэфициентов ужатия той или другой стороны, в зависимости от переданных параметров
    * если выставлен флаг autofit - сжатие происходит с учетом пропорций сторон,
    *   если при этом не указана ширина или высота thumbnail-а то исходное изображение ужимается так, что величина стороны с неуказанным размером уменьшается пропорционально
    *   если указана и ширина и высота thumbnail то изображение пропорционально уменьшается, а та сторона коэф сжатия которой больше обрезается точно по заданным размерам
    * если  autofit = false то изображение уменьшается не пропорционально
    *   если при этом не указана величина одной из сторон изображения, то она не сжимается(т.е сжатие по указанной стороне)
    */
    private function calcSizesGD()
    {
        /* если пропорции будут сохранятся */
        if($this->oImg['autofit'])
        {
            if(!$this->oImg['height'])
            {
              /*не указана высота  thumbnaila пропорциональное изображение по ширине*/  
                @$this->oImg['height'] = ($this->oImg['width']/$this->oImg['orig_width'])*$this->oImg['orig_height']; 
            }
            else if(!$this->oImg['width'])
            {
              /*не указана ширина thumbnaila пропорциональное изображение по высоте*/     
              @$this->oImg['width'] = ($this->oImg['height']/$this->oImg['orig_height'])*$this->oImg['orig_width'];
            }
            else{
                /* указаны значения и высоты и ширины, обрезать изображение точно по размерам */
                if($this->oImg['height']>0 && $this->oImg['width']>0)
                {
                    /* проверяем какая сторона ужимается меньше всего*/
                    $nWidthDif = $this->oImg['width']/$this->oImg['orig_width'];
                    $nHeightDif = $this->oImg['height']/$this->oImg['orig_height'];
                    /* сжимаем пропорционально по ширине или по высоте (по меньшему сжатию) сторону, коэф сжатия которой больше обрезаем*/
                    if($nWidthDif > $nHeightDif)
                    {
                        $this->cropHeightGD($nWidthDif, $this->oImg['crop_v']);
                    }
                    else 
                    {
                        $this->cropWidthGD($nHeightDif,  $this->oImg['crop_h']);
                    }
                }
                else{
                    $this->errors->set('no_th_size');
                }
            }
        }
        /* ужимать не сохраняя пропорции */
        else {
            if(!$this->oImg['height'])
            {
              /*не указана высота  thumbnaila (выставляем высоту оригинального изображения)*/  
              $this->oImg['height'] = $this->oImg['orig_height'];  
            }
            else if(!$this->oImg['width'])
            {
              /*не указана ширина thumbnaila (выставляем ширину оригинального изображения)*/     
              $this->oImg['width'] = $this->oImg['orig_width'];  
            }
        }
    }
    /**
    * внутренняя функция, обрезает нужную часть изображения по ширине
    */
    private function cropWidthGD($nKoef, $align = 'center')
    {
        $nCropingPart = (($this->oImg['orig_width']*$nKoef) - $this->oImg['width'])/$nKoef;
        switch ($align)
        {
            case 'left':
                $this->oImg['src_x'] = 0;
                $this->oImg['orig_width'] = $this->oImg['orig_width'] - $nCropingPart;
                break;
            case 'right':
                $this->oImg['src_x'] = $nCropingPart;
                $this->oImg['orig_width'] = $this->oImg['orig_width'] - $nCropingPart;
                break;                                                                                       
            default://center
            $this->oImg['src_x'] = (int)$nCropingPart/2;
            $this->oImg['orig_width'] = $this->oImg['orig_width'] - (int)$nCropingPart;
        } 
    }
    
    /**
    * внутренняя функция, обрезает нужную часть изображения по высоте
    */
    private function cropHeightGD($nKoef, $valign = 'center')
    {
        $nCropingPart = (($this->oImg['orig_height']*$nKoef) - $this->oImg['height'])/$nKoef;
        switch ($valign)
        {
            case 'top':
                $this->oImg['src_y'] = 0;
                $this->oImg['orig_height'] = $this->oImg['orig_height'] - $nCropingPart;
                break;
            case 'bottom':
                $this->oImg['src_y'] = $nCropingPart;
                $this->oImg['orig_height'] = $this->oImg['orig_height'] - $nCropingPart;
                break;                                                                                       
            default:
            $this->oImg['src_y'] = (int)$nCropingPart/2;
            $this->oImg['orig_height'] = $this->oImg['orig_height'] - (int)$nCropingPart;
        }
    }
    /**
    * сохраняет thumbnail с помощью GD
    */
    private function saveFileGD()
    {
        if($this->errors->no())
        { 
            $this->oImg['dest'] = ImageCreateTrueColor($this->oImg['width'], $this->oImg['height']);
            //сохранение прозрачности у png
            if($this->oImg['type'] == IMAGETYPE_PNG ) 
            {   
               ImageAlphaBlending($this->oImg['dest'], false);
               ImageSaveAlpha($this->oImg['dest'], true);
               $transparent = imagecolorallocatealpha($this->oImg['dest'], 255, 255, 255, 127);
               imagefilledrectangle($this->oImg['dest'], 0, 0, $this->oImg['width'], $this->oImg['height'], $transparent);
            }
//            сохранение прозрачности у gif
            else if($this->oImg['type'] == IMAGETYPE_GIF)
            {
                $colorcount = imagecolorstotal($this->oImg['src']);
                imagetruecolortopalette($this->oImg['dest'],true,$colorcount);
                imagepalettecopy($this->oImg['dest'],$this->oImg['src']);
                $transparentcolor = imagecolortransparent($this->oImg['src']);
                imagefill($this->oImg['dest'],0,0,$transparentcolor);
                imagecolortransparent($this->oImg['dest'],$transparentcolor);                                                                                                                                                        
            }
            //копируем в нужных размерах
            ImageCopyResampled ($this->oImg['dest'], $this->oImg['src'], 0, 0, $this->oImg['src_x'], $this->oImg['src_y'], $this->oImg['width'], $this->oImg['height'], $this->oImg['orig_width'], $this->oImg['orig_height']);
            
            //Если нужен watermark - ставмим
            if($this->oImg['watermark'])
            { 
                $this->init_watermark($this->oImg['watermark_src'],$this->oImg['watermark_pos_x'],$this->oImg['watermark_pos_y'],$this->oImg['watermark_padding_h'],$this->oImg['watermark_padding_v'],$this->oImg['watermark_on_original']);
                $this->make_watermark($this->oImg['dest'],intval($this->oImg['width']),intval($this->oImg['height'])); 
            }
            //Если нужно закруглить угла - закругляем
            if($this->oImg['round_corners'])
            {
                $this->roundCornersGD($this->oImg['dest'],$this->oImg['round_corners_color'],$this->oImg['round_corners_radius'],$this->oImg['round_corners_rate']);
            }       
            //сохраняем картинку
            switch($this->oImg['type'])
            {
                case IMAGETYPE_GIF:  { imageGIF($this->oImg['dest'],  $this->oImg['filename']);  } break;  
                case IMAGETYPE_JPEG: { imageJPEG($this->oImg['dest'], $this->oImg['filename'], $this->oImg['quality']); } break;
                case IMAGETYPE_PNG:  { imagePNG($this->oImg['dest'],  $this->oImg['filename']);  } break;
                case IMAGETYPE_WBMP: { imageWBMP($this->oImg['dest'], $this->oImg['filename']); } break; 
            }
            $this->clearOriginalImage();
            ImageDestroy ($this->oImg['dest']);
        }
    }
    
    /**
    *  Очищает $this->oImg для повторного перезаполнения(используется и gd и Image Magick)
    */
    private function clearOriginalImage()
    {
        $this->oImg['width'] = false;
        $this->oImg['height'] = false;
        $this->oImg['filename'] = "";
        $this->oImg['autofit'] = true;
        $this->oImg['crop_h'] = 'center';
        $this->oImg['crop_v'] = 'center';
        $this->oImg['orig_width'] = 0;
        $this->oImg['orig_height'] = 0;
        $this->oImg['round_corners']= false;
        $this->oImg['watermark'] = false;
    }
    
  /**
    * Инициализация водяного знака. Если $sWatermark - не файл, то печатается введенный там текст  
    * 
    * @param mixed $sWatermark водяной знак
    * @param mixed $hPosition позиция по горизонтали
    * @param mixed $vPosition позиция по вертикали
    * @param mixed $xPadding отступ от края по oX 
    * @param mixed $yPadding отступ от края по oY 
    * @param mixed $bMakeSourceWatermark флаг, ставить ли знак на оригинале изображения
    */
    
    private function init_watermark($sWatermark, $hPosition='right', $vPosition='bottom', $xPadding = 15,$yPadding=15, $bMakeSourceWatermark = true)
    {
        if(!$this->oImg['watermark_resizeable'])
        {
              $this->oImg['coef_width']=1;
              $this->oImg['coef_height']=1;
        }
        if(file_exists($sWatermark))   
        {
            if($this->checkIsImage($sWatermark))
            {
                //если watermark с таким путем не кэширован, то создается, иначе - берется из кэша 
                if(!isset($this->aWatermarkSources[$sWatermark]))
                {
                    $size = getimagesize($sWatermark);
                    
                    $this->aWatermarkSources[$sWatermark]['width']=$size[0]; 
                    $this->aWatermarkSources[$sWatermark]['height']=$size[1]; 
                                   
                    switch($size[2])
                    {
                        case IMAGETYPE_GIF:  $this->aWatermarkSources[$sWatermark]['src'] = imageCreateFromGIF($sWatermark);  break;
                        case IMAGETYPE_JPEG: $this->aWatermarkSources[$sWatermark]['src'] = imageCreateFromJPEG($sWatermark); break;
                        case IMAGETYPE_PNG:  $this->aWatermarkSources[$sWatermark]['src'] = imageCreateFromPNG($sWatermark);  break;
                        case IMAGETYPE_WBMP: $this->aWatermarkSources[$sWatermark]['src'] = imageCreateFromWBMP($sWatermark); break;
                        default: return;
                    }
                    
                }
                // определяем нужные размеры wm исходя из коэффициентов
                if($this->oImg['watermark_resizeable'])
                {   
                    $nCoef=$this->oImg['coef_width']>$this->oImg['coef_height']?$this->oImg['coef_height']:$this->oImg['coef_width'];
                    $dst_width=intval($this->aWatermarkSources[$sWatermark]['width']*$nCoef);
                    $dst_height=intval($this->aWatermarkSources[$sWatermark]['height']*$nCoef);
                }
                else
                {
                    $dst_width=$this->aWatermarkSources[$sWatermark]['width'];
                    $dst_height=$this->aWatermarkSources[$sWatermark]['height'];
                }
                
                $this->oImg['wm']=imagecreatetruecolor($dst_width,$dst_height);
                
                ImageAlphaBlending($this->oImg['wm'], false);
                ImageSaveAlpha($this->oImg['wm'], true);
                
                imagecopyresized($this->oImg['wm'],$this->aWatermarkSources[$sWatermark]['src'],0,0,0,0,$dst_width,$dst_height,$this->aWatermarkSources[$sWatermark]['width'],$this->aWatermarkSources[$sWatermark]['height']);

                
                
                $this->oImg['wm_width'] = $dst_width;
                $this->oImg['wm_height'] = $dst_height;
                $this->oImg['watermark_pos_x'] = $hPosition; 
                $this->oImg['watermark_pos_y'] = $vPosition;
                $this->oImg['watermark_padding_h'] = intval($xPadding*$this->oImg['coef_width']); 
                $this->oImg['watermark_padding_v'] = intval($yPadding*$this->oImg['coef_height']);
            }
            else
            {
                unset($this->oImg['wm']);
                $this->errors->set('watermark_src_isnt_image');
            } 
        }
        else
        { 
            $nCoef=$this->oImg['coef_width']>$this->oImg['coef_height']?$this->oImg['coef_height']:$this->oImg['coef_width'];
            $nFontSize = round($this->oImg['watermark_font_size']*$nCoef);
            $nFontSize = round($nFontSize / 1.333);
            //определение координат нужного прямоугольника под WM
            $aImageDimmention=imagettfbbox($nFontSize,0,$this->sFontDir.$this->oImg['watermark_font'],$sWatermark);

            //ширина (разница по oX между нижними левым и правым углом прямоугольника)
            $nImgWidth = $aImageDimmention[4] - $aImageDimmention[6];
            //высота (разница по oY между левыми верхним и нижним углом прямоугольника)
            $nImgHeight = $aImageDimmention[1] - $aImageDimmention[7];
            if(!$nImgHeight)
            {
                $this->errors->set('no_language_support');
                $this->oImg['wm'] = false;
            }
            
            if($this->errors->no())
            {
                $nImgHeight*=1.6;//небольшое увеличение высоты чтобы поместились выступающие края букв

                
                $this->oImg['wm'] = ImageCreateTrueColor($nImgWidth,$nImgHeight);
                
                if($this->oImg['type']==IMAGETYPE_GIF)
                {
                    //Определяем индекс прозрачного цвета у gif
                    $colorcount = imagecolorstotal($this->oImg['src']); 
                    imagetruecolortopalette($this->oImg['wm'],true,$colorcount);
                    imagepalettecopy($this->oImg['wm'],$this->oImg['wm']);
                    $trans = imagecolortransparent($this->oImg['wm']);
                    imagefill($this->oImg['wm'],0,0,$trans); 
                    imagecolortransparent($this->oImg['wm'],$trans);
                    $textcolor=imagecolorallocate($this->oImg['wm'],(integer)($this->oImg['watermark_font_color']%0x1000000/0x10000), (integer)($this->oImg['watermark_font_color']%0x10000/0x100), $this->oImg['watermark_font_color']%0x100); 
                }
                else
                {
                    //Определяем индекс прозрачного цвета у png
                    $opacity = imagecolorallocatealpha($this->oImg['wm'],255,255,255,127);
                    imagefill($this->oImg['wm'],0,0,$opacity);
                    ImageAlphaBlending($this->oImg['wm'], false);
                    ImageSaveAlpha($this->oImg['wm'], true);
                    $textcolor = imagecolorallocatealpha($this->oImg['wm'], (integer)($this->oImg['watermark_font_color']%0x1000000/0x10000), (integer)($this->oImg['watermark_font_color']%0x10000/0x100), $this->oImg['watermark_font_color']%0x100,0);
                }
                // Наносим текст

                imagettftext($this->oImg['wm'],$nFontSize,0,0,intval($nImgHeight*0.8),$textcolor,$this->sFontDir.$this->oImg['watermark_font'],$sWatermark);
                
                $this->oImg['wm_width'] = $nImgWidth;
                $this->oImg['wm_height'] = $nImgHeight;
                $this->oImg['watermark_pos_x'] = $hPosition; 
                $this->oImg['watermark_pos_y'] = $vPosition;
                $this->oImg['watermark_padding_h'] = intval($xPadding*$this->oImg['coef_width']); 
                $this->oImg['watermark_padding_v'] = intval($yPadding*$this->oImg['coef_height']); 
            }
        } 
        // нанесение wm на оригинал        
        if($bMakeSourceWatermark)
        {
            $this->oImg['only_src'] = imageCreateTrueColor($this->oImg['orig_width'], $this->oImg['orig_height']);
            ImageCopy($this->oImg['only_src'], $this->oImg['src'], 0, 0, 0, 0, $this->oImg['orig_width'], $this->oImg['orig_height']); 
        
            $this->make_watermark($this->oImg['only_src'], intval($this->oImg['orig_width']), intval($this->oImg['orig_height']));

            switch($this->oImg['format'])
            {
                case IMAGETYPE_GIF:  { imageGIF($this->oImg['only_src'], $this->oImg['orig_filename']);  } break;  
                case IMAGETYPE_JPEG: { imageJPEG($this->oImg['only_src'],$this->$this->oImg['orig_filename'], $this->oImg['quality']); } break;
                case IMAGETYPE_PNG:  { imagePNG($this->oImg['only_src'], $this->$this->oImg['orig_filename']); } break;
                case IMAGETYPE_WBMP: { imageWBMP($this->oImg['only_src'],$this->$this->oImg['orig_filename']); } break; 
            }

            imageDestroy($this->oImg['only_src']); 
        }
    }
    
    /**
    * Нанесение водяного знака
    * 
    * @param mixed $img  дескриптор изображения
    * @param mixed $sDestWidth координаты правого нижнего угла водяного знака по X
    * @param mixed $sDestHeight координаты правого нижнего угла водяного знака по Y
    */
    private function make_watermark(&$img, $sDestWidth, $sDestHeight)
    {
        if(isset($this->oImg['wm']) && $this->oImg['wm'])
        {   
            //определяем позиционирование
            switch($this->oImg['watermark_pos_x']){
                case 'left':   $placementX = $this->oImg['watermark_padding_h']; break;
                case 'right':  $placementX = $sDestWidth - $this->oImg['wm_width'] - $this->oImg['watermark_padding_h']; break;
                default:$placementX = round( ($sDestWidth - $this->oImg['wm_width']) / 2); break;
            }

            switch($this->oImg['watermark_pos_y']){
                case 'top':    $placementY = $this->oImg['watermark_padding_v'];; break;
                case 'bottom': $placementY = $sDestHeight - $this->oImg['wm_height'] - $this->oImg['watermark_padding_v']; break;
                default:$placementY = round( ($sDestHeight - $this->oImg['wm_height']) / 2); break;
            }
            //накладывем watermark
            imagecopy($img,
                $this->oImg['wm'],
                $placementX, $placementY, 
                0, 0, $this->oImg['wm_width'], $this->oImg['wm_height']); 
        }
        else if(!isset($this->oImg['wm']))
        {
            $this->errors->set('no_init_watermark');
        }
        if($this->oImg['wm']) 
            imagedestroy($this->oImg['wm']);
    }
    /**
    * Закругленные углы
    * 
    * @param mixed $img  дескриптор изображения
    * @param mixed $cornercolor  цвет углов в 16-ной кодировке. Если false - прозрачный
    * @param mixed $radius  радиус закругления
    * @param mixed $rate  сглаживание закругления, максимум - 20
    */
    private function roundCornersGD(&$img,$cornercolor,$radius=5,$rate=5)
    {
        if($radius<=0)
        {
            return false;
        } 
        if($rate <=0)
        {
            $rate = 5;
        }
        if($radius > 100)
        {
            $radius = 100;    
        }
        if($rate > 20)
        {
            $rate = 20;
        }
                
        $width = ImagesX($img);
        $height = ImagesY($img);
        
        $radius = ($width<=$height)?round((($width/100)*$radius)/2):round((($height/100)*$radius)/2); 

        $rs_radius = $radius * $rate;
        $rs_size = $rs_radius * 2;
        

        ImageAlphablending($img, false);
        ImageSaveAlpha($img, true);

        $corner = ImageCreateTrueColor($rs_size, $rs_size);
        ImageAlphablending($corner, false);
        
        if($cornercolor===false) // указан ли прозрачный цвет.
        {
            $this->oImg['type'] = IMAGETYPE_PNG;
        }
        if($this->oImg['type'] == IMAGETYPE_PNG)
        {
            $trans = ImageColorAllocateAlpha($corner, 255, 255, 255, 127);
        }
        else
        {
            $trans = ImageColorAllocateAlpha($corner,(integer)($cornercolor%0x1000000/0x10000), (integer)($cornercolor%0x10000/0x100), $cornercolor%0x100,0);
        } 
        imagefilledrectangle($corner,0,0,$rs_size,$rs_size,$trans);
         
        $positions = array(
            array(0, 0, 0, 0),
            array($rs_radius, 0, $width - $radius, 0),
            array($rs_radius, $rs_radius, $width - $radius, $height - $radius),
            array(0, $rs_radius, 0, $height - $radius),
        );
     
        foreach ($positions as $pos) {
            ImageCopyResampled($corner, $img, $pos[0], $pos[1], $pos[2], $pos[3], $rs_radius, $rs_radius, $radius, $radius);
        }
        
        $lx = $ly = 0;
        $i = -$rs_radius;
        $y2 = -$i;
        $r_2 = $rs_radius * $rs_radius;
     
        for (; $i <= $y2; $i++) {
     
            $y = $i;
            $x = sqrt($r_2 - $y * $y);
     
            $y += $rs_radius;
            $x += $rs_radius;
     
            ImageLine($corner, $x, $y, $rs_size, $y, $trans);
            ImageLine($corner, 0, $y, $rs_size - $x, $y, $trans);
     
            $lx = $x;
            $ly = $y;
        }
        foreach ($positions as $i => $pos) {
            ImageCopyResampled($img, $corner, $pos[2], $pos[3], $pos[0], $pos[1], $radius, $radius, $rs_radius, $rs_radius);
        }     
        ImageDestroy($corner);    
    }
   
    
    /* сохранение тумбнейлов с помощью ImageMagick */
    
    /**
    * указывает путь к Image Magick
    * @param string $sPath 
    */
    public function setIMagickPath($sPath)
    {
         $this->sIMagickPath = $sPath;
    }
    
    /**
    * сохраняет thumbnail с пом Image Magick
    */
    private function saveIM()
    {
        if($this->sIMagickPath == '')
        {
            $this->errors->set('no_imagick_path');
            return false;
        }
        foreach($this->aParams as $key=>$v)
        {
            foreach($v as $key=>$val)
            {
               $this->oImg[$key]=$val;
            }
            $this->oImg['src_x'] = 0;
            $this->oImg['src_y'] = 0; 
            $this->oImg['orig_width']  = $this->oImg['src_width']; 
            $this->oImg['orig_height'] = $this->oImg['src_height'];
            $this->oImg['coef_height'] = $this->oImg['height']/$this->oImg['orig_height'];
            $this->oImg['coef_width'] = $this->oImg['width']/$this->oImg['orig_width']; 
            $this->sPath = dirname($this->oImg['filename']);
             
            /*проверяет является ли картинка анимированным gif */
            if($this->isAnimatedGif())
            {
                /*если gif анимированный и используется наложение watermark или закругление уголков то работаем с первым кадром gifa*/
               if($this->oImg['watermark'] || $this->oImg['round_corners'])
                {
                    $sNewFile=$this->sPath.'/__firstFrame.gif';
                    exec($this->sIMagickPath.'convert '.$this->oImg['orig_filename'].'[0] '.$sNewFile);
                    $this->oImg['orig_filename'] = $sNewFile;
                }
            }
            
            $this->saveFileIM();
            
            //удаляем первый кадр анимированного гифа, если наносился wm или round_corners
            if(file_exists($this->sPath.'/__firstFrame.gif'))
                unlink($this->sPath.'/__firstFrame.gif');
        }
        if(!$this->bSaveOriginal)
            unlink($this->oImg['src']);
        if($this->errors->no())
        {
            return true;
        }
        return false;
    }
    
    /**
    * внутренняя функция, обрезает нужную часть изображения по высоте
    */
    private function cropWidthIM($nKoef, $align = 'center')
    {
        switch ($align)
        {
            case 'left':
                $this->oImg['gravity'] = 'West';
                break;
            case 'right':
                $this->oImg['gravity'] = 'East';   
                break;                                                                                       
            default:
            $this->oImg['gravity'] = 'Center';   
        } 
    }
    
    /**
    * внутренняя функция, обрезает нужную часть изображения по ширине
    */
    private function cropHeightIM($nKoef, $valign = 'center')
    {
        switch ($valign)
        {
            case 'top':
                $this->oImg['gravity'] = 'North';  
                break;
            case 'bottom':
                $this->oImg['gravity'] = 'South';  
                break;                                                                                       
            default:
                $this->oImg['gravity'] = 'Center';  
        }
    }
    
   
    private function saveFileIM()
    {     
       /* если пропорции будут сохранятся */
        if($this->oImg['autofit'])
        {
            if(!$this->oImg['height'] && $this->oImg['width']>0)
            {
              /*не указана высота  thumbnaila пропорциональное изображение по ширине*/  
              if(exec($this->sIMagickPath.'convert '.$this->oImg['orig_filename'].' -resize '.$this->oImg['width'].'x -quality '.$this->oImg['quality'].' '.$this->oImg['filename']))
                $this->errors->set('im_prop_w_err');
            }
            else if(!$this->oImg['width'] && $this->oImg['height']>0)
            {
              /*не указана ширина thumbnaila пропорциональное изображение по высоте*/     
               if(exec($this->sIMagickPath.'convert '.$this->oImg['orig_filename'].' -resize x'.$this->oImg['height'].' -quality '.$this->oImg['quality'].' '.$this->oImg['filename']))
                $this->errors->set('im_prop_h_err'); 
            }                      
            else{
                /* указаны значения и высоты и ширины, обрезать изображение точно по размерам */
                if($this->oImg['height']>0 && $this->oImg['width']>0)
                {
                    /* проверяем какая сторона ужимается меньше всего*/
                    $nWidthDif = $this->oImg['width']/$this->oImg['orig_width'];
                    $nHeightDif = $this->oImg['height']/$this->oImg['orig_height'];
                    /* сжимаем пропорционально по ширине или по высоте (по меньшему сжатию) сторону, коэф сжатия которой больше обрезаем*/
                    if($nWidthDif > $nHeightDif)
                    {
                        $this->cropHeightIM($nWidthDif, $this->oImg['crop_v']);
                        if(exec($this->sIMagickPath.'convert '.$this->oImg['orig_filename'].' -resize '.$this->oImg['width'].'x -quality '.$this->oImg['quality'].' '.$this->oImg['filename']))
                           $this->errors->set('im_prop_h_err'); 
                    }
                    else                        
                    {
                        $this->cropWidthIM($nHeightDif,  $this->oImg['crop_h']);
                    
                        if(exec($this->sIMagickPath.'convert '.$this->oImg['orig_filename'].' -resize x'.$this->oImg['height'].' -quality '.$this->oImg['quality'].' '.$this->oImg['filename']))
                            $this->errors->set('im_prop_w_err'); 
                    }
                    /*обрезка изображения до точного размера*/
                    if(exec($this->sIMagickPath.'convert '.$this->oImg['filename'].' -gravity '.$this->oImg['gravity'].' -quality 100 -crop '.$this->oImg['width'].'x'.$this->oImg['height'].'+0+0 +repage '.$this->oImg['filename']))
                        $this->errors->set('im_croping_err');
                }                              
                else{
                    $this->errors->set('no_th_size');
                }
            }
        }
        /* ужимать не сохраняя пропорции */
        else {
            if(!$this->oImg['height'] && $this->oImg['width']>0)
            {
              /*не указана высота  thumbnaila (выставляем высоту оригинального изображения)*/  
              if(exec($this->sIMagickPath.'convert '.$this->oImg['orig_filename'].' -scale '.$this->oImg['width'].'x'.$this->oImg['orig_height'].'! -quality '.$this->oImg['quality'].' '.$this->oImg['filename']))
                $this->errors->set('im_unprop_h_err'); 
            }                              
            else if(!$this->oImg['width']&& $this->oImg['height']>0)
            {
              /*не указана ширина thumbnaila (выставляем ширину оригинального изображения)*/     
              if(exec($this->sIMagickPath.'convert '.$this->oImg['orig_filename'].' -scale '.$this->oImg['orig_width'].'x'.$this->oImg['height'].'! -quality '.$this->oImg['quality'].' '.$this->oImg['filename']))   
                $this->errors->set('im_unprop_w_err');
            }                               
            else{
              if(exec($this->sIMagickPath.'convert '.$this->oImg['orig_filename'].' -scale '.$this->oImg['width'].'x'.$this->oImg['height'].'! -quality '.$this->oImg['quality'].' '.$this->oImg['filename']))
                 $this->errors->set('im_unprop_err');   
            }
        }                                    
        /* 
            нанесение водяного знака
        */
        if($this->oImg['watermark'] && $this->oImg['watermark_src'])
        {
           $this->makeWatermarkIM(); 
        }
        
        /*
            закругление углов картинки
        */
        if($this->oImg['round_corners'])
        {
            $this->roundCornersIM($this->oImg['filename'],$this->oImg['round_corners_color'],$this->oImg['round_corners_radius'],$this->oImg['round_corners_rate']);
        }

        $this->clearOriginalImage();
     }
    /**
    * перевод положения картинки к формату ImageMagick 
    * 
    * @param mixed $vertical - положение по вертикали
    * @param mixed $horisontal - положение по горизонтали  
    */
    private function convertDimentionsIM($vertical,$horisontal)
    {
         if($vertical=='top' && $horisontal=='left')
         {
             $dim = 'NorthWest';
         }
         else if($vertical == 'center' && $horisontal == 'left')
         {
            $dim = 'West';    
         }
         else if($vertical == 'bottom' && $horisontal == 'left')
         {
            $dim = 'SouthWest';    
         }
         else if($vertical == 'top' && $horisontal == 'center')
         {
            $dim = 'North';    
         }
         else if($vertical == 'center' && $horisontal == 'center')
         {
            $dim = 'Center';    
         }
         else if($vertical == 'bottom' && $horisontal == 'center')
         {
            $dim = 'South';    
         }
         else if($vertical == 'top' && $horisontal == 'right')
         {
            $dim = 'NorthEast';    
         }
         else if($vertical == 'center' && $horisontal == 'right')
         {
            $dim = 'East';    
         }
         else
         {
            $dim = 'SouthEast';    
         }
         return $dim;
     }
    /**
    * нанести watermark нa изображение с помощью Image Magick
    */ 
    private function makeWatermarkIM()
    {
        $sGravity = $this->convertDimentionsIM($this->oImg['watermark_pos_y'],$this->oImg['watermark_pos_x']);

            /*если есть файл с wm */
            if(file_exists($this->oImg['watermark_src']))
            {
                /** если указано что wm надо ужимать вместе с картинкой
                * то получаем размеры загружаемого wm 
                * и уменьшаем его пропорционально с основным изображением
                */
                if($this->oImg['watermark_resizeable'])
                {
                    $sParam = exec($this->sIMagickPath.'identify '.$this->oImg['watermark_src']);
                    $aParam =split(' ', $sParam);
                    $aSize = split('x',$aParam[2]);
                    $nWMwidth=intval($aSize[0]*$this->oImg['coef_width']);
                    $nWMheight=intval($aSize[1]*$this->oImg['coef_height']);
                    if(exec($this->sIMagickPath.'convert '.$this->oImg['watermark_src'].' -scale '.$nWMwidth.'x'.$nWMheight.' -quality '.$this->oImg['quality'].' '.$this->sPath.'/def_w_mark.png'))
                        $this->errors->set('im_wmresize_err');   
                }
                /*наложение wm на thumbnail */
                if(exec($this->sIMagickPath.'composite -dissolve 100 -gravity '.$sGravity.' -geometry +'.$this->oImg['watermark_padding_h'].'+'.$this->oImg['watermark_padding_v'].' '.$this->sPath.'/def_w_mark.png '. $this->oImg['filename'].' '.$this->oImg['filename']))
                    $this->errors->set('im_wmadd_err');      
                
                /*наложение wm на оригинал  */
                if($this->oImg['watermark_on_original'])
                {
                   if(exec($this->sIMagickPath.'composite -dissolve 100 -gravity '.$sGravity.' -geometry +'.$this->oImg['watermark_padding_h'].'+'.$this->oImg['watermark_padding_v'].' '. $this->oImg['watermark_src'] .' '. $this->oImg['orig_filename'].' '.$this->oImg['orig_filename']))
                        $this->errors->set('im_wmorig_err');
                }
            }
            /*создать wm из переданного текста*/
            else
            {
               /*задает цвет шрифта wm  */
               if($this->oImg['watermark_font_color'] < 0x1)
                {
                    $addzero = '000000';
                }                       
                else if($this->oImg['watermark_font_color']<0x10)
                {
                    $addzero='00000';
                }
                else if($this->oImg['watermark_font_color']<0x100)
                {
                    $addzero='0000';
                }
                else if($this->oImg['watermark_font_color']<0x1000)
                {
                    $addzero='000';
                }
                else if($this->oImg['watermark_font_color']<0x10000)
                {
                    $addzero='00';
                }
                else if($this->oImg['watermark_font_color']<0x100000)
                    $addzero='0';
                else
                    $addzero='';

                $sTextColor='#'.$addzero.dechex($this->oImg['watermark_font_color']).''; 

                $this->oImg['watermark_src'] = addslashes($this->oImg['watermark_src']);
                $nCoef = 1;

                if($this->oImg['watermark_resizeable'])
                {
                    $nCoef=$this->oImg['coef_width']>$this->oImg['coef_height']?$this->oImg['coef_height']:$this->oImg['coef_width'];
                    
                    /* создает изображение png из текста переданного как wm (размер шрифта уменьшается пропорционально картинке) */
                    if(exec($this->sIMagickPath.'convert -background "none"  -fill "'.$sTextColor.'" -font '.$this->sFontDir.'/'.$this->oImg['watermark_font'].' -pointsize '.round($nCoef * $this->oImg['watermark_font_size']).' label:"'.$this->oImg['watermark_src'].'" '. $this->sPath.'/def_w_mark.png'))
                        $this->errors->set('im_wmcreate_err');
                }
                else{
                    /* создает изображение png из текста переданного как wm (размер шрифта не изменяется) */
                     if(exec($this->sIMagickPath.'convert -background "none"  -fill "'.$sTextColor.'" -font '.$this->sFontDir.'/'.$this->oImg['watermark_font'].' -pointsize '.($this->oImg['watermark_font_size']).' label:"'.$this->oImg['watermark_src'].'" '. $this->sPath.'/def_w_mark.png')) 
                        $this->errors->set('im_wmorig_err');   
                }
                /*накладывает изображение с wm на thumbnail */  
                if(exec($this->sIMagickPath.'composite -dissolve 100 -gravity '.$sGravity.' -geometry +'.$this->oImg['watermark_padding_h']*$nCoef.'+'.$this->oImg['watermark_padding_v']*$nCoef.' '. $this->sPath.'/def_w_mark.png' .' '.$this->oImg['filename'] .' '.$this->oImg['filename']))
                 $this->errors->set('im_wmcreate_err');   

                /*если нужно нанести wm на оригинал изображения*/
                if($this->oImg['watermark_on_original'])
                {
                   if(exec($this->sIMagickPath.'composite -dissolve 100 -gravity '.$sGravity.' -geometry +'.$this->oImg['watermark_padding_h'].'+'.$this->oImg['watermark_padding_v'].' '. $this->sPath.'/def_w_mark.png' .' '. $this->oImg['orig_filename'].' '.$this->oImg['orig_filename']))
                    $this->errors->set('im_wmorig_err');
                }
            }
        /*удаление временного файла с wm*/
        $this->oImg['orig_filename'] = $this->oImg['src'];
        unlink($this->sPath.'/def_w_mark.png');
    }
    
    /**
    * Проверяет является ли обрабатываемое изображение  анимированным gif                                           
    */
    public function isAnimatedGif()
    {
       if($this->oImg['format'] == IMAGETYPE_GIF)
       {
            /*с помощью identify получает строку с параметрами файла, если gif массив - он анимированный*/
            $sParam = exec($this->sIMagickPath.'identify '.$this->oImg['orig_filename']);
            preg_match('/.gif\[(\d)\]/Us', $sParam , $aFind); 
            
            /*если gif анимированный */                                                             
            if(isset($aFind[1]))
            {
               return true;               
            }
            else
            {
                return false;
            }
       }
       else{
           return false;
       }
    }
    
    /**
    * Создание закругленных уголков с помощью Image Magick
    */ 
    private function roundCornersIM($img,$cornercolor,$radius=5, $rate=5)
    {   
    
        if($this->oImg['format']==IMAGETYPE_GIF || $this->oImg['format']==IMAGETYPE_PNG)
        {
            /*если файл png или gif то углы прозрачные*/
            $cornercolor = false;
        }
        if($radius<=0)
        {
            return false;
        } 
        if($rate <=0)
        {
            $rate = 5;
        }
        
        if($radius > 100) 
        {
            $radius = 100;    
        }
        if($rate > 20)  
        {
            $rate = 20;
        }
        
        $width=$this->oImg['width'];
        $height=$this->oImg['height'];
        $radius = ($width<=$height)?((($width/100)*$radius)/2):((($height/100)*$radius)/2); 
        
        $rs_radius = $radius * $rate;
        $rs_size = $rs_radius * 2;
        
        if($cornercolor===false)
        {
            $sOldName = $img;
            $sNewName = $img;
            if($this->oImg['format']!=IMAGETYPE_PNG)
            {
                $nLength=strrpos($img,'.');
                $sNewName=substr($img,0,$nLength).'.png'; 
            }
            /*если углы прозрачные закругляем уголки без заливки цветом*/
            $cornercolor = 'transparent';
            if(exec($this->sIMagickPath.'convert "'.$sOldName.'" -border 0 -format "roundrectangle 0,0 %[fx:w],%[fx:h] '.$radius.','.$radius.'" info: > '.$this->sPath.'/tmp.mvg'))
                $this->errors->set('no_rouncorners');
             if(exec($this->sIMagickPath.'convert  "'.$sOldName.'" -border 0 -matte -channel RGBA -threshold -1 -background '.$cornercolor.' -fill none  -strokewidth 0 -draw "@'.$this->sPath.'/tmp.mvg"  '.$this->sPath.'/__overlay.png'))
                $this->errors->set('no_rouncorners');
             if(exec($this->sIMagickPath.'convert "'.$sOldName.'" -border 0 -matte -channel RGBA -threshold -1 -background '.$cornercolor.' -fill white  -strokewidth 0 -draw "@'.$this->sPath.'/tmp.mvg" '.$this->sPath.'/__mask.png '))
                $this->errors->set('no_rouncorners');
             
             if(exec($this->sIMagickPath.'convert "'.$sOldName.'" -matte -bordercolor '.$cornercolor.'  -border 0 '.$this->sPath.'/__mask.png -compose DstIn -composite '.$this->sPath.'/__overlay.png -compose Over -composite  -quality 95 "'.$sNewName.'"  '))
                $this->errors->set('no_rouncorners');
             
             if($sOldName != $sNewName)
             {
                 unlink($sOldName);
                 rename($sNewName,$sOldName);
             }   
             unlink($this->sPath.'/__mask.png'); 
             unlink($this->sPath.'/__overlay.png'); 
        } 
        else
        {
            //перевед 16-ного числа в нужную строку для определения цвета
            if($cornercolor < 0x1)
            {
                $addzero = '000000';
            }                       
            else if($cornercolor<0x10)
            {
                $addzero='00000';
            }
            else if($cornercolor<0x100)
            {
                $addzero='0000';
            }
            else if($cornercolor<0x1000)
            {
                $addzero='000';
            }
            else if($cornercolor<0x10000)
            {
                $addzero='00';
            }
            else if($cornercolor<0x100000)
                $addzero='0';
            else
                $addzero='';
                
            $cornercolor='#'.$addzero.dechex($cornercolor).'';
             
           /*если углы не прозрачные закругляем уголки и заливаем из выставленным цветом*/ 
            if(exec($this->sIMagickPath.'convert "'.$img.'"  -border 0 -format "fill '.$cornercolor.' rectangle 0,0 %[fx:w],%[fx:h]" info: > '.$this->sPath.'/tmp.mvg'))
                $this->errors->set('no_rouncorners'); 
            if(exec( $this->sIMagickPath.'convert "'.$img.'" -matte -channel RGBA -threshold -1 -draw "@'.$this->sPath.'/tmp.mvg" PNG:'.$this->sPath.'/__underlay.png'))
               $this->errors->set('no_rouncorners');
            if(exec($this->sIMagickPath.'convert '.$this->sPath.'/__underlay.png ( "'.$img.'" ( +clone -threshold -1 -draw "fill black polygon 0,0 0,'.$radius.' '.$radius.',0 fill white circle '.$radius.','.$radius.' '.$radius.',0" ( +clone -flip ) -compose Multiply -composite ( +clone -flop ) -compose Multiply -composite -blur 1x1 ) +matte -compose CopyOpacity -composite ) -matte -compose over -composite "'.$img.'"'))
                $this->errors->set('no_rouncorners');
            
            unlink($this->sPath.'/__underlay.png');
        } 
        
        unlink($this->sPath.'/tmp.mvg'); 
        $this->oImg['orig_filename'] = $this->oImg['src'];
        return $this->errors->no();
     }

     /**
     * Указание пути к шрифтам
     * путь к дирректории, в которой лежат шрифты
     */
     function setFontDir($sPath)
     {
        $this->sFontDir=$sPath;
     }
     /**
     * проверка, является ли файл изображением
     * 
     * @param mixed $sPath - путь к файлу
     */
     private function checkIsImage($sPath)
     {
        $imSize = getimagesize($sPath);
        if(!empty($imSize) && in_array($imSize[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
            return true;
        }
        return false;
     }
     
     public function getErrors()
     {
        return $this->errors->show();
     }
     /**
     * Устанавливает лимит оперативной памяти
     * 
     * @param int $nSize - объем памяти в мегабайтах
     */
     public function setMemoryLimit($nSize)
     {
         $nSize = intval($nSize);
         if($nSize<=0)
         {
             $nSize=1;
         }
         ini_set('memory_limit', $nSize.'M');
     }
     public static function testIsSuccess()
     {
         if(!CThumbnail::$bIsComplete)
         {   
             header('location:'.$_SERVER['HHTP_REFERER'].'&errno=imposible');
         }  
     }
}    
