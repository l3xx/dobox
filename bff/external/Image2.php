<?php

require_once PATH_CORE.'external/LiveImage/Image.php';

class ImageConverter extends LiveImage 
{ 
    /**
     * Неопределенный тип ошибки при загрузке изображения
     */
    const UPLOAD_IMAGE_ERROR      = 1;
    /**
     * Ошибка избыточного размера при загрузке изображения
     */
    const UPLOAD_IMAGE_ERROR_SIZE = 2;
    /**
     * Неизвестный тип изображения
     */
    const UPLOAD_IMAGE_ERROR_TYPE = 4;
    /**
     * Ошибка чтения файла при загрузке изображения
     */
    const UPLOAD_IMAGE_ERROR_READ = 8;
    
    /**
     * Настройки модуля по умолчанию
     *
     * @var array
     */
    protected $aParamsDefault = array();
    
    /**
     * Тескт последней ошибки
     *
     * @var unknown_type
     */
    protected $sLastErrorText = null;
    
    /**
     * Инициализация модуля
     */
    public function Init() {    
        $this->aParamsDefault = array(
            'watermark_use'=>false,
            'round_corner' =>false
        );
    }
    /**
     * Получает текст последней ошибки
     *
     * @return unknown
     */
    public function GetLastError() {
        return $this->sLastErrorText;
    }
    /**
     * Устанавливает текст последней ошибки
     *
     * @param unknown_type $sText
     */
    public function SetLastError($sText) {
        $this->sLastErrorText=$sText;
    }
    /**
     * Очищает текст последней ошибки
     *
     */
    public function ClearLastError() {
        $this->sLastErrorText=null;
    }

    /**
     * Resize,copy image, 
     * make rounded corners and add watermark
     *
     * @param  string $sFileSrc
     * @param  string $sDirDest
     * @param  string $sFileDest  
     * @param  int    $iWidthDest
     * @param  int    $iHeightDest
     * @param  bool   $bForcedMinSize
     * @param  array  $aParams
     * @return string
     */
    public function doResize($sFileSrc,$sDirDest,$sFileDest,$iWidthDest=null,$iHeightDest=null,$bForcedMinSize=true,$aParams=null) 
    {
        $this->ClearLastError();
        /**
         * Если параметры не переданы, устанавливаем действия по умолчанию
         */
        if(!is_array($aParams)) {
            $aParams = $this->aParamsDefault;
        }
        
        if($this->get_last_error()){
            $this->SetLastError($this->get_last_error());
            return false;
        }

        $sFileDest .= '.'.$this->get_image_params('format');
        $sFileFullPath = $sDirDest.$sFileDest;
            
        if ($iWidthDest) {
            if ($bForcedMinSize and ($iWidthDest>$this->get_image_params('width'))) {
                $iWidthDest = $this->get_image_params('width');
            }
            /**
             * Ресайзим и выводим результат в файл.
             * Если не задана новая высота, то применяем масштабирование.
             * Если нужно добавить Watermark, то запрещаем ручное управление alfa-каналом
             */
            $this->resize($iWidthDest,$iHeightDest,(!$iHeightDest),(!$aParams['watermark_use']));
            
            /**
             * Добавляем watermark согласно в конфигурации заданым параметрам
             */
            if($aParams['watermark_use']) {
                switch($aParams['watermark_type']) {
                    default:
                    case 'text':
                        $this->set_font(
                            $aParams['watermark_font_size'],  0, 
                            $aParams['path']['fonts'].$aParams['watermark_font'].'.ttf'
                        );
                        
                        $this->watermark(
                            $aParams['watermark_text'], 
                            explode(',',$aParams['watermark_position'],2), 
                            explode(',',$aParams['watermark_font_color']), 
                            explode(',',$aParams['watermark_back_color']), 
                            $aParams['watermark_font_alfa'], 
                            $aParams['watermark_back_alfa']
                        );    
                        break;
                    case 'image':
                        $this->paste_image(
                            $aParams['path']['watermarks'].$aParams['watermark_image'],
                            true, explode(',',$aParams['watermark_position'],2)
                        );    
                        break;
                }
            }
            /**
             * Скругляем углы
             */
            if($aParams['round_corner']) {
                $this->round_corners($aParams['round_corner_radius'], $aParams['round_corner_rate']);
            }
            /**
             * Для JPG формата устанавливаем output quality, если это предусмотрено в конфигурации
             */
            if(isset($aParams['jpg_quality']) and $this->get_image_params('format')=='jpg') {
                $this->set_jpg_quality($aParams['jpg_quality']);
            }
            
            $this->output(null,$sFileFullPath);
            
            chmod($sFileFullPath,0666);
            return $sFileDest;
        } elseif (copy($sFileSrc,$sFileFullPath)) {
            chmod($sFileFullPath,0666);
            return $sFileDest;
        }
        
        return false;
    }
    
    /**
     * Вырезает максимально возможный квадрат
     *
     * @return boolean
     */
    public function CropSquare() 
    {
        if(!$this->get_last_error()) {
            return false;
        }
        $iWidth  = $this->get_image_params('width');
        $iHeight = $this->get_image_params('height');

        # Если высота и ширина совпадают
        if($iWidth==$iHeight){ return true; }
        
        # Вырезаем квадрат из центра
        $iNewSize = min($iWidth,$iHeight);
        $this->crop($iNewSize,$iNewSize,($iWidth-$iNewSize)/2,($iHeight-$iNewSize)/2);

        return true;
    }
}