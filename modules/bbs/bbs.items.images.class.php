<?php

class BBSItemsImages extends dbImagesField 
{
    function __construct()
    {
       $this->setParams(TABLE_BBS_ITEMS, 'id', 'img', 'imgfav', 'imgcnt'); 
       $this->init(); 
    }
    
    /**
    * @return BBS объект
    */
    function getBBSModule()
    {
        return bff::i()->GetModule('bbs');
    }

    /** 
    * Сохранение файла изображения
    * @param string путь для сохранения
    * @param integer ID объекта
    * @param array данные о загрузке   
    */
    function saveImageFileCustom($sPath, $nID, $aUploadData)
    {                   
        $sFilename = func::generator(12).'.'.$aUploadData['ext'];
        
        $oThumb = new CThumbnail($aUploadData['tmp_name'], false);
        
        $aSave = array();         
        $aSave[] = array( 'filename'=>$sPath.$nID.'s'.$sFilename,
                          'width'=>74,
                          'height'=>74,
                          'autofit'=>true,  
                          'crop_h'=>'center',
                          'crop_v'=>'center',
                          'quality'=>90 );
                          
        $aSave[] = array( 'filename'=>$sPath.$nID.'t'.$sFilename,
                          'width'=>102,
                          'height'=>102,
                          'autofit'=>true,  
                          'crop_h'=>'center',
                          'crop_v'=>'center',
                          'quality'=>90 );
        
        $aSave[] = array( 'filename'=>$sPath.$nID.$sFilename,
                          'width'=>350,
                          'height'=>false,
                          'autofit'=>true,
                          'crop_h'=>'center',
                          'crop_v'=>'center',                   
                          'quality'=>90 );
        
        if(!$oThumb->save($aSave))
        {
            return false;
        }
          
        return $sFilename;
    }
    
    /** 
    * Переименовывание файла изображения
    * @param string путь к изображению
    * @param integer ID объекта
    * @param string имя файла
    */
    function renameImageFileCustom($sPath, $nID, $sFilename)
    {
        if(file_exists($sPath.'0'.$sFilename)) {
            @rename($sPath.'0s'.$sFilename, $sPath.$nID.'s'.$sFilename);
            @rename($sPath.'0t'.$sFilename, $sPath.$nID.'t'.$sFilename);
            @rename($sPath.'0'.$sFilename, $sPath.$nID.$sFilename);
            return true;
        }
        return false;
    } 
       
    /** 
    * Удаление файла изображения
    * @param string путь для сохранения
    * @param integer ID объекта    
    * @param string имя файла
    */
    function deleteImageFileCustom($sPath, $nID, $sFilename)
    {
        if($nID>0) {
            $this->deleteImage($nID, $sFilename);
        }
        
        @unlink($sPath.$nID.'s'.$sFilename);
        @unlink($sPath.$nID.'t'.$sFilename);
        @unlink($sPath.$nID.$sFilename);
    }
}  
