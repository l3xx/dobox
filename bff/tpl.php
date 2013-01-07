<?php

class tpl {
    
    static function escape($string, $type='html') {
        switch($type)
        {
            case 'html':
                return htmlspecialchars($string, ENT_QUOTES);  
            case 'javascript':
                // escape quotes and backslashes, newlines, etc.
                return strtr($string, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));            
            default:
                return $string;
        }
    }
    
    static function wysiwyg($sContent, $sFieldName, $nWidth=575, $nHeight=300, $sToolbarMode='average', $sTheme='sd')
    {
        static $oWysiwyg;
        if(!isset($oWysiwyg))
        {
            require_once PATH_CORE.'wysiwyg.php';
            $oWysiwyg = new CWysiwyg();
        }
        return $oWysiwyg->init($sFieldName, $sContent, $nWidth, $nHeight, $sToolbarMode, $sTheme);
    }
    
    static function jwysiwyg($sContent, $sFieldName, $nWidth=575, $nHeight=300, $sType='adm', $sJSObjectName = '')
    {
        static $jsIncluded = false;
        if(!$jsIncluded)
        {
            bff::includeJS('wysiwyg');
            $jsIncluded = true;
        }
     
        $sParams = '';
        switch($sType) {                                                                                                                                          
            case 'adm': { $sParams = '{ uploadFunction: function(){ return "?hash=7653976"; }, controls : { insertImageSimple: {visible: false} } }'; } break;
            default: $sParams = '{}'; break;
        }
        
        if(strpos($sFieldName,',')!==FALSE) {
            list($sFieldID, $sFieldName) = explode(',', $sFieldName);
            if(empty($sFieldName)) $sFieldName = $sFieldID;
        } else {
            $sFieldID = $sFieldName;
        }

        if ( strpos( $nWidth, '%' ) === false )
            $WidthCSS = $nWidth . 'px' ;
        else
            $WidthCSS = $nWidth ;

        if ( strpos( $nHeight, '%' ) === false )
            $HeightCSS = $nHeight . 'px' ;
        else
            $HeightCSS = $nHeight ;
        
        $sHTML = '<textarea name="'.$sFieldName.'" id="'.$sFieldID.'" style="height:'.$HeightCSS.'; width:'.$WidthCSS.';">'.$sContent.'</textarea>
                  <script type="text/javascript"> $(document).ready(function(){  '.(!empty($sJSObjectName)? $sJSObjectName.' = ':'').' $(\'#'.$sFieldID.'\').bffWysiwyg(
                    '.$sParams.', true); }); </script>';
        return $sHTML;
    }
    
    static function publicator($aData, $ID, $sFieldName, $sAction = 'edit', $sJSObjectName = '')
    {
        ob_start(); 
        include (PATH_CORE.'database'.DIRECTORY_SEPARATOR.'publicator'.DIRECTORY_SEPARATOR.'form.php');
        return ob_get_clean();
    }
    
    static function filesize($size, $extendedTitle = false)
    {
        $units = ($extendedTitle ? array('Байт', 'Килобайт', 'Мегабайт', 'Гигабайт', 'Терабайт') : array('б', 'Кб', 'Мб', 'Гб', 'Тб'));
        for ($i = 0; $size > 1024; $i++) { $size /= 1024; }
        return round($size, 2).' '.$units[$i];
    }        
    
    /**
    * Строим ссылку на файл-изображение
    * Формат ссылки: SITEURL.'/files/images/[folder]/[id]_[prefix][postprefix][file]'
    */
    static function imgurl($p)
    {
        $url = bff::buildUrl($p['folder'], 'images');
        if(empty($p['file'])) {
            //если 'file' не указан, возвращаем изображение по-умолчанию
            return $url.(isset($p['prefix'])?$p['prefix'].(isset($p['postprefix'])?$p['postprefix']:'').'_':'').BFF_DEFAULT_IMG; 
        }
        
        //'file' - является уже сформированнным именем файла
        if(!empty($p['static']))
            return $url.$p['file'];
        
        $url .= ($p['id']!==false ? $p['id'].'_' : '');
        if(isset($p['prefix'])) $url.= $p['prefix'];
        if(isset($p['postprefix'])) $url.= $p['postprefix'];

        return $url.$p['file'];
    }

    /**
    * Обрезаем строку
    * @param string строка
    * @param integer необходимая длина текста
    * @param string окончание строки
    * @param boolean разрывать ли слова
    * @param boolean учитывать ли длину текста @param $etc перед обрезанием
    */
    static function truncate($string, $length = 80, $etc = '...', $break_words = false, $calc_etc_length = true)
    {
        if ($length == 0)
            return '';

        if (mb_strlen($string) > $length) {
            $length -= ($calc_etc_length===true ? mb_strlen($etc) : $calc_etc_length);
            if (!$break_words)
                $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1));
          
            return mb_substr($string, 0, $length).$etc;
        } else
        return $string;
    }
    
    static function date_format2($sDatetime, $getTime = false, $bSkipYearIfCurrent = false, $glue1=' ', $glue2=' в ')
    {
        if(!$sDatetime){ if(is_string($bSkipYearIfCurrent)) return $bSkipYearIfCurrent; return false; }
        $res = Func::parse_datetime($sDatetime);
        switch(intval($res['month']))
        {
            case '1': $res['month'] = 'Января'; break;
            case '2': $res['month'] = 'Февраля'; break;
            case '3': $res['month'] = 'Марта'; break;
            case '4': $res['month'] = 'Апреля'; break;
            case '5': $res['month'] = 'Мая'; break;
            case '6': $res['month'] = 'Июня'; break;
            case '7': $res['month'] = 'Июля'; break;
            case '8': $res['month'] = 'Августа'; break;
            case '9': $res['month'] = 'Сентября'; break;
            case '10': $res['month'] = 'Октября'; break;
            case '11': $res['month'] = 'Ноября'; break;
            case '12': $res['month'] = 'Декабря'; break;
            default: break;
        }
        if($getTime) {
            return intval($res['day']).' '.$res['month'].($bSkipYearIfCurrent===true && date('Y',time())==$res['year']?'':$glue1.$res['year']).(!(int)$res['hour'] && !(int)$res['min']?'':$glue2.$res['hour'].':'.$res['min']);
        }
        else 
            return  intval($res['day']).' '.$res['month'].($bSkipYearIfCurrent===true && date('Y',time())==$res['year']?'':$glue1.$res['year']);   
    }
    
    static function date_format3($sDatetime, $sFormat = false)
    {                        
        //get datetime
        if(!$sDatetime) return '';
        $date = func::parse_datetime($sDatetime);

        if($sFormat!==false) {
            return date($sFormat, mktime($date['hour'], $date['min'], 0, $date['month'], $date['day'], $date['year']));
        }
        
        //get now
        $now = array();
        list($now['year'],$now['month'],$now['day']) = explode(',', date('Y,m,d'));

        //дата позже текущей
        if($now['year'] < $date['year'])
            return '';
        
        if($now['year'] == $date['year'] && $now['month'] == $date['month'])
        {
            if($now['day'] == $date['day'])
            {
                return "сегодня {$date['hour']}:{$date['min']}";
            }
            else if($now['day'] == $date['day']-1)
            {
                return "вчера {$date['hour']}:{$date['min']}";
            }
        }
        
        return "{$date['day']}.{$date['month']}.{$date['year']} в {$date['hour']}:{$date['min']}";

    }
    
    static function declension($nCount, $mForms, $bAddCount = true)
    {
        $n = abs($nCount);
        
        $sResult = '';
        if($bAddCount)
            $sResult = $n.' ';
        
        $aForms = (is_string($mForms) ? explode(';', $mForms) : $mForms);

        $n = $n % 100;
        $n1 = $n % 10;  
        if ($n > 10 && $n < 20) {
            return $sResult.$aForms[2];
        }
        if ($n1 > 1 && $n1 < 5) {
            return $sResult.$aForms[1];  
        }
        if ($n1 == 1) {
            return $sResult.$aForms[0];  
        }
        return $sResult.$aForms[2];
    }
    
    static function fetchPHP($aData, $sTemplatePath)
    {
        ob_start(); 
        include ( $sTemplatePath );
        return ob_get_clean();
    }
        
}
