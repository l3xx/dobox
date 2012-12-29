<?php

##############################################
# Shiege Iseng Resize Class
# 11 March 2003
# shiegege_at_yahoo.com
# View Demo :
#   http://kentung.f2o.org/scripts/thumbnail/sample.php
################
# Thanks to :
# Dian Suryandari <dianhau_at_yahoo.com>
/*############################################
Sample :
$thumb=new thumbnail("./shiegege.jpg");            // generate image_file, set filename to resize
$thumb->size_width(100);                // set width for thumbnail, or
$thumb->size_height(300);                // set height for thumbnail, or
$thumb->size_auto(200);                    // set the biggest width or height for thumbnail
$thumb->jpeg_quality(75);                // [OPTIONAL] set quality for jpeg only (0 - 100) (worst - best), default = 75
$thumb->show();                        // show your thumbnail
$thumb->save("./huhu.jpg");                // save your thumbnail to file
----------------------------------------------
Note :
- GD must Enabled
- Autodetect file extension (.jpg/jpeg, .png, .gif, .wbmp)
but some server can't generate .gif / .wbmp file types
- If your GD not support 'ImageCreateTrueColor' function,
change one line from 'ImageCreateTrueColor' to 'ImageCreate'
(the position in 'show' and 'save' function)
*/############################################
/** @version: 1.15
 *  @changed to 1.15 + roundcorners
 *  @changed to 1.12
 */

/**
 * create thumbnail
 * @internal
 *   @$this->img["lebar"]        source image X
 *   @$this->img['tinggi']       source image Y
 *   @$this->img["lebar_thumb"]  result size image X
 *   @$this->img["tinggi_thumb"] result size image Y
 *
 *
 */
class thumbnail
{
    var $img;
    var $bSaveAsOriginal  = false;
    var $bProgressive     = true;
    var $sSourcePath2file = '';
    /**
     * constructor for resizing image
     * @internal
     *  @$this->img["lebar"]     source image X
     *  @$this->img['tinggi']    source image Y
     *
     * @param string $imgfile
     * @return void
     */
    function thumbnail($imgfile)
    {
        if(!file_exists($imgfile)) 
            return false;
        
        ini_set('display_errors', '0');

        $this->sSourcePath2file = $imgfile;
        $this->img["src"] = $imgfile;

        $aImageSize = getimagesize($imgfile);
        $this->img['format'] = $aImageSize[2];
        
        if (extension_loaded('gd') || extension_loaded('gd2') )
        {                      
            set_time_limit('6000');
            ini_set('memory_limit', '100M');
            ini_set('post_max_size', '7M');            

            switch($this->img['format'])
            {
                case IMAGETYPE_GIF:  { $this->img['src'] = ImageCreateFromGIF($imgfile);  } break;  
                case IMAGETYPE_JPEG: { $this->img['src'] = ImageCreateFromJPEG($imgfile); } break;
                case IMAGETYPE_PNG:  { $this->img['src'] = ImageCreateFromPNG($imgfile);  } break;
                case IMAGETYPE_WBMP: { $this->img['src'] = ImageCreateFromWBMP($imgfile); } break; 
                default: { echo 'Not Supported File'; exit(); }
            }
        }
        

        /**
         *     @$this->img["lebar"] image X
         *    @$this->img['tinggi'] image Y
         */              
        
        $this->img['lebar'] = $this->img["lebar_thumb"] = $this->img["width"] = $aImageSize[0]; //imagesx($this->img["src"]);
        $this->img['tinggi'] = $this->img["tinggi_thumb"] = $this->img["height"] = $aImageSize[1];// imagesy($this->img["src"]);
        //default quality jpeg
        $this->img["quality"]=85;

        $this->img['src_x'] = 0;
        $this->img['src_y'] = 0;

    }

    function size_height($nSize = 100)
    {
        //height
        $this->img["tinggi_thumb"] = $nSize;
        @$this->img["lebar_thumb"] = ($this->img["tinggi_thumb"]/$this->img['tinggi'])*$this->img['lebar'];
    }

    function size_width($nSize = 100)
    {
        //width
        $this->img["lebar_thumb"]   = $nSize;
        @$this->img["tinggi_thumb"] = ($this->img["lebar_thumb"]/$this->img['lebar'])*$this->img['tinggi'];
    }

