<?php
/**
 * Dirs and files
 *
 * @version 2007-03-14
 */

class CDir{

	/**
	 * Recursive get filest into dir and subdirs
	 *
	 * @param string $directory
	 * @return array
	 * 
	 * // Example of use
	 *	print_r(getFiles('.'));
	 * 
	 */
	static function getFiles($directory, $base='', $bRecursive = true,  $bReturnFullPath = true,  $bReturnAll= false, $fileTypes = array(), $exclude = array()) {


		if(is_file($directory)) return false;
		if(!is_dir($directory)) return false;

		$directory = rtrim($directory, '\\/');

		if(!is_dir($directory)) return false;
		// Try to open the directory
		if($dir = opendir($directory)) {
			// Create an array for all files found
			$tmp = Array();
			// Add the files
			while($file = readdir($dir)) {
                
                if($file==='.' || $file==='..')
                    continue;                

                $isFile = is_file($directory.'/'.$file);
                if(!self::validatePath($base, $file, $isFile, $fileTypes, $exclude))
                   continue;
  
				if($isFile)
				{
                    if($bReturnAll)
                    {
                        $arr_item = array('item'=>$file, 'fullitem'=>$directory .'/'. $file, 'isFile'=>true);
                        array_push($tmp, $arr_item);
                    }
                    else if ($bReturnFullPath)
                        array_push($tmp, $directory.'/'.$file);
                    else
                        array_push($tmp, $file);
				} else { // If it's a directory, list all files within it   
                    if($bRecursive)
                    {
                        $tmp2 = self::getFiles($directory.'/'.$file, $base.'/'.$file, $bRecursive, $bReturnFullPath, $bReturnAll, $fileTypes, $exclude);
                        if(!empty($tmp2)) {
                            $tmp = array_merge($tmp, $tmp2);
                        }
                    }					
				}  
			}
			// Finish off the function
			closedir($dir);
			return $tmp;
		}
        
		return array();
	}

    /**
     * Validates a file or directory.
     * @param string the path relative to the original source directory
     * @param string the file or directory name
     * @param boolean whether this is a file
     * @param array list of file name suffix (without dot). Only files with these suffixes will be copied.
     * @param array list of directory and file exclusions. Each exclusion can be either a name or a path.
     * If a file or directory name or path matches the exclusion, it will not be copied. For example, an exclusion of
     * '.svn' will exclude all files and directories whose name is '.svn'. And an exclusion of '/a/b' will exclude
     * file or directory '$src/a/b'. Note, that '/' should be used as separator regardless of the value of the DIRECTORY_SEPARATOR constant.
     * @return boolean whether the file or directory is valid
     */
    protected static function validatePath($base, $file, $isFile, $fileTypes, $exclude)
    {
        foreach($exclude as $e)
        {
            if($file===$e || strpos($base.'/'.$file,$e)===0)
                return false;
        }
        if(!$isFile || empty($fileTypes))
            return true;
        if(($pos=strrpos($file,'.'))!==false)
        {
            $type=substr($file,$pos+1);
            return in_array($type, $fileTypes);
        }
        else
            return false;
    }
    
	static function getDirs($sDir, $bRecursive = false, $bWithDots = false, $bReturnFullPath = true)
	{
		$sDir = rtrim($sDir, '\\/');
		$sDir = $sDir.'/';

		$arr_res = array();
		if (is_dir($sDir))
		{
			if ($dh = opendir($sDir))
			{
				while (($file = readdir($dh)) !== false)
				{
					//echo "filename: $file : filetype: " . filetype($sDir . $file) . "\n";
					if(!$bWithDots && ($file=='.' || $file=='..' || $file=='.svn')) continue;
					if(is_dir($sDir . $file) )
					{
						//$arr_res[] = $file;
						if($bReturnFullPath)
						array_push($arr_res, $sDir . $file.'/');
						else
						array_push($arr_res, $file);
						if($bRecursive)
						{
							$tmp2 = self::getDirs( $sDir . $file.'/', $bRecursive, $bWithDots);
							if(is_array($tmp2))
							$arr_res = array_merge($arr_res, $tmp2);

						}
					}
				}
				closedir($dh);
				return $arr_res;
			}
		}
		return false;

	}

