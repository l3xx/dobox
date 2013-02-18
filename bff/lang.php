<?php

$oLang = new CLang();

/**
 * @version: 0.6
 */
class CLang
{                                      
	protected $aLanguages      = array();
    private $currentLanguage = '';    
	private $defaultLanguage = false;
    public  $detected        = false;
        
	function __construct($def = LANG_DEFAULT)
	{                                                       
        $this->defaultLanguage = $def;
	}
	
	public function assignLang($sKeyword, $sTitle='Default', $sCharset='utf-8')
	{
		$this->aLanguages[$sKeyword] = array('title'=>$sTitle, 'charset'=>$sCharset);
        return $this;
	}
	
	public function detectLang()
	{       
		$lng = (!empty($_GET[LANG_VAR]) ? $_GET[LANG_VAR] : (!empty($_POST[LANG_VAR]) ? $_POST[LANG_VAR] : false) );
		if($lng) { //инициировали смену языка
            func::setCOOKIE(LANG_VAR, $lng);              
        }
        else {
           $lng = isset($_COOKIE[LANG_VAR]) ? $_COOKIE[LANG_VAR] : false;
        } 
        
        if(!$lng) {
            $lng = $this->defaultLanguage;
            func::setCOOKIE(LANG_VAR, $lng);
        }
            
		$this->currentLanguage = $lng;
		$this->detected = true;
		return $lng;
	}
	
	public function curLang()
	{
		if(!$this->detected) 
            $this->detectLang();
                                                            
		return $this->currentLanguage;
	}
    
	static function getLanguages()
	{
        global $oLang;
		return $oLang->aLanguages;	
	}
    
	public function getDefaultLang()
	{
		return $this->defaultLanguage;
	}
}