    function size_auto($size=100, $reverse = false)
    {
        if(!$reverse)
        {
            //size
            if ($this->img['lebar']>=$this->img['tinggi']) {
                $this->img["lebar_thumb"]=$size;
                @$this->img["tinggi_thumb"] = ($this->img["lebar_thumb"]/$this->img['lebar'])*$this->img['tinggi'];
            } else {
                $this->img["tinggi_thumb"]=$size;
                @$this->img["lebar_thumb"] = ($this->img["tinggi_thumb"]/$this->img['tinggi'])*$this->img['lebar'];
            }
        }
        else
        {
            //size
            if ($this->img['lebar']<=$this->img['tinggi']) {
                $this->img["lebar_thumb"]=$size;
                @$this->img["tinggi_thumb"] = ($this->img["lebar_thumb"]/$this->img['lebar'])*$this->img['tinggi'];
            } else {
                $this->img["tinggi_thumb"]=$size;
                @$this->img["lebar_thumb"] = ($this->img["tinggi_thumb"]/$this->img['tinggi'])*$this->img['lebar'];
            }
        }
    }

    function jpeg_quality($nQuality = 75)
    {
        //jpeg quality
        $this->img['quality'] = (int)$nQuality;
    }

    function show()
    {   
        //show thumb
        @Header('Content-Type: '.image_type_to_mime_type($this->img['format']) );

        /* change ImageCreateTrueColor to ImageCreate if your GD not supported ImageCreateTrueColor function*/
        $this->img["des"] = ImageCreateTrueColor($this->img["lebar_thumb"],$this->img["tinggi_thumb"]);
        @imageCopyResampled ($this->img["des"], $this->img["src"], 0, 0, 0, 0, $this->img["lebar_thumb"], $this->img["tinggi_thumb"], $this->img['lebar'], $this->img['tinggi']);

        switch($this->img['format'])
        {
            case IMAGETYPE_GIF:  { imageGIF($this->img["des"]);  } break;  
            case IMAGETYPE_JPEG: { imageJPEG($this->img["des"], "", $this->img["quality"]); } break;
            case IMAGETYPE_PNG:  { imagePNG($this->img["des"]);  } break;
            case IMAGETYPE_WBMP: { imageWBMP($this->img["des"]); } break; 
        }
    } 
    
    /**
     * ctreates round corners
     * @param string   destination image key
     * @param integer  radius (0-100)
     * @param integer  rounding quality (1-5)
     * @return void
     */
    function make_roundcorners($sDestKey, $radius=5, $rate=5)
    {
        $width = ImagesX($this->img[$sDestKey]);
        $height = ImagesY($this->img[$sDestKey]);
     
        ImageAlphablending($this->img[$sDestKey], false);
        ImageSaveAlpha($this->img[$sDestKey], true);
     
        $rs_radius = $radius * $rate;
        $rs_size = $rs_radius * 2;
     
        $corner = ImageCreateTrueColor($rs_size, $rs_size);
        ImageAlphablending($corner, false);
     
        $trans = ImageColorAllocateAlpha($corner, 255, 255, 255, 127);
        ImageFill($corner, 0, 0, $trans);
     
        $positions = array(
            array(0, 0, 0, 0),
            array($rs_radius, 0, $width - $radius, 0),
            array($rs_radius, $rs_radius, $width - $radius, $height - $radius),
            array(0, $rs_radius, 0, $height - $radius),
        );
     
        foreach ($positions as $pos) {
            ImageCopyResampled($corner, $this->img[$sDestKey], $pos[0], $pos[1], $pos[2], $pos[3], $rs_radius, $rs_radius, $radius, $radius);
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
            ImageCopyResampled($this->img[$sDestKey], $corner, $pos[2], $pos[3], $pos[0], $pos[1], $radius, $radius, $rs_radius, $rs_radius);
        }
        ImageDestroy($corner);

    }
    
