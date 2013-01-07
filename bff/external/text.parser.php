<?php

require_once (PATH_CORE.'external/jevix/jevix.class.php');

/**
 * Класс обработки текста на основе типографа Jevix
 */
class CTextParser {

	protected $oJevix;		

	public function __construct() {	
		$this->oJevix = new Jevix();	
	} 
    
    /** @example: $errors = &CTextParser::singleton(); */
    static function &instanse()
    {     
        static $object;
        if(!isset($object)) 
            $object = new CTextParser();                                       
     
        return $object;
    }
	
	/**
	 * Парсинг текста с помощью Jevix
	 * @param string $sText
	 * @param array $aError
	 * @return string
	 */
	public function JevixParser($sText,&$aError=null) {		
		$sResult=$this->oJevix->parse($sText,$aError);
		return $sResult;
	}
	
	/**
	 * Парсинг текста на предмет видео
	 * @param string $sText
	 * @return string
	 */
	public function VideoParser($sText) {	
		//youtube.com
		$sText = preg_replace('/<video>http:\/\/(?:www\.|)youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)<\/video>/Ui', '<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/$1&hl=en"></param><param name="wmode" value="opaque"></param><embed src="http://www.youtube.com/v/$1&hl=en" type="application/x-shockwave-flash" wmode="opaque" width="425" height="344"></embed></object>', $sText);		
		//rutube.ru
		$sText = preg_replace('/<video>http:\/\/(?:www\.|)rutube.ru\/tracks\/\d+.html\?v=([a-zA-Z0-9_\-]+)<\/video>/Ui', '<OBJECT width="470" height="353"><PARAM name="movie" value="http://video.rutube.ru/$1"></PARAM><PARAM name="wmode" value="opaque"></PARAM><PARAM name="allowFullScreen" value="true"></PARAM><PARAM name="flashVars" value="uid=662118"></PARAM><EMBED src="http://video.rutube.ru/$1" type="application/x-shockwave-flash" wmode="opaque" width="470" height="353" allowFullScreen="true" flashVars="uid=662118"></EMBED></OBJECT>', $sText);				
		return $sText;
	}

	/**
	 * Заменяет все вхождения короткого тега <param/> на длиную версию <param></param>
	 * Заменяет все вхождения короткого тега <embed/> на длиную версию <embed></embed>
	 */
	protected function FlashParamParser($sText) {	
		if (preg_match_all("@(<\s*param\s*name\s*=\s*\".*\"\s*value\s*=\s*\".*\")\s*/?\s*>(?!</param>)@Ui",$sText,$aMatch)) {				
			foreach ($aMatch[1] as $key => $str) {
				$str_new=$str.'></param>';				
				$sText=str_replace($aMatch[0][$key],$str_new,$sText);				
			}	
		}
		if (preg_match_all("@(<\s*embed\s*.*)\s*/?\s*>(?!</embed>)@Ui",$sText,$aMatch)) {				
			foreach ($aMatch[1] as $key => $str) {
				$str_new=$str.'></embed>';				
				$sText=str_replace($aMatch[0][$key],$str_new,$sText);				
			}	
		}	
		/**
		 * Удаляем все <param name="wmode" value="*"></param>		 
		 */
		if (preg_match_all("@(<param\s.*name=\"wmode\".*>\s*</param>)@Ui",$sText,$aMatch)) {
			foreach ($aMatch[1] as $key => $str) {
				$sText=str_replace($aMatch[0][$key],'',$sText);
			}
		}
		/**
		 * А теперь после <object> добавляем <param name="wmode" value="opaque"></param>
		 * Решение не фантан, но главное работает :)
		 */
		if (preg_match_all("@(<object\s.*>)@Ui",$sText,$aMatch)) {
			foreach ($aMatch[1] as $key => $str) {
				$sText=str_replace($aMatch[0][$key],$aMatch[0][$key].'<param name="wmode" value="opaque"></param>',$sText);
			}
		}
		
		return $sText;
	}

	/**
	 * Делает ссылки не видимыми для поисковиков
	 * @param unknown_type $sText
	 * @return unknown
	 */
	public function MakeUrlNoIndex($sText) {
		return preg_replace("/(<a .*>.*<\/a>)/Ui","<noindex>$1</noindex>",$sText);
	}
    
   /**
     * Парсит текст комментария к работе
     * @param string $sText
     */
    public function parseItemComment($sMessage) 
    {
        $sMessage = preg_replace ("/(\<script)(.*?)(script>)/si", '', $sMessage);
        $sMessage = htmlspecialchars($sMessage);
        $sMessage = preg_replace ("/(\<)(.*?)(--\>)/mi", ''.nl2br("\\2").'', $sMessage);
        return Func::hrefActivate( nl2br($sMessage) );    

    }
    
