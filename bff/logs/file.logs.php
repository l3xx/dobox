<?php

/**
 * CFileLogger records log messages in files.
 * The log files are stored under {@link setLogPath logPath} and the file name
 * is specified by {@link setLogFile logFile}. If the size of the log file is
 * greater than {@link setMaxFileSize maxFileSize} (in kilobytes), a rotation
 * is performed, which renames the current log file by suffixing the file name
 * with '.1'. All existing log files are moved backwards one place, i.e., '.2'
 * to '.3', '.1' to '.2'. The property {@link setMaxLogFiles maxLogFiles}
 * specifies how many files to be kept.
 */
 
class CFileLogger
{
	// maximum log file size
	private $_maxFileSize = 1024; // in KB
    
	// number of log files used for rotation
	private $_maxLogFiles = 2;
    
	//directory storing log files
	private $_logPath;
    
	//log file name
	private $_logFile = 'app.log';


	/**
	 * Initializes the route.
	 * This method is invoked after the route is created by the route manager.
	 */
	public function __construct($sPath, $sFilename, $nMaxFileSize = 1024, $nMaxLogFiles = 2)
	{                    
		if($this->getLogPath() === null)
			$this->setLogPath($sPath);
        
        $this->setLogFile($sFilename);
        $this->setMaxFileSize($nMaxFileSize);
        $this->setMaxLogFiles($nMaxLogFiles);
	}

	/**
	 * @return string directory storing log files. Defaults to application runtime path.
	 */
	public function getLogPath()
	{
		return $this->_logPath;
	}

	/**
	 * @param string directory for storing log files.
	 * @throws CException if the path is invalid
	 */
	public function setLogPath($value)
	{
		$this->_logPath = realpath($value);
		if($this->_logPath===false || !is_dir($this->_logPath) || !is_writable($this->_logPath))
            trigger_error('CFileLogJSRoute.logPath "'.$value.'" does not point to a valid directory. Make sure the directory exists and is writable by the Web server process.');
	}

	/**
	 * @return string log file name. Defaults to 'application.log'.
	 */
	public function getLogFile()
	{
		return $this->_logFile;
	}

	/**
	 * @param string log file name
	 */
	public function setLogFile($value)
	{
		$this->_logFile=$value;
	}

	/**
	 * @return integer maximum log file size in kilobytes (KB). Defaults to 1024 (1MB).
	 */
	public function getMaxFileSize()
	{
		return $this->_maxFileSize;
	}

	/**
	 * @param integer maximum log file size in kilobytes (KB).
	 */
	public function setMaxFileSize($value)
	{
		if(($this->_maxFileSize=(int)$value)<1)
			$this->_maxFileSize=1;
	}

	/**
	 * @return integer number of files used for rotation. Defaults to 5.
	 */
	public function getMaxLogFiles()
	{
		return $this->_maxLogFiles;
	}

	/**
	 * @param integer number of files used for rotation.
	 */
	public function setMaxLogFiles($value)
	{
		if(($this->_maxLogFiles=(int)$value)<1)
			$this->_maxLogFiles = 1;
	}
    
    /**
     * Logs a message.
     * Messages logged by this method may be retrieved back via {@link getLogs}.
     * @param string message to be logged
     * @param string level of the message (e.g. 'Trace', 'Warning', 'Error'). It is case-insensitive.
     * @param string category of the message (e.g. 'system.web'). It is case-insensitive.
     */
    public function log($message, $level='info', $category='*')
    {
        $logFile = $this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
        if(@filesize($logFile) > $this->getMaxFileSize()*1024)
            $this->rotateFiles();

        error_log( @gmdate('Y/m/d H:i:s',microtime(true))." $message\n", 3, $logFile );
    }

	/**
	 * Rotates log files.
	 */
	protected function rotateFiles()
	{
		$file = $this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
		$max  = $this->getMaxLogFiles();
		for($i=$max;$i>0;--$i)
		{
			$rotateFile = $file.'.'.$i;
			if(is_file($rotateFile))
			{
				if($i===$max)
					@unlink($rotateFile);
				else
					@rename($rotateFile,$file.'.'.($i+1));
			}
		}
		if(is_file($file))
			rename($file,$file.'.1');
	}
}
