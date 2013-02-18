<?php

 /**
 * Класс загрузки файлов
 *
 */

class Upload
{
	var $fieldname;
    var $filename = '';
    var $filename_saved = '';    
    
    private $errors = null;   
        
	/**
	 * Конструктор
	 * @param string ключ в массиве FILES
	 * @param boolean сообщать ли об ошибке, в случае неудачно загруженного файла
	 * @return boolean
	 */
	public function __construct($sFieldname, $isCompulsory = false)
	{
		$this->fieldname = $sFieldname;
		
		if(!isset($_FILES[$sFieldname]) || !isset($_FILES[$sFieldname]['name']) || !$_FILES[$sFieldname]['name']) 
		{
			if($isCompulsory)
			{
                return $this->error('no_file');
			}
		}
        
        if($_FILES[$sFieldname]['error'] != UPLOAD_ERR_OK)
        {
            switch($_FILES[$sFieldname]['error'])
            {
                case UPLOAD_ERR_INI_SIZE:  //file exceeds the upload_max_filesize
                case UPLOAD_ERR_FORM_SIZE: //file exceeds the MAX_FILE_SIZE
                { 
                    //$this->error('size_max', $maxSize);
                } break;
            }
        }
        
		if(!is_uploaded_file($_FILES[$sFieldname]['tmp_name']))
		{   
            if($isCompulsory) {
                return $this->error('no_file');
            }
		}
		else 
		{
			if(isset($_FILES[$sFieldname]))
			{                                          
				$this->filename = $_FILES[$sFieldname]['name'];
			}
		}
	}
	
	function getFilename()
	{
		return $this->filename;
	}

    function getFilenameUploaded()
    {
        return $_FILES[$this->fieldname]['tmp_name'];
    }
    
	function getFilenameSaved()
	{
		return $this->filename_saved;
	}
	
	function checkIsIMG()
	{
        if($this->isError()) return false;
        
		if(strpos($_FILES[$this->fieldname]['type'], 'image/')!==false) {
			return true;
		}
		
        return $this->error('upload_img');
	}

	function checkSize( $maxSize = null )
	{
		if($this->isError()) return false;
		
		if($_FILES[$this->fieldname]['size']!=0)
		{           
			if( isset($maxSize) && 
                $_FILES[$this->fieldname]['size']>$maxSize)
			{
                return $this->error('size_max', $maxSize);
			}   
		}
		else 
		{
            return $this->error('size_0');
		}
        return true;
	}
	
	/**
	 * Сохраняем загруженный файл по указанному пути
	 * @param string путь для сохранения
     * @param string префикс добавляемый к имени сохраняемого файла, 
     *               либо полное имя файла в случае если $bAddFilename = false
     * @param boolean использовать ли оригинальное имя файла
     * @return boolean 
	 */
	function save($path, $sFilenamePrefix='', $bAddFilename = true, $chmod = 0777)
	{
        if($this->isError()) return false;
        
		if(!is_dir($path)) {
            return $this->error('path should be a folder:'.$path);
		}
        
		$path = rtrim($path, '\\/');
        $filename = $sFilenamePrefix.($bAddFilename ? $this->filename : '' );

        $res = $this->_save($path.'/'.$filename, $chmod);

        $this->filename_saved = ( $res === true ? $filename : false );
        
        return $res;
	}
	    	
	/**
	 * Сохраняем загруженный файл по указанному пути
	 * @param string путь для сохранения файла
	 * @return boolean
	 */
	private function _save($sFilePath, $chmod = 0777)
	{
		if($this->isError()) return false; 

        if(!move_uploaded_file( $this->getFilenameUploaded() , $sFilePath) )
        {
            return $this->error('cant_write_file', $sFilePath);
        }
        else {
            if($chmod!==false) {
                @chmod($sFilePath, $chmod);
            }
            return true;
        }
	}
	