    /**
     * Парсит текст комментария
     * @param string $sText
     */
    public function parseComments($sText) 
    {
        #Конфигурирует типограф
        
        # Разрешённые теги
        $this->oJevix->cfgAllowTags(array('cut','a', 'img', 'i', 'b', 'u', 's', 'video', 'em',  'strong', 'nobr', 'li', 'ol', 'ul', 'sup', 'abbr', 'sub', 'acronym', 'h4', 'h5', 'h6', 'br', 'hr', 'pre', 'code', 'object', 'param', 'embed', 'blockquote'));
        # Коротие теги типа
        $this->oJevix->cfgSetTagShort(array('br','img', 'hr', 'cut'));
        # Преформатированные теги
        $this->oJevix->cfgSetTagPreformatted(array('pre','code','video'));
        # Разрешённые параметры тегов        
        $this->oJevix->cfgAllowTagParams('img', array('src', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int', 'hspace' => '#int', 'vspace' => '#int'));
        $this->oJevix->cfgAllowTagParams('a', array('href',  'title', 'rel'));        
        $this->oJevix->cfgAllowTagParams('cut', array('name'));
        $this->oJevix->cfgAllowTagParams('object', array('width' => '#int', 'height' => '#int', 'data' => '#link'));
        $this->oJevix->cfgAllowTagParams('param', array('name' => '#text', 'value' => '#text'));
        $this->oJevix->cfgAllowTagParams('embed', array('src' => '#image', 'type' => '#text','allowscriptaccess' => '#text', 'allowfullscreen' => '#text','width' => '#int', 'height' => '#int', 'flashvars'=> '#text', 'wmode'=> '#text'));
        # Параметры тегов являющиеся обязательными
        $this->oJevix->cfgSetTagParamsRequired('img', 'src');
        $this->oJevix->cfgSetTagParamsRequired('a', 'href');
        # Теги которые необходимо вырезать из текста вместе с контентом
        $this->oJevix->cfgSetTagCutWithContent(array('script', 'iframe', 'style'));
        # Вложенные теги
        $this->oJevix->cfgSetTagChilds('ul', array('li'), false, true);
        $this->oJevix->cfgSetTagChilds('ol', array('li'), false, true);
        $this->oJevix->cfgSetTagChilds('object', 'param', false, true);
        $this->oJevix->cfgSetTagChilds('object', 'embed', false, false);
        # Если нужно оставлять пустые не короткие теги
        $this->oJevix->cfgSetTagIsEmpty(array('param','embed'));
        # Теги с обязательными параметрами
        $this->oJevix->cfgSetTagParamsAutoAdd('embed',array(array('name'=>'wmode','value'=>'opaque','rewrite'=>true)));
        # Отключение авто-добавления <br>
        $this->oJevix->cfgSetAutoBrMode(false);
        # Автозамена
        $this->oJevix->cfgSetAutoReplace(array('+/-', '(c)', '(r)', '(C)', '(R)'), array('±', '©', '®', '©', '®'));
        $this->oJevix->cfgSetXHTMLMode(false);
        $this->oJevix->cfgSetTagNoTypography('code');
        $this->oJevix->cfgSetTagNoTypography('video');

        $sResult = $this->FlashParamParser($sText);        
        $sResult = $this->JevixParser($sResult);    
        $sResult = $this->VideoParser($sResult);        
        return $sResult;
    }
    
    /**
     * Проверка текста описания работы
     * @param string $sText
     */
    public function parseItemDescription($sText) 
    {
        #Конфигурирует типограф
        
        # Разрешённые теги
        $this->oJevix->cfgAllowTags(array('a', 'i', 'b', 'u', 'span', 'img', 'div', 'em',  'strong', 'nobr', 'li', 'ol', 'ul', 'sup', 'sub', 
                                          'abbr', 'acronym', 'h4', 'h5', 'h6', 'br', 'hr', 'pre', 'object', 'param', 'embed', 'blockquote'));
        # Коротие теги типа
        $this->oJevix->cfgSetTagShort(array('br','img', 'hr'));
        # Преформатированные теги
        $this->oJevix->cfgSetTagPreformatted(array('pre'));
        # Разрешённые параметры тегов        
        $this->oJevix->cfgAllowTagParams('img', array('src', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int', 'hspace' => '#int', 'vspace' => '#int'));
        $this->oJevix->cfgAllowTagParams('a', array('href',  'title', 'rel'));
        $this->oJevix->cfgAllowTagParams('span', array('style', 'align', 'class'));  
        $this->oJevix->cfgAllowTagParams('div', array('style', 'align', 'class'));
        $this->oJevix->cfgAllowTagParams('object', array('width' => '#int', 'height' => '#int', 'data' => '#link'));
        $this->oJevix->cfgAllowTagParams('param', array('name' => '#text', 'value' => '#text'));
        $this->oJevix->cfgAllowTagParams('embed', array('src' => '#image', 'type' => '#text','allowscriptaccess' => '#text', 'allowfullscreen' => '#text','width' => '#int', 'height' => '#int', 'flashvars'=> '#text', 'wmode'=> '#text'));
        # Параметры тегов являющиеся обязательными
        $this->oJevix->cfgSetTagParamsRequired('img', 'src');
        $this->oJevix->cfgSetTagParamsRequired('a', 'href');
        # Теги которые необходимо вырезать из текста вместе с контентом
        $this->oJevix->cfgSetTagCutWithContent(array('script', 'iframe', 'style'));
        # Вложенные теги
        $this->oJevix->cfgSetTagChilds('ul', array('li'), false, true);
        $this->oJevix->cfgSetTagChilds('ol', array('li'), false, true);
        $this->oJevix->cfgSetTagChilds('object', 'param', false, true);
        $this->oJevix->cfgSetTagChilds('object', 'embed', false, false);
        # Если нужно оставлять пустые не короткие теги
        $this->oJevix->cfgSetTagIsEmpty(array('param','embed','span'));
        # Теги с обязательными параметрами
        $this->oJevix->cfgSetTagParamsAutoAdd('embed',array(array('name'=>'wmode','value'=>'opaque','rewrite'=>true)));
        # Отключение авто-добавления <br>
        $this->oJevix->cfgSetAutoBrMode(false);
        # Автозамена
        $this->oJevix->cfgSetAutoReplace(array('+/-', '(c)', '(r)', '(C)', '(R)'), array('±', '©', '®', '©', '®'));
        $this->oJevix->cfgSetXHTMLMode(false);  

        $sResult = $this->FlashParamParser($sText);        
        $sResult = $this->JevixParser($sResult);    
        return $this->VideoParser($sResult);
    }
    
}