<?php

class CAttachment 
{
    var $input   = 'attach';
    var $maxsize = 0;
    var $limit   = 1;
    var $path    = '';  

    private $extensionsForbidden = array(
        'php','php2','php3','php4','php5','phtml','pwml','inc','asp','aspx','ascx','jsp','cfm','cfc',
        'pl','py','rb','bat','exe','com','cmd','dll','vbs','vbe','js','jse','reg','cgi','wsf','wsh',
    ); 

    /* 
    * возможно допустимые расширения: 
    * jpg,jpeg,gif,png,bmp,tiff,ico 
    * doc,docx,xls,rtf,pdf,djvu
    * zip,gzip,gz,7z,rar,
    * txt,sql,
    */
    
    function __construct($sInputName = 'attach', $sPath = '', $nMaxsize = 0)
    {
        $this->input   = $sInputName;
        $this->maxsize = $nMaxsize; 
        $this->path    = $sPath;
    }
    
    static function dirSize($path)
    {
        $size = 0;
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file){
            $size += $file->getSize();
        }
        return $size;

        //$result=explode("\t",exec("du -hs ".$path),2);
        //return ($result[1]==$path ? $result[0] : "error"); 
    }
    
    function isAllowedExtension($sFileExtension='')
    {
        return !in_array($sFileExtension, $this->extensionsForbidden);
    }
    
    function upload()
    {                             
        #Загружались ли файлы
        if(empty($_FILES))
            return false;

        #Достаточно ли свободного места на диске
        if($nDiskFreeSpace = @disk_free_space($this->path)) {
            if ($nDiskFreeSpace <= 524288000) //на диске <= 500 мб
            {
                trigger_error('attach_quota_reached');
                return false;
            }
        }

        $aAttachments = array();
            
        $i = 1;
        $aFiles = array_reverse($_FILES);
        foreach($aFiles as $sFileKey=>$aFileParams)      
        {
            if(strpos($sFileKey, $this->input)===FALSE || $aFileParams['error'] == 4) /* файл не был загружен */
                continue;
            
            $aAttachments[$i] = array(
                'error'     => 0,
                'filesize'  => @filesize($aFileParams['tmp_name']),
                'rfilename' => $aFileParams['name'],
                'extension' => mb_strtolower(pathinfo($aFileParams['name'], PATHINFO_EXTENSION))
            );
            
            if(!$aAttachments[$i]['filesize'])
                $aAttachments[$i]['filesize'] = $aFileParams['size'];
 
            #Не указана ли ошибка загрузки
            if($aFileParams['error'] != 0) {   
                switch($aFileParams['error'])
                {
                    case 1:  //The uploaded file exceeds the upload_max_filesize directive in php.ini. 
                    case 2:{ //The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
                        $aAttachments[$i]['error'] = BFF_UPLOADERROR_MAXSIZE;
                    } break;
                    default: { 
                        //3: The uploaded file was only partially uploaded. 
                        //4: No file was uploaded. 
                        //6: Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3. 
                        //7: Failed to write file to disk. Introduced in PHP 5.1.0. 
                        $aAttachments[$i]['error'] = -$aFileParams['error'];
                    }break;
                }
                continue;
            }
                
            #Загружен ли файл?
            if(!is_uploaded_file($aFileParams['tmp_name'])) {
                  $aAttachments[$i]['error'] = BFF_UPLOADERROR_UPLOADERR;
                  continue;
            } 

            #Проверка имени файла
            if(preg_match("#[\\/:;*?\"<>|]#i", $aFileParams['name'])) {
                $aAttachments[$i]['error'] = BFF_UPLOADERROR_WRONGNAME;
                continue;
            }
 
            #Проверка размера файла                                                                                           
            if($aAttachments[$i]['filesize']<=0) {
                $aAttachments[$i]['error'] = BFF_UPLOADERROR_WRONGSIZE;
                continue;
            }
            if(!empty($this->maxsize) && $aAttachments[$i]['filesize'] > $this->maxsize) {
                $aAttachments[$i]['error'] = BFF_UPLOADERROR_MAXSIZE;
                continue;
            }            
            
            #Проверка типа файла по раширению
            if(!$this->isAllowedExtension( $aAttachments[$i]['extension'] )) {
                $aAttachments[$i]['error'] = BFF_UPLOADERROR_WRONGTYPE;
                continue;
            }

            #Проверка свободного места на диске
            if ($nDiskFreeSpace <= $aAttachments[$i]['filesize'])
            {
                $aAttachments[$i]['error'] = BFF_UPLOADERROR_DISKQUOTA;
                continue;
            }     
            
            #Генерация имени файла            
            if($aAttachments[$i]['extension'] == 'jpeg')
                $aAttachments[$i]['extension'] = 'jpg';
                
            $sFilename = (func::generateRandomName(10, false, true)).'.'.$aAttachments[$i]['extension'];
            $sFilepath = $this->path.$sFilename;
            
            #Сохранение
            if(!move_uploaded_file($aFileParams['tmp_name'], $sFilepath)) {
                $aAttachments[$i]['error'] = BFF_UPLOADERROR_UPLOADERR;
                continue;
            }
                                            
            $aAttachments[$i]['filename']  = $sFilename;  
                        
            @chmod($sFilepath, 0666);
             
            if(++$i > $this->limit)
                break; 
        }
                 
        if($this->limit == 1){
            $attach = current($aAttachments);
            return ($attach['error'] === 0 ? $attach['filename'].';'.$attach['filesize'].';'.$attach['extension'] : '');
        }
        
        return $aAttachments;
    }
    
   /** 
    * удаление забытых вложений :)
    * @param string 
    */
    function _clearlost($aAttachments = array())
    {                
        $i = 0;
        clearstatcache();
        
        $filesALL = CDir::getFiles($this->path, false, false);
        foreach($filesALL as $file)
        {
            list($file,$size,$ext) = explode(';', $file);
            
            if(!in_array($file, $aAttachments)) {
                $i++;
                @unlink($this->path.$file);
            }
        }
        
        return $i;
    }
    
}  
