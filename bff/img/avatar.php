<?php

/**
*  @desc    Класс сохранения/удаления аватары
*  @version 0.5 (29.07.2009)
*/

class CAvatar 
{
    protected $table = '';    
    protected $fieldAvatar = 'avatar';
    protected $fieldID = 'id';
    protected $path = '';
    protected $input = '';
    
    /** 
    * 4194304 - 4mb
    */   
    public $maxsize = 4194304;
    public $width = 46; 
    public $height = 46; 
    public $filenameLetters = 7;
        
    public function __construct($table, $path, $fieldAvatar='avatar', $fieldID='id', $input='avatar') 
    {
        $this->table = $table;
        $this->path  = $path; 
        
        $this->fieldAvatar = $fieldAvatar;
        $this->fieldID = $fieldID;
        $this->input = $input;
    }
    
    /** загрузка(сохранение/обновление) аватара
    * @param integer ID записи
    * @param boolean удалять предыдущий аватар
    * @return имя файла успешно загруженной аватары | false
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
                $oThumb->crop_proportionaly(1,1, 'middle', 'center');
                $oThumb->createTumbnail_if_more_then($this->path.$nRecordID.'_'.$sFilename, $this->width, $this->height, true); 
                @unlink($_FILES[$this->input]['tmp_name']);
                
                if($bDoUpdateQuery)
                {                              
                    $oDb->execute("UPDATE $this->table 
                                   SET $this->fieldAvatar =".$oDb->str2sql($sFilename)."
                                   WHERE $this->fieldID = $nRecordID ");
                }
                
                return $sFilename;
            }
        }
        return false;
    }
    
    /** 
    * удаление аватара
    * @param integer ID записи
    * @param boolean обновлять запись в БД
    */
    function delete($nRecordID, $bUpdateRecord = true)
    {
        global $oDb;
        
        //удаляем аватар
        if($nRecordID)
        {
            $sOldAvatarFilename = $oDb->one_data("SELECT $this->fieldAvatar FROM $this->table WHERE $this->fieldID = $nRecordID LIMIT 1");
            if(!empty($sOldAvatarFilename))
            {
                @unlink($this->path.$nRecordID.'_'.$sOldAvatarFilename);  
                
                if($bUpdateRecord)            
                    $oDb->execute("UPDATE $this->table SET $this->fieldAvatar='' WHERE $this->fieldID=$nRecordID ");
                
                return true;
            }
        }
        return false;
    }
    
    /** 
    * удаление забытых аватар :)
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
            $avatars = $oDb->select_one_column("SELECT (id || '_' || avatar) FROM \"{$module['table']}\" WHERE avatar!='' ");
            $avatars[] = 'default.gif';
            
            $aFiles = CDir::getFiles($module['path'], false, false);
            foreach($aFiles as $f)
            {
                if(!in_array($f, $avatars)) {
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