    function init_watermark($sWatermarkFilename, $hPosition='right', $vPosition='bottom', $edgePadding = 15, $bMakeSourceWatermark = true)
    {
        $size = getimagesize($sWatermarkFilename);
        switch($size[2])
        {
            case IMAGETYPE_GIF:  $this->img["wm"] = imageCreateFromGIF($sWatermarkFilename);  break;
            case IMAGETYPE_JPEG: $this->img["wm"] = imageCreateFromJPEG($sWatermarkFilename); break;
            case IMAGETYPE_PNG:  $this->img["wm"] = imageCreateFromPNG($sWatermarkFilename);  break;
            case IMAGETYPE_WBMP: $this->img["wm"] = imageCreateFromWBMP($sWatermarkFilename); break;
            default: return;
        }
        $this->img["wm_width"] = $size[0];
        $this->img["wm_height"] = $size[1];
        $this->img["wm_h_pos"] = $hPosition; 
        $this->img["wm_v_pos"] = $vPosition;
        $this->img["wm_padding"] = $edgePadding; 
        
        //накладываем watermark на файл ($this->sSourcePath2file)
        //на тот случай если bSaveAsOriginal (тогда оригинал уже будет с watermark)
        if($bMakeSourceWatermark)
        {
            $this->img["only_src"] = imageCreateTrueColor($this->img["width"], $this->img["height"]);
            ImageCopy($this->img["only_src"], $this->img["src"], 0, 0, 0, 0, $this->img["width"], $this->img["height"]); 
        
            $this->make_watermark("only_src", $this->img["width"], $this->img["height"]);

            switch($this->img['format'])
            {
                case IMAGETYPE_GIF:  { imageGIF($this->img["only_src"], $this->sSourcePath2file);  } break;  
                case IMAGETYPE_JPEG: { imageJPEG($this->img["only_src"],$this->sSourcePath2file, $this->img["quality"]); } break;
                case IMAGETYPE_PNG:  { imagePNG($this->img["only_src"], $this->sSourcePath2file); } break;
                case IMAGETYPE_WBMP: { imageWBMP($this->img["only_src"],$this->sSourcePath2file); } break; 
            }

            imageDestroy($this->img["only_src"]);   
        }
    }
    
    function make_watermark($sDestKey, $sDestWidth, $sDestHeight)
    {
        if(isset($this->img["wm"]))
        {
            //определяем позиционирование
            switch($this->img["wm_h_pos"]){ // find the X coord for placement
                case 'left':   $placementX = $this->img["wm_padding"]; break;
                case 'center': $placementX = round( ($sDestWidth - $this->img["wm_width"]) / 2); break;
                case 'right':  $placementX = $sDestWidth - $this->img["wm_width"] - $this->img["wm_padding"]; break;
            }

            switch($this->img["wm_v_pos"]){
                // find the Y coord for placement
                case 'top':    $placementY = $edgePadding; break;
                case 'center': $placementY = round( ($sDestHeight - $this->img["wm_height"]) / 2); break;
                case 'bottom': $placementY = $sDestHeight - $this->img["wm_height"] - $this->img["wm_padding"]; break;
            }
        
            //накладывем watermark
            imagecopy($this->img[$sDestKey],
                $this->img["wm"],
                $placementX, $placementY, //position
                0, 0, $this->img["wm_width"], $this->img["wm_height"]);
        }
    }

