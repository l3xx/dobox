<?php

/**
*  Класс по работе с полями: images, favorite-image, images-count
*  Структура таблицы:
*  `img` text NOT NULL               //fieldImages
*  `imgfav` varchar(255) NOT NULL    //fieldFav
*  `imgcnt` tinyint(1) unsigned NOT NULL default 0 //fieldCount
* 
*  Changelog:
*  @version 1.4   (20.02.2011) добавлена работа со столбцом "imgcnt", для подсчета кол-ва прикрепленных изображений,
*                              изменен порядок параметров ф-ции setParams
*  @version 1.3   (26.04.2010) added saveImageFileFav, removed IMAGETYPE_WBMP 
*  @version 1.221 (07.03.2009) fix uploadImages: changed 'name' to 'tmp_name' 
*  @version 1.22 (06.03.2009) (mod uploadImages) +image_type_to_extension 
*  @version 1.21 (17.02.2009) (mod saveImageFile)
*  @version 1.2 (16.02.2009)
*/

abstract class dbImagesField extends Component 
{
    //название таблицы и полей
    protected $table   = '';
    protected $fID     = 'id';
    protected $fImages = 'img';
    protected $fFav    = '';
    protected $fCount  = '';
    
    //флаг, определяющий работу с полем 'fFav'
    protected $useFav = false;
    //флаг, определяющий работу с полем 'fCount' 
    protected $useCount = false;
        
    public function __construct() {
    }                              
    
    function setParams($sTableName, $sFieldID='id', $sFieldImages='images', $sFieldFav=false, $sFieldCount=false) {
       $this->table   = $sTableName;
       $this->fID     = $sFieldID; //id
       $this->fImages = $sFieldImages; //img, images, photos        
                   
       if($sFieldFav!==false) {
           $this->fFav = $sFieldFav; //imgfav, images_fav, photofav
           $this->useFav = true;
       }   
           
       if($sFieldCount!==false) {
           $this->fCount = $sFieldCount; //imgcnt, images_cnt, photocnt
           $this->useCount = true;
       }
    }

    /** 
     * Заполнение поля
     * @param integer ID записи
     * @param string строка имен файлов @example: '1.jpg, 2.jpg' (CSV) 
     * @param integer FAV индекс в массиве имен файлов
     */
    function setImages($ID, $sImages, $nFavoriteIndex = 0) {

        if(empty($sImages)) return false;
        
        $aImages = explode(',', $sImages);
        if(empty($aImages)) return false;

        $sImagesOld = $this->db->one_data("SELECT $this->fImages FROM $this->table WHERE $this->fID=$ID");
                    
        $sQuery = "UPDATE $this->table
                  SET $this->fImages = ".$this->db->str2sql((!empty($sImagesOld)?$sImagesOld.',':'').$sImages).
                      ($this->useCount?", $this->fCount = $this->fCount + ".sizeof($aImages):'')."
                  WHERE $this->fID = $ID";
        $this->db->execute($sQuery);

        //если FAV еще не был создан
        if( $this->useFav && empty($sImagesOld))
        {
            if(!isset($aImages[$nFavoriteIndex]))
                $nFavoriteIndex = 0;
                
            $sQuery = "UPDATE $this->table
                       SET $this->fFav = ".$this->db->str2sql($aImages[$nFavoriteIndex])."
                       WHERE $this->fID=$ID";
            $this->db->execute($sQuery);
            
            $this->saveImageFileFav($ID, $aImages[$nFavoriteIndex], 0);
        }
        return true;      
    }
    
    /** 
     * Заполнение FAV поля
     * @param integer ID записи
     * @param string имя FAV файлa @example: '1.jpg'
     */
    function setImageFavorite($ID, $sImageFavorite) 
    {
        if($this->useFav && !empty($sImageFavorite))
        {
            //check, if FAV in images array                                 
            $aData = $this->db->one_array("SELECT $this->fImages".($this->useFav?", $this->fFav":'')." FROM $this->table WHERE $this->fID=$ID");
            if(mb_strpos($aData[$this->fImages], $sImageFavorite) === FALSE)
                return false;                

            $sQuery = "UPDATE $this->table
                       SET $this->fFav = ".$this->db->str2sql($sImageFavorite)."
                       WHERE $this->fID=$ID";
            $this->db->execute($sQuery);
            
            $this->saveImageFileFav($ID, $sImageFavorite, $aData[$this->fFav]);
            return true;
        }
        return false;        
    }

    /** 
     * Удаление одного изображения
     * @param integer ID записи
     * @param string имя файлa изображения, которое необходимо удалить @example: '1.jpg'
     */
    function deleteImage($ID, $sImage) {
        if(!empty($sImage)) {
            return $this->deleteImagesSome($ID, array($sImage));
        }
        return false;
    }
    
    /** 
     * Удаление некоторых изображений
     * @param integer ID записи
     * @param array массив имен изображений, которые необходимо удалить
     */
    function deleteImagesSome($ID, $aImagesDelete) 
    {
        if(!empty($aImagesDelete))
        {
            $data = $this->db->one_array("SELECT $this->fImages".($this->useFav?", $this->fFav":'')." FROM $this->table WHERE $this->fID=$ID");
            $aImagesLeave = array_diff( explode(',', $data[$this->fImages]) , $aImagesDelete); //array of images to leave
            
            foreach($aImagesDelete as $k=>$v)
                $this->deleteImageFile($ID, $v, (!$this->useFav ? false : $v == $data[$this->fFav]) ); 
                                   
            if(count($aImagesLeave)==0) {
                $this->db->execute("UPDATE $this->table SET $this->fImages=".$this->db->str2sql('').
                                ($this->useFav?", $this->fFav=".$this->db->str2sql(''):''). 
                                ($this->useCount?", $this->fCount=0":'').
                              " WHERE $this->fID=$ID");
            }
            else
            {
                //если FAV был удален, берем первый файл из оставшихся
                $sAdd2Query = '';
                if($this->useFav && in_array($data[$this->fFav], $aImagesLeave) === FALSE) {
                    $sAdd2Query .=", $this->fFav = ".$this->db->str2sql(current($aImagesLeave));                     
                    $this->saveImageFileFav($ID, current($aImagesLeave), 0);    
                }
                if($this->useCount) {
                    $sAdd2Query .=", $this->fCount = ".count($aImagesLeave);
                }
                $this->db->execute("UPDATE $this->table SET $this->fImages=".$this->db->str2sql(join(',', $aImagesLeave))." $sAdd2Query WHERE $this->fID=$ID"); 
            }
        }
    }
    
    /** 
     * Удаление всех изображений
     * @param mixed ID записи
     * @param boolean флаг, определяющий требуется ли актуализировать записи в БД
     */
    function deleteImagesAll($mID, $bUpdateRows = true) 
    {
        $aData = $this->db->one_array("SELECT $this->fImages ".
                                        ($this->useFav?", $this->fFav":'')." 
                                  FROM $this->table WHERE $this->fID=$mID");
                                  
        $aImages = explode(',', $aData[$this->fImages]);
        if(empty($aImages)) return true;
        
        foreach($aImages as $k=>$v)
            $this->deleteImageFile($mID, $v, (!$this->useFav ? false : $v == $aData[$this->fFav]));
        
        if($bUpdateRows)  
            $this->db->execute("UPDATE $this->table SET $this->fImages=".$this->db->str2sql('').
                            ($this->useFav?", $this->fFav=".$this->db->str2sql(''):'').
                            ($this->useCount?", $this->fCount=0":''). 
                          " WHERE $this->fID=$mID");
    }

    /** 
     * Загрузка файлов изображений, при помощи input=file
     * @param integer ID записи
     * @param integer кол-во букв в сгенерированном имени файла
     * @return string имена загруженных файлов @example: '1.jpg,2.jpg' (CSV)
     */
    function uploadImages($ID, $nGenerateLettersCount = 5) 
    {
        $aFilenames = array();
        if(func::isPostMethod())
        {   
            $ID = intval($ID); 
            
            if(empty($nGenerateLettersCount))
                $nGenerateLettersCount = 5;
                
            foreach($_FILES as $sInputName => $aFile)      
            {   
                if($aFile['name']!='')
                {    
                    $pUpload = new Upload($sInputName);
                    $aImageSize = getimagesize( $pUpload->getFilenameUploaded() );
                    if($aImageSize!==FALSE && $pUpload->isSuccessfull() && 
                       in_array($aImageSize[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
                    {   
                        $sExtension = func::image_type_to_extension($aImageSize[2], false);                        
                        $sFilename = Func::generateRandomName($nGenerateLettersCount, false, true);
                        $sFilenameFull = "$sFilename.$sExtension";
                        if( $this->saveImageFile($ID, $sFilenameFull, $pUpload, array('name'=>$sFilename, 'ext'=>$sExtension) ) ) {
                            $aFilenames[] = $sFilenameFull;
                        }
                    }
                }
            }
        }
        
        if(count($aFilenames)>0) {
            $sFilenames = implode(',',$aFilenames);
        }
        
        return (isset($sFilenames)?$sFilenames:'');
    }

    /** 
     * Загрузка файлов изображений через swfuploader
     * @param integer ID записи
     * @param integer кол-во букв в сгенерированном имени файла
     * @return string имена загруженных файлов @example: '1.jpg,2.jpg' (CSV)
     */
    function uploadImageSWF($ID, $nGenerateLettersCount = 5) 
    {
        Upload::swfuploadStart( );

        $sImg = $this->uploadImages($ID, $nGenerateLettersCount);
        $this->setImages($ID, $sImg);

        Upload::swfuploadFinish( $sImg );
    }
    
    /** 
     * Сохранение файла изображения
     * @param integer ID записи
     * @param string полное имя файла
     * @param object Upload объект      
     * @param array массив (name=> имя файла(без точки и расширения), ext=>расширение )
    */
    function saveImageFile($ID, &$sFilenameFull, &$pUpload, $aFilenameParams) {
        return false;   
    }

    /** 
     * Сохранение файла избранного изображения, в случае если требуется отдельный формат
     * @param integer ID записи
     * @param string имя нового избранного файла
     * @param string имя предыдущего избранного файла
     */
    function saveImageFileFav($ID, $sFilenameNew, $sFilenameOld='') {
        return false;
    }
    
    /** 
     * Удаление файла изображения
     * @param integer ID записи
     * @param string имя файла
     * @param boolean удаляемый файл был избранным
     */
    function deleteImageFile($ID, $sFilename, $bFavotite=false) {       
        return false;
    }
    
}  
