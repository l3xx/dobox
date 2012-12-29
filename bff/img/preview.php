<?php

/**
*  @desc    Класс сохранения/удаления скриншота
*  @version 0.5 (29.07.2009)
*/

class CPreview 
{
    protected $table = '';    
    protected $fieldPreview = 'preview';
    protected $fieldID = 'id';
    protected $path = '';
    protected $input = '';
    
    public $maxsize = 4194304; /** 4194304 - 4mb */ 
    public $sizes = array();   /** sizeinfo array (h - height, w - width, p - prefix, autofit - boolean, original - boolean) */
    public $filenameLetters = 7;
        
    public function __construct($table, $path, $sizes, $fieldPreview='preview', $fieldID='id', $input='preview') 
    {
        $this->table = $table;
        $this->path  = $path; 
        $this->sizes = $sizes;
        
        $this->fieldPreview = $fieldPreview;
        $this->fieldID = $fieldID;
        $this->input = $input;  
    }
    
    function addsize($aSizeInfo)
    {
        $this->sizes[] = $aSizeInfo;
    }
    
    /** загрузка(сохранение/обновление) скриншота
    * @param integer ID записи
    * @param boolean удалять предыдущий скриншот
    * @return имя файла успешно загруженного скриншота | false
    */
    function update($nRecordID, $bDeletePrevious = false, $bDoUpdateQuery = false)
    {
        global $oDb;
        
        if($nRecordID && !empty($_FILES) && $_FILES[$this->input]['error'] == UPLOAD_ERR_OK)
        { 
            $oUpload = new Upload($this->input, false);
            $aImageSize = getimagesize($_FILES[$this->input]['tmp_name']);
                         
            if($oUpload->isSuccessfull() && $aImageSize!==FALSE && 
               in_array($aImageSize[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
            {
                if($bDeletePrevious)
                    $this->delete($nRecordID, false);
                 
                $sExtension = func::image_type_to_extension($aImageSize[2], false);
                $sFilename  = Func::generateRandomName($this->filenameLetters, true, true).'.'.$sExtension;
                
                //проверяем размер файла
                if(!$oUpload->checkSize($this->maxsize))
                    return false;
                                                                 
                //создаем thumbnail
                $oThumb = new thumbnail($_FILES[$this->input]['tmp_name']);
                $oThumb->jpeg_quality(85);                             
                
                $bFileMoved = false;
                foreach($this->sizes as $s) 
                {
                    if(!empty($s['original'])) {
                        $oUpload->save($this->path, $nRecordID.'_'.$s['p'].$sFilename, false, false);
                        $bFileMoved = true;
                        break;
                    }
                    $oThumb->createTumbnail_if_more_then($this->path.$nRecordID.'_'.$s['p'].$sFilename, $s['w'], $s['h'], (isset($s['autofit'])?$s['autofit']:true)); 
                }
                 
                if(!$bFileMoved)
                    @unlink($_FILES[$this->input]['tmp_name']);
                
                if($bDoUpdateQuery)
                {                              
                    $oDb->execute("UPDATE $this->table 
                                   SET $this->fieldPreview =".$oDb->str2sql($sFilename)."
                                   WHERE $this->fieldID = $nRecordID");
                }
                  
                return $sFilename;
            }
        }
        return false;
    }
    
    /** 
    * удаление скриншота
    * @param integer ID записи
    * @param boolean обновлять запись в БД
    */
    function delete($nRecordID, $bUpdateRecord = true)
    {
        global $oDb;
        
        //удаляем скриншот
        if($nRecordID)
        {
            $sOldPreviewFilename = $oDb->one_data("SELECT $this->fieldPreview FROM $this->table WHERE $this->fieldID = $nRecordID LIMIT 1");
            if(!empty($sOldPreviewFilename))
            {
                foreach($this->sizes as $size)
                    @unlink($this->path.$nRecordID.'_'.$size['p'].$sOldPreviewFilename);
                
                if($bUpdateRecord)            
                    $oDb->execute("UPDATE $this->table SET $this->fieldPreview=".$oDb->str2sql('')." WHERE $this->fieldID=$nRecordID");
                
                return true;
            }
        }
        return false;
    }
    
    /** 
    * удаление забытых скриншотов :)
    * @param array модули (path, table)
    */
    function deletelost()
    {
        global $oDb;
        
        if(!func_num_args())
            return;
        
        $i = 0;
        
        clearstatcache();
        $modules = func_get_args();
        foreach($modules as $module)
        {
            $previews = $oDb->select_one_column('SELECT CONCAT(id, "_", '.$this->fieldPreview.') FROM '.$module['table'].' WHERE '.$this->fieldPreview.'!="" ');
            $previews[] = 'default.gif';
            
            $aFiles = CDir::getFiles($module['path'], false, false);
            foreach($aFiles as $f)
            {
                if(!in_array($f, $previews)) {
                    $i++;
                    @unlink($module['path'].'/'.$f);
                }
            }
        }
        
        return $i;
    }

        //UPLOAD_ERR_OK
        //Value: 0; There is no error, the file uploaded with success. 

        //UPLOAD_ERR_INI_SIZE
        //Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini. 

        //UPLOAD_ERR_FORM_SIZE
        //Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. 

        //UPLOAD_ERR_PARTIAL
        //Value: 3; The uploaded file was only partially uploaded. 

        //UPLOAD_ERR_NO_FILE
        //Value: 4; No file was uploaded. 

        //UPLOAD_ERR_NO_TMP_DIR
        //Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3. 

        //UPLOAD_ERR_CANT_WRITE
        //Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0. 
    
}  