    function save($save="", $bMakeWatermark=false, $nRoundCornersRadius=false)
    {
        if($this->bSaveAsOriginal)
        {
            $res = @copy($this->sSourcePath2file, $save);
            if(!$res)
            {
                echo 'cann`t writre to '.$save;
                exit();
            }
        }
        if (extension_loaded('gd') || extension_loaded('gd2') )
        {
            if (!function_exists('imagecreatetruecolor'))
            {   
                str2log(__CLASS__.'::'.__FUNCTION__.' - cant`t find ImageCreateTrueColor()', 'error.log');
                return false;
            }
            if (!function_exists('imagecopyresampled'))
            {
                str2log(__CLASS__.'::'.__FUNCTION__.' - cant`t find ImageCopyResampled()', 'error.log');
                return false;
            }
                                         
            //save thumb
            if (empty($save)) $save = mb_strtolower('./thumb'.func::image_type_to_extension($this->img['format'], true) );
            /* change ImageCreateTrueColor to ImageCreate if your GD not supported ImageCreateTrueColor function*/
                
            $this->img["des"] = ImageCreateTrueColor($this->img["lebar_thumb"], $this->img["tinggi_thumb"]);
            
            // задаем чересстрочный режим
            //if ($this->bProgressive) ImageInterlace($this->img["des"], 1);              
            
            if($this->img['format'] == IMAGETYPE_PNG || 
               $this->img['format'] == IMAGETYPE_GIF) {
               @ImageAlphaBlending($this->img["des"], false);
               @ImageSaveAlpha($this->img["des"], true);
            }
            
            ImageCopyResampled ($this->img["des"], $this->img["src"], 0, 0, $this->img['src_x'], $this->img['src_y'], $this->img["lebar_thumb"], $this->img["tinggi_thumb"], $this->img['lebar'], $this->img['tinggi']);
            
            if($bMakeWatermark)
            {
                $this->make_watermark('des', $this->img["lebar_thumb"], $this->img["tinggi_thumb"]);
            }
            
            $nImageFormat = $this->img['format'];
            if(isset($nRoundCornersRadius) && $nRoundCornersRadius!==false && $nRoundCornersRadius>0)
            {
                $this->make_roundcorners('des', $nRoundCornersRadius);
                $nImageFormat = IMAGETYPE_PNG;
            }
                
            //сохраняем картинку
            switch($nImageFormat)
            {
                case IMAGETYPE_GIF:  { imageGIF($this->img['des'],  $save);  } break;  
                case IMAGETYPE_JPEG: { imageJPEG($this->img['des'], $save, $this->img['quality']); } break;
                case IMAGETYPE_PNG:  { imagePNG($this->img['des'],  $save);  } break;
                case IMAGETYPE_WBMP: { imageWBMP($this->img['des'], $save); } break; 
            }

            ImageDestroy ($this->img['des']);
        }
        else
        {

            $res = system('convert '.$this->img['src'].' '.'-resize "'.$this->img["lebar_thumb"].'x'.$this->img["tinggi_thumb"].'"  '.$save.' > /dev/null 2>/opt/hosting/www.alof.com/htdocs/log.txt', $retval);
            //debug('convert  '.'-resize '.((int)$this->img["lebar_thumb"]).'x'.((int)$this->img["tinggi_thumb"]).' '.$this->img['src'].' '.$save);
            $res = system('convert -version');
            if(!$res)
            {
                debug('Sorry, imageMagic - not defined!. GD library not defined!. Please Contact to administrator.');
                exit();
            }
        }

        return true;
    }

    function chage_size($width, $height)
    {
        $this->img["tinggi_thumb"]= $height;
        $this->img["lebar_thumb"] = $width;
    }

    function size_auto_in_rect($width, $height)
    {
        $this->img["lebar_thumb"] = $width;
        @$this->img["tinggi_thumb"] = round( ($this->img["lebar_thumb"]/$this->img['lebar'])*$this->img['tinggi'] );

        if($this->img["tinggi_thumb"]>$height)
        {
            $this->img["tinggi_thumb"] = $height;
            @$this->img["lebar_thumb"] = round( ($this->img["tinggi_thumb"]/$this->img['tinggi'])*$this->img['lebar'] );
        }
    }



    function size_auto_in_rect_one_side($width, $height)
    {

        $x = $this->img['lebar'];
        $y = $this->img['tinggi'];

        if( $width/$height > $x/$y   )
        {
            $this->size_width($width);
        }
        else
        {
            $this->size_height($height);
        }

    }

    /**
     * Crop Image by Width
     *    required GD2
     * 
     * @param size $width
     * @param string_center_left_right_null $align
     * @param int $position_X
     */
    function crop_width($width, $align = 'center', $position_X = null )
    {
        //$width = $width*$this->img['w/h'];
        $width = (int)$width;
        //width
        $this->img["lebar_thumb"]  = $width;
        //height
        $this->img["tinggi_thumb"] = $this->img['tinggi'];


        //     crop from custom position size:
        if(isset($position_X))
        {
            $this->img['src_x'] = $position_X;
            $this->img['lebar'] = $width;
            return ;
        }

        switch ($align)
        {
            case 'left':
                $this->img['src_x'] = 0;
                $this->img['lebar'] = $width;
                break;
            case 'right':
                $this->img['src_x'] = $this->img['lebar']-$width;
                $this->img['lebar'] = $width;
                break;
            default://center
            $center = (int) $this->img['lebar']/2;
            $this->img['src_x'] = $center - (int)$width/2;
            $this->img['lebar'] = $width;//-= $this->img['src_x'];
        }
    }
    