	static function getInDir($sDir, $bRecursive = false, $bWithDots = false , $bReturnAll= true, $bReturnFullPath = true )
	{
		$sDir = rtrim($sDir, '\\/');
		$sDir = $sDir.'/';


		$arr_res = array();
		if (is_dir($sDir))
		{
			if ($dh = opendir($sDir))
			{
				while (($file = readdir($dh)) !== false)
				{
					//echo "filename: $file : filetype: " . filetype($sDir . $file) . "\n";
					if(!$bWithDots && ($file=='.' || $file=='..')) continue;
					if(is_dir($sDir . $file) )
					{

						//$arr_res[] = $file;
						if($bReturnAll)
						{
							$arr_item = array('item'=>$file, 'fullitem'=>$sDir . $file.'/', 'isFile'=>false);
							array_push($arr_res, $arr_item);
						}
						else if($bReturnFullPath)
						array_push($arr_res, $sDir . $file.'/');
						else
						array_push($arr_res,  $file);

						if($bRecursive)
						{
							$tmp2 = self::getInDir( $sDir . $file.'/', $bRecursive, $bWithDots, $bReturnAll, $bReturnFullPath);
							if(is_array($tmp2))
							$arr_res = array_merge($arr_res, $tmp2);

						}
					}
					else
					{
						if($bReturnAll)
						{
							$arr_item = array('item'=>$file, 'fullitem'=>$sDir . $file, 'isFile'=>true);
							array_push($arr_res, $arr_item);
						}
						else if($bReturnFullPath)
						array_push($arr_res, $sDir . $file);
						else
						array_push($arr_res, $file);

					}


				}
				closedir($dh);

				return $arr_res;
			}
		}
        
		return false;
	}

	static function getFileContent($s_path2file)
	{
		if($fp=@fopen($s_path2file,'r'))
		{
			$file_content = fread($fp, @filesize($s_path2file));
			fclose($fp);
			return $file_content;
		}
		return false;
	}

	static function putFileContent($filename, $content, $mode = 'w', $chmod = 0666)
	{
        if($fp = @fopen($filename, $mode))
		{
			fwrite($fp, $content);
			fclose($fp);
			@chmod($filename, $chmod);
			return true;
		}
		return false;
	}

	static function serachByMD5($md5path_or_filedirname, $path, $bSearchByFileDirName = false, $bRecursive = false, $bReturnAll = true,  $bReturnFullPath = true)
	{

		$arr_all = self::getInDir($path, $bRecursive, false, true);
		for( $i=0; $i < count($arr_all); $i++)
		{
			if($bSearchByFileDirName)
			{
				if(md5($arr_all[$i]['item']) == $md5path_or_filedirname)
				{
					if($bReturnAll)
					return $arr_all[$i];

					if($bReturnFullPath)
					return $arr_all[$i]['fullitem'];
					else
					return $arr_all[$i]['item'];
				}
			}
			else
			{
				if(md5($arr_all[$i]['fullitem']) == $md5path_or_filedirname)
				{
					if($bReturnAll)
					return $arr_all[$i];

					if($bReturnFullPath)
					return $arr_all[$i]['fullitem'];
					else
					return $arr_all[$i]['item'];
				}
			}
		}
		return false;
	}

	static function isFileInDirectory($fileName, $dir)
	{
		return in_array( $fileName, self::getFiles($dir) );
	}

	static function haveWriteAccess($s_path2file)
	{
		if(is_writable($s_path2file))
		{
			return true;
		}
		return false;
	}
    
    /**
     * Чистим имя файла от запрещенных символов
     * @param  string имя файла
     * @return string относительный путь
     */
    static function cleanFilename($str, $relative_path = FALSE)
    {
        $bad = array( "../",
                      "<!--",
                      "-->",
                      "<",
                      ">",
                      "'",
                      '"',
                      '&',
                      '$',
                      '#',
                      '{',
                      '}',
                      '[',
                      ']',
                      '=',
                      ';',
                      '?',
                      "%20",
                      "%22",
                      "%3c",      // <
                      "%253c",    // <
                      "%3e",      // >
                      "%0e",      // >
                      "%28",      // (
                      "%29",      // )
                      "%2528",    // (
                      "%26",      // &
                      "%24",      // $
                      "%3f",      // ?
                      "%3b",      // ;
                      "%3d"       // =
                    );

        if(!$relative_path) {
            $bad[] = './';
            $bad[] = '/';
        }
        
        return stripslashes(str_replace($bad, '', $str));
    }
}