	function error( $errno, $extra = '' )
	{   
        $errorMsg = '';
        switch( $errno )
        {
            case 'no_file':         $errorMsg = 'Please upload file';     break;
            case 'upload_img':      $errorMsg = 'Please upload image file';  break;
            case 'size_0':          $errorMsg = 'The size of uploaded file should be more then "0"'; break;
            case 'size_max':        $errorMsg = 'The size of uploaded file should be less then: '.$extra;  break;
            case 'cant_write_file': $errorMsg = 'Cant write file: '.$extra; break;            
            case 'no_thumbnail.class':    $errorMsg = 'Thumbnail class is required'; break;
            case 'upload_audio_or_video': $errorMsg = 'Please upload audio or video files'; break;    
        } 

        $this->errors[] = $errorMsg;
		Errors::i()->set( $errorMsg );
        return false;
	}

    function isSuccessfull()
    {
        return !$this->isError();
    }
    
    function isError()
    {
        return (!empty($this->errors));
    }
	
	function getErrors()
	{
		return $this->errors;
	}

    /** 
     * Начало загрузки файла через swfuploader
     * проверка размера файла, ...
     * @param array разрешенные типы файлов
     */
    static function swfuploadStart($bReturnErrors = false, $extension_whitelist = array('jpg', 'jpeg', 'gif', 'png') ) 
    {
        // Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
        $POST_MAX_SIZE = ini_get('post_max_size');
        $unit = mb_strtoupper(substr($POST_MAX_SIZE, -1));
        $multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

        if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
            return self::swfuploadError('POST exceeded maximum allowed size.', $bReturnErrors);
        }
        
        // Settings
        $upload_name = 'Filedata';
        $max_file_size_in_bytes = 2147483647;                       // 2GB in bytes              
        $valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';    // Characters allowed in the file name (in a Regular Expression format)
        
        // Other variables    
        $MAX_FILENAME_LENGTH = 260;
        $file_name = $file_extension = '';
        $uploadErrors = array(
            0=>'There is no error, the file uploaded with success',
            1=>'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2=>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3=>'The uploaded file was only partially uploaded',
            4=>'No file was uploaded',
            6=>'Missing a temporary folder'
        );
        
        // Validate the upload
        if (!isset($_FILES[$upload_name])) {
            return self::swfuploadError('No upload found for ' . $upload_name, $bReturnErrors);
        } else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
            return self::swfuploadError($uploadErrors[$_FILES[$upload_name]["error"]], $bReturnErrors);
        } else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
            return self::swfuploadError('Upload failed is_uploaded_file test.', $bReturnErrors);
        } else if (!isset($_FILES[$upload_name]['name'])) {
            return self::swfuploadError('File has no name.', $bReturnErrors);
        }

        // Validate the file size (Warning: the largest files supported by this code is 2GB)
        $file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
        if (!$file_size || $file_size > $max_file_size_in_bytes) {
            return self::swfuploadError('File exceeds the maximum allowed size', $bReturnErrors);
        }
        
        if ($file_size <= 0) 
        {
            return self::swfuploadError('File size outside allowed lower bound', $bReturnErrors);
        }    

        // Validate file name (for our purposes we'll just remove invalid characters)
        $file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
        if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
            return self::swfuploadError('Invalid file name', $bReturnErrors);
        }

        // Validate file extension
        $path_info = pathinfo($_FILES[$upload_name]['name']);
        $file_extension = $path_info['extension'];
        if(!empty($extension_whitelist)) {
            $is_valid_extension = false;
            foreach ($extension_whitelist as $extension) {
                if (strcasecmp($file_extension, $extension) == 0) {
                    $is_valid_extension = true;
                    break;
                }
            }
            if (!$is_valid_extension) {
                return self::swfuploadError('Invalid file extension', $bReturnErrors);
            }
        }
        
        return array('ext'=>$file_extension, 
                     'size'=>$file_size, 
                     'tmp_name'=>$_FILES[$upload_name]['tmp_name'], 
                     'field_name'=>$upload_name);
    }

    static function swfuploadFinish($sFilename)
    {
        echo 'FILEID:'.$sFilename;
        exit(0);
    }
    
    private static function swfuploadError( $sMessage, $bReturnErrors = false ) 
    {
        if(!$bReturnErrors ) {
            header("HTTP/1.1 500 Internal Server Error");
            echo 'Error: '.$sMessage;
            exit(0);
        }  else {
            return $sMessage;
        }
    }
    
}