    /**
     * Crop Image by Height
     * required GD2
     *
     * @param size $width
     * @param string_middle_top_bottom_null $align
     * @param int $position_Y
     */
    function crop_height($height, $valign = 'middle', $position_Y = null )
    {
        $height = (int)$height;
        //width
        $this->img["lebar_thumb"]= $this->img['lebar'];
        //height
        $this->img["tinggi_thumb"] =$height;

        //crop from custom position size:
        if(isset($position_Y))
        {
            $this->img['src_y'] = $position_Y;
            $this->img['tinggi'] = $height;
            return ;
        }

        switch ($valign)
        {
            case 'top':
                $this->img['src_y'] = 0;
                $this->img['tinggi'] = $height;
                break;
            case 'bottom':
                $this->img['src_y'] = $this->img['tinggi']-$height;
                $this->img['tinggi'] = $height;
                break;
            default://middle
            $center = (int) $this->img['tinggi']/2;
            $this->img['src_y'] = $center - (int)$height/2;
            $this->img['tinggi'] = $height;//-= $this->img['src_x'];
        }
    }
    
    /**
     * Обрезаем фото 
     * Подготовка фотографии к уменьшению
     * @param float $fPropWidth  - соотношение сторон
     * @param float $fPropHeight - соотношение сторон 
     * @param string_middle_top_bottom_null $valign  
     * @param string_center_right_left_null $align 
     */
    function crop_proportionaly($fPropWidth=1, $fPropHeight=1, $valign='top', $align='left')
    {
        $this->img['src_x']  = 0;
        $this->img['src_y']  = 0;
        $this->img['tinggi'] = $this->img['height'];
        $this->img['lebar']  = $this->img['width'];
  
        if( ($fPropWidth>=$fPropHeight ? $fPropWidth-$fPropHeight : $fPropHeight-$fPropWidth)>0 )
        {
            if($fPropWidth>$fPropHeight)
                $this->crop_height( ($this->img['width'] * $fPropHeight) ,$valign);
            else
                $this->crop_width( ($this->img['height'] * $fPropWidth) ,$align);
        }
        else
        {
            if($this->img['height']>$this->img['width'])
                $this->crop_height($this->img['width'],$valign);
            else if($this->img['height']<$this->img['width'])
                $this->crop_width($this->img['height'],$align);    
        }
    }
    
    /**
     * Crop image
     * require GD2 library
     *
     * @param int $width
     * @param int $height
     * @param string_center_right_left_null $align
     * @param string_middle_top_bottom_null $valign
     * @param int $position_X
     * @param int $position_Y
     */
    function crop($width, $height, $align='center', $valign = 'middle', $position_X= null, $position_Y = null)
    {
        //$width = $width*$this->img['w/h'];
        $width = (int)$width;
        //         width
        $this->img["lebar_thumb"]=$width;
        //         height
        $this->img["tinggi_thumb"] =$height;


        switch ($align)
        {
            case 'left':
                $this->img['src_x'] = 0;
                $this->img['lebar'] = $width;
                break;
            case 'right':
                $this->img['src_x'] = $this->img['lebar']-$width;
                $this->img['lebar'] = $width;
                break;
            case 'center':
                $center = (int) $this->img['lebar']/2;
                $this->img['src_x'] = $center - (int)$width/2;
                $this->img['lebar'] = $width;//-= $this->img['src_x'];
                break;
        }
        switch ($valign)
        {
            case 'top':
                $this->img['src_y'] = 0;
                $this->img['tinggi'] = $height;
                break;
            case 'bottom':
                $this->img['src_y'] = $this->img['tinggi']-$height;
                $this->img['tinggi'] = $height;
                break;
            case 'middle':
                $center = (int) $this->img['tinggi']/2;
                $this->img['src_y'] = $center - (int)$height/2;
                $this->img['tinggi'] = $height;//-= $this->img['src_x'];
                break;
        }



        //     crop from custom position size:
        if(isset($position_X) && isset($position_Y))
        {
            $this->img['src_x'] = $position_X;
            $this->img['lebar'] = $width;
            $this->img['src_y'] = $position_Y;
            $this->img['tinggi'] = $height;
            return ;
        }
        if(isset($position_X))
        {
            $this->img['src_x'] = $position_X;
            $this->img['lebar'] = $width;
        }
        if(isset($position_Y))
        {
            $this->img['src_y'] = $position_Y;
            $this->img['tinggi'] = $height;
        }
    }

    function size_width_if_more_then($max_width)
    {
        if($this->img['lebar']>$max_width) {
            $this->size_width($max_width);
        }
        else {
            $this->bSaveAsOriginal = true;
        }
    }

    function size_height_if_more_then($max_height)
    {
        if($this->img['tinggi']>$max_height) {
            $this->size_height($max_height);
        }
        else {
            $this->bSaveAsOriginal = true;
        }
    }

    function chage_size_if_more_then($max_width, $max_height)
    {
        if($this->img['tinggi']>$max_height || $this->img['lebar']>$max_width) {
            $this->chage_size($max_width, $max_height);
        }
        else {
            $this->bSaveAsOriginal = true;
        }
    }

    function size_auto_in_rect_if_more_then($max_width, $max_height)
    {
        if($this->img['tinggi']>$max_height || $this->img['lebar']>$max_width) {
            $this->size_auto_in_rect($max_width, $max_height);
        }
        else {
            $this->bSaveAsOriginal = true;
        }
    }
    
    function putInColorBox_and_save($fileresult, $resultWidth, $resultHeight, $bgColorRed=0xFF, $bgColorGreen=0xFF, $bgColorBlue=0xFF)
    {
        // get size
        $res = getImageSize($this->sSourcePath2file);
        $width = $res[0];
        $height = $res[1];

        // open image
        $img_src = $this->img["src"];

        // make new size
        $newimg_width = $resultWidth;
        $newimg_height = $newimg_width/$width * $height;
        if($newimg_height>$resultHeight)
        {
            $newimg_height = $resultHeight;
            $newimg_width = ($newimg_height/$height)*$width;
        }

        // create img with specific size resultWidth x resultHeight
        $im_new = ImageCreateTrueColor($resultWidth, $resultHeight);

        // set BG
        $color = ImageColorAllocate($im_new, $bgColorRed, $bgColorGreen, $bgColorBlue); ;
        $res = ImageFilledRectangle($im_new, 0,0, $resultWidth, $resultHeight, $color);
        // detact offset
        $dX = $resultWidth - $newimg_width;
        $dY = $resultHeight - $newimg_height;
        $dX /=2;
        $dY /=2;
        //put image to new image
        ImageCopyResampled($im_new, $img_src, $dX, $dY, 0,0, $newimg_width, $newimg_height, $width, $height);

        switch($this->img['format'])
        {
            case IMAGETYPE_GIF:  { imageGIF($im_new,  $fileresult); } break;  
            case IMAGETYPE_JPEG: { imageJPEG($im_new, $fileresult,$this->img["quality"]); } break;
            case IMAGETYPE_PNG:  { imagePNG($im_new,  $fileresult); } break;
            case IMAGETYPE_WBMP: { imageWBMP($im_new, $fileresult); } break; 
        }
    }
    
    function createTumbnail_if_more_then($sPath2File, $width=false , $height=false, $auto_fit=false, $bMakeWatermark=false, $nRoundCornersRadius=false)
    {
        if($width && $height && $auto_fit)
        {
            $this->size_auto_in_rect_if_more_then($width, $height);
        }
        else if($width && $height)
        {
            $this->chage_size_if_more_then($width, $height);
        }
        else if ($width)
        {
            $this->size_width_if_more_then($width);            
        }
        else if($height)
        {
            $this->size_height_if_more_then($height);
        }
        
        $res = $this->save($sPath2File, $bMakeWatermark, $nRoundCornersRadius);
        @chmod($sPath2File, 0666);
        return $res;
    }      
}
