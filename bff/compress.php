<?php

/**
 * Compression not work after first request to server with mod_rewrite (url-rewrite) :(
 * & i don`t know how its work with settings in php.ini ob_header =  zlib.commpression...
 * 
 * @final  2006-03-22
 * @version 2.2
 * @author hobobobo
 * @example 
 * 
 * $oCompress = &new Compress(); 
 * $oCompress->BeginCompression();
 * echo $content 
 * $oCompress->EndCompression();
 * 
 */

class Compress
{
	/**
	 * settings: Show bottom compress persent
	 *
	 * @var bool 
	 */
	var $bShowCompress = true;
	var $bLog = true;
	
	/**
	 * internal flag
	 */
	var $bCompress = false;
	var $b_ob_gzhandler = false;
	var $bWorking = true;
	
	
	var $error ='';
	
	
	function Compress($bShowCompressInfo = null, $bLog = false)
	{
		$this->bShowCompress = $bShowCompressInfo;
		$this->bLog = $bLog;
		
		if($bShowCompressInfo) $this->bShowCompress = $bShowCompressInfo;
		
		if(headers_sent())
		{
			$this->error = 'Header already is sent. Compression is failed';	
			$this->bWorking = false;
		}
	}
	
	
	function Log()
	{
		function myfunc($s,$n)
		{
		  	global   $full_length ;
			$gzip_size = $full_length ;
		  	$compressed_size = strlen($s);			
			$persent = $compressed_size/$gzip_size*100;
			$str = 'compresssed: '.sprintf("%.2f", $gzip_size/1024).'kb = '.sprintf("%.2f",$persent).'% - '.sprintf("%.2f", $compressed_size/1024).'kb';
		  	error_log($str);
		  	
		  return $s;
		}
		
		
		if($this->bLog)
		{
			ob_start('myfunc');
		}
	}
	/**
	 * Run before output content
	 *
	 */
	function BeginCompression()
	{
		if(!$this->bWorking) return ;
		$phpver = phpversion();
		$useragent = (isset($_SERVER["HTTP_USER_AGENT"]) ) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT;
		if ( $phpver >= '4.0.4pl1' && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) )
		{
			if ( extension_loaded('zlib') )
			{
				$this->Log();
				
				if(!ini_get('zlib.output_compression') && @ob_start('ob_gzhandler'))
					$this->b_ob_gzhandler = true;
			}
		}
		else if ( $phpver > '4.0' )
		{
			if ( isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') )
			{
				if ( extension_loaded('zlib') )
				{
					$this->bCompress = TRUE;
					ob_start();
					ob_implicit_flush(0);
					header('Content-Encoding: gzip');
				}
			}
		}
	}
	
	/**
	 * Run after output content
	 *
	 */
	function EndCompression()
	{
		global $oLang;

		if(!$this->bWorking) return ;
		
		if ( $this->bCompress )
		{
			//
			// Borrowed from php.net!
			//
			$content = $gzip_contents = ob_get_contents();
			ob_end_clean();
		
			$gzip_size = strlen($gzip_contents);
			$gzip_crc = crc32($gzip_contents);
		
			$gzip_contents = gzcompress($gzip_contents, 9);
			$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);
			
			if($this->bShowCompress)
			{
				$compressed_size = strlen($gzip_contents);			
				$persent = $compressed_size/$gzip_size*100;
				if( defined('LANG_RUSSIAN') && isset($oLang) && $oLang->getCurLang()==LANG_RUSSIAN)
					$content = str_replace('</div></div></body>', 
                                            '<div class="footer">
                                                        <div class="footer_block">
                                                            <div class="menu_towns">
                                                                <div style="padding-top:7px;" class="alignCenter" id="compressed">Сжатие страницы: исходный размер = '.sprintf("%.2f", $gzip_size/1024).'кб;  процент сжатия =  '.sprintf("%.2f",$persent).'%; в результате размер страницы = '.sprintf("%.2f", $compressed_size/1024).'kb</div>
                                                            </div>
                                                        </div>
                                                    </div><div class="clear"></div>
                                                    </div></div><div class="clear"></div></body>', $content);
				else
					$content = str_replace('</body>', '<div style="background-color:#42709B; font-size:8pt;"><center id="compressed">compresssed: '.sprintf("%.2f", $gzip_size/1024).'kb = '.sprintf("%.2f",$persent).'% - '.sprintf("%.2f", $compressed_size/1024).'kb</center></div><div class="cleaner"></div></body>', $content);
				
				$gzip_size = strlen($content);
				$gzip_crc = crc32($content);
		
				$gzip_contents = gzcompress($content, 9);
				$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);
			}
			
			echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
			echo $gzip_contents;
			echo pack('V', $gzip_crc);
			echo pack('V', $gzip_size);
		}
		
		if($this->b_ob_gzhandler && $this->bShowCompress)
		{
			$content = $gzip_contents = ob_get_contents();
			
			ob_end_clean();			
			ob_start('ob_gzhandler');
			$gzip_size = strlen($gzip_contents);
			$compressed_size = strlen( gzcompress($gzip_contents, 9) );			

			$persent = $compressed_size/$gzip_size*100;
			
			if(defined('LANG_RUSSIAN') && isset($oLang) && $oLang->getCurLang()==LANG_RUSSIAN)
				$content = str_replace('</div></div></body>',
                                                    '
                                                    <div class="footer">
                                                        <div class="footer_block">
                                                            <div class="menu_towns">
                                                                <div style="padding-top:7px;" class="alignCenter" id="compressed">Сжатие страницы: исходный размер = '.sprintf("%.2f", $gzip_size/1024).'кб;  процент сжатия =  '.sprintf("%.2f",$persent).'%; в результате размер страницы = '.sprintf("%.2f", $compressed_size/1024).'кб</div>
                                                            </div>
                                                        </div>
                                                    </div><div class="clear"></div>
                                                    </div></div><div class="clear"></div></body>', $content);
			else
				$content = str_replace('</div></div></body>', '
                                    <div class="footer">
                                        <div class="footer_block">
                                            <div class="menu_towns">
                                                <div style="padding-top:7px;" class="alignCenter" id="compressed">compresssed: '.sprintf("%.2f", $gzip_size/1024).'kb = '.sprintf("%.2f",$persent).'% - '.sprintf("%.2f", $compressed_size/1024).'kb</div>
                                            </div>
                                            <div class="clear"></div>
                                    </div>
                                    </div></div><div class="clear"></div>
                                    </body>', $content);
			echo $content;
		}
		
		if($this->b_ob_gzhandler)
		{
			global  $full_length;
			$full_length = ob_get_length();
		}
		
		exit;
	}
}
?>