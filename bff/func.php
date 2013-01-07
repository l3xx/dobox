<?php

class Func
{
    /**
     * Array Functions
     */

    /**
     * Transparent Array
     *
     * @param array $arr_data
     * @param key $sByKey
     * @return array_t
     */
    static function array_transparent($arr_data, $sByKey, $bOneInRows = false)
    {
        if(!is_array($arr_data)) return false;
        $arr_data_t = array();
        for($l=0;$l<count($arr_data);$l++)
        {
            if($bOneInRows)
                $arr_data_t[$arr_data[$l][$sByKey]] = $arr_data[$l];
            else
                $arr_data_t[$arr_data[$l][$sByKey]][] = $arr_data[$l];
        }
        return $arr_data_t;
    }

    /**
    * Multi-dimentions array sort, with ability to sort by two and more dimensions
    * $array = array_subsort($array [, 'col1' [, SORT_FLAG [, SORT_FLAG]]]...);
    **/
    static function array_subsort() 
    {
       $args = func_get_args();
       $marray = array_shift($args);

       $i = 0;
       $msortline = "return(array_multisort(";
       foreach ($args as $arg) {
           $i++;
           if (is_string($arg)) {
               foreach ($marray as $row) {
                   $sortarr[$i][] = $row[$arg];
               }
           } else {
               $sortarr[$i] = $arg;
           }
           $msortline .= "\$sortarr[".$i."],";
       }
       $msortline .= "\$marray));";
       
       eval($msortline);
       return $marray;
    }

    static function array_sort_by_subkey(&$arr_data, $subkey, $asc = true)
    {

        $arr_res = Func::array_transparent($arr_data, $subkey);
        if($asc)
        ksort($arr_res);
        else
        krsort($arr_res);

        $arr_data = array();
        foreach ($arr_res as $key => $value) {
            $arr_data = array_merge($arr_data, $value);
        }
    }

    // Сортировка ассоциативного массива
    static function array_key_multi_sort($aArray, $sKey ,$asc=true, $f='strnatcasecmp')
    {
        if($asc)
        {
            usort($aArray, create_function('$a, $b', "return $f(\$a['$sKey'], \$b['$sKey']);"));
        }else{
            usort($aArray, create_function('$a, $b', "return $f(\$b['$sKey'], \$a['$sKey']);"));
        }
        return($aArray);
    }
    
    static function array_2_htmlspecialchars($aParams, $aKeysOnly = null, $stripslashes = false)
    {
        $bMagicQuotes = get_magic_quotes_gpc();
        if(is_array($aParams))
        {
            foreach($aParams as $key=>$val) {
                if(is_array($val) || (!empty($aKeysOnly) && !in_array($key, $aKeysOnly))) continue;
                
                if ($stripslashes && $bMagicQuotes)
                    $val = stripslashes($val);
                
                $aParams[$key] = htmlspecialchars($val);
            }
        }elseif(is_string($aParams))
        {
            $aParams = htmlspecialchars($aParams);
        }
        return $aParams;
    }
    
    /**
     * Create options list for select tag
     *
     * @param array $arr
     * @param string $cur_val
     * @param field_name $field_name
     * @param index_name $index_name
     * @return string
     * $arr - array of date
     * $cur_val - current value
     * $title_key - key name for name of option
     * $index_key - key name for value of option
     */
    static function MakeOptionsListEx($arr, $cur_val='', $title_key='', $index_key='')
    {
        if ($title_key=='' && $index_key=='')
        {
            $options_list = '';
            for($i=0; $i<count($arr);$i++)
            {
                $tmp = '';
                if(is_array($cur_val) && in_array($arr[$i], $cur_val)) $tmp = ' selected';
                elseif (!is_array($cur_val) && $arr[$i]==$cur_val) $tmp = ' selected';
                $options_list .= '<option '.$tmp.'>'.$arr[$i].'</option>';
            }
            return $options_list;
        }

        $options_list = '';
        for($i=0; $i<count($arr);$i++)
        {
            $tmp = '';
            if($title_key!='') $val_iteration = $arr[$i][$title_key];
            else $val_iteration = $arr[$i];
            if ($index_key!='') $cur_val_iteration = $arr[$i][$index_key];
            else $cur_val_iteration = $arr[$i];
            //if ($cur_val_iteration == $cur_val) $tmp = ' selected';
            if(is_array($cur_val) && in_array($cur_val_iteration, $cur_val))
            {
                $tmp = ' selected';
            }
            elseif (!is_array($cur_val) && $cur_val_iteration==$cur_val) $tmp = ' selected';

            $options_list .= '<option value="'.$cur_val_iteration.'"'.$tmp.'>'.$val_iteration.'</option>';
        }
        return $options_list;


    }
    
    /**
     * Navigation 
     */
    static function JSRedirect($url, $bUseJS=false)
    {
        if (headers_sent() || $bUseJS) print "<script type='text/javascript' lang='JavaScript'>location='$url';</script>";
        else header('location: '.$url);
        exit();
    }

    /**
    * Check is this string is email address
    *
    * @param string $str
    * @return bool
    */
    static function IsEmailAddress(&$sEmail)
    {
        if(empty($sEmail))
            return true;
        
        // t.e.s.t@gmail.com => test@gmail.com
        $nAtPosition = stripos($sEmail, '@');
        if(substr($sEmail, $nAtPosition) == '@gmail.com') {
            $sEmail = str_replace('.', '', substr_replace($sEmail, '', $nAtPosition) ).'@gmail.com';
        }  

        if(!ereg( "^.+@.+\\..+$",$sEmail)){return FALSE;}else{
            return (ereg( "^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*$",$sEmail));}
    }
    
    static function getEmailHash(&$sEmail)
    {
        // t.e.s.t@gmail.com => test@gmail.com
        $nAtPosition = stripos($sEmail, '@');
        if(substr($sEmail, $nAtPosition) == '@gmail.com') {
            $sEmail = str_replace('.', '', substr_replace($sEmail, '', $nAtPosition) ).'@gmail.com';
        }  
        
        //fix http://bugs.php.net/bug.php?id=39062
        $crc = crc32(strtolower($sEmail));                  
        if($crc & 0x80000000) $crc-=2<<31;
        
        return ( $crc.strlen($sEmail) );
    }

    static function isPostMethod()
    {
        return ($_SERVER['REQUEST_METHOD']=='POST' ? true : false);
    }

    static function isAjaxRequest($mMethod = 'POST')
    {
        return 
            (isset($_SERVER['HTTP_X_REQUESTED_WITH'])?
                $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest' && ( !empty($mMethod) ? $_SERVER['REQUEST_METHOD'] == $mMethod : true ) 
                : false );
    }
    
    static function getRemoteAddress($bConvert2Integer = false)
    {
        $sIP = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        return ($bConvert2Integer ? ($sIP!=='' ? ip2long($sIP) : 0) : $sIP);
    }
    
    /**
     * POST, GET, SESSION, COOKIES, SERVER
     */

    /**
     * Get POST or GET value
     * @param string key
     * @param boolean trim value
     * @param boolean convert value to integer
     * @param mixed default value
     * @return mixed
     */
    static function POSTGET($sVarName, $bTrim = false, $bConvertToInteger = false, $mDefault = false)
    {
        if(isset($_POST[$sVarName]))
        {
            if($bTrim)
                $_POST[$sVarName] = trim($_POST[$sVarName]);

            if($bConvertToInteger)
                $_POST[$sVarName] = intval($_POST[$sVarName]);
            
            return  $_POST[$sVarName];
        }
        if(isset($_GET[$sVarName]))
        {
            if($bTrim)
                $_GET[$sVarName] = trim($_GET[$sVarName]);

            if($bConvertToInteger)
                $_GET[$sVarName] = intval($_GET[$sVarName]);
                
            return  $_GET[$sVarName];
        }                                  
                    
        return ($bConvertToInteger ? 0 : $mDefault);
    }

    /**
     * Get POST value 
     * @param string key
     * @param boolean trim value
     * @param boolean convert value to integer
     * @param mixed default value
     * @return mixed
     */
    static function POST($sKey, $bTrim = false, $bConvertToInteger = false, $mDefault = false)
    {
        return (isset($_POST[$sKey]) ?
                    (!$bConvertToInteger ? ($bTrim ? trim($_POST[$sKey]) : $_POST[$sKey]) :
                         intval( $bTrim ? trim($_POST[$sKey]) : $_POST[$sKey] ) ) :
                    ($bConvertToInteger ? 0 : $mDefault)
                        );
    }

    /**
    * Get GET or POST value
    * @param string key
    * @param boolean trim value
    * @param boolean convert value to integer
    * @param mixed default value
    * @return mixed
    */
    static function GETPOST($sVarName, $bTrim = false, $bConvertToInteger = false, $mDefault = false)
    {
        if(isset($_GET[$sVarName]))
        {
            if($bTrim)
                $_GET[$sVarName] = trim($_GET[$sVarName]);

            if($bConvertToInteger)
                $_GET[$sVarName] = intval($_GET[$sVarName]);
                
            return  $_GET[$sVarName];
        }                                  

        if(isset($_POST[$sVarName]))
        {
            if($bTrim)
                $_POST[$sVarName] = trim($_POST[$sVarName]);

            if($bConvertToInteger)
                $_POST[$sVarName] = intval($_POST[$sVarName]);
            
            return  $_POST[$sVarName];
        }          
                    
        return ($bConvertToInteger ? 0 : $mDefault);
    }
    
    /**
     * Get GET value
     * @param string key
     * @param boolean trim
     * @param boolean convert value to integer
     * @param mixed default value
     * @return mixed
     */
    static function GET($sKey, $bTrim = false, $bConvertToInteger = false, $mDefault = false)
    {
        return (isset($_GET[$sKey]) ?
                    (!$bConvertToInteger ? ($bTrim ? trim($_GET[$sKey]) : $_GET[$sKey]) :
                         intval( $bTrim ? trim($_GET[$sKey]) : $_GET[$sKey] ) ) :
                    ($bConvertToInteger ? 0 : $mDefault)
                        );
    }

    /**
     * Get SESSION value
     * @param string key
     * @param mixed default falue
     * @return mixed
     */
    static function SESSION($sVarName, $mDefault = false)
    {
        return (isset($_SESSION['SESSION'][$sVarName]) ? $_SESSION['SESSION'][$sVarName] : $mDefault);
    }

    static function setSESSION($sVarName, $value)
    {
        $_SESSION['SESSION'][$sVarName] = $value;
    }
    
    /**
     * Get/Set COOKIE value
     */
    static function setCOOKIE($sName, $mData, $nExpireDays = 30, $sPath = '/', $sDomain = false)
    {
        //if $nExpireDays is null - delete cookie
        return setcookie($sName, $mData, (is_null($nExpireDays)? null : (time() + (3600 * 24 * $nExpireDays))), $sPath, ($sDomain!==false?$sDomain:'.'.SITEHOST));
    }
    
    static function getCOOKIE($sName, $mDefault = false)
    {
        return (isset($_COOKIE[$sName]) ? $_COOKIE[$sName] : $mDefault);
    }
    
    /**
     * Get SERVER value
     */  
    static function getSERVER($key, $mDefault = null)
    {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        } elseif (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        return $mDefault;
    }
    
    static function setSmartyTemplateDir($sDirectory = '', &$sDirectoryPrev = '' )
    {
        global $oSm;
        
        $sDirectoryPrev = '';
        if(isset($oSm)) {
            $sDirectoryPrev = $oSm->template_dir;
            $oSm->template_dir = $sDirectory;
        }
    }

    static function log($sContent, $sFilename = 'errors.log')
    {                                                             
        static $loggers;
        if(!isset($loggers[$sFilename])) {
            $loggers[$sFilename] = new CFileLogger(PATH_BASE.'files'.DIRECTORY_SEPARATOR.'logs', $sFilename);
        }
        return $loggers[$sFilename]->log( $sContent );
    }

    /**
     * Parse date time
     * also you can use in PHP 5
     * $format = '%d/%m/%Y %H:%M:%S';
        $strf = strftime($format);

        echo "$strf\n";

        print_r(strptime($strf, $format));
        03/10/2004 15:54:19
        
        Array
        (
            [tm_sec] => 19
            [tm_min] => 54
            [tm_hour] => 15
            [tm_mday] => 3
            [tm_mon] => 9
            [tm_year] => 104
            [tm_wday] => 0
            [tm_yday] => 276
            [unparsed] =>
        )


     *
     * @param unknown_type $sDatetime
     * @return unknown
     */
    static function parse_datetime($sDatetime='2006-04-05 01:50:00')
    {
        $arr = explode(' ',$sDatetime);

        $arr_res = array();
        $arr_res['year'] = $arr_res['month'] = $arr_res['day'] = $arr_res['hour'] = $arr_res['min'] = $arr_res['sec'] = '';
        if(isset($arr[0]))
        {
            $arr_date = explode('-',$arr[0]);
            if(count($arr_date)==3)
            {
                $arr_res['year'] = $arr_date[0];
                $arr_res['month'] = $arr_date[1];
                $arr_res['day'] = $arr_date[2];
            }
        }
        if(isset($arr[1]))
        {

            $arr_time = explode(':',$arr[1]);
            if(count($arr_time)==3)
            {
                $arr_res['hour'] = $arr_time[0];
                $arr_res['min'] = $arr_time[1];
                $arr_res['sec'] = $arr_time[2];
            }
        }
        return $arr_res;

    }
    
    static function Datetime2dateUSSR($sDatetime='2007-12-31 00:00:00', $getTime=false)
    {
        if(!$sDatetime) return false;
        $res = Func::parse_datetime($sDatetime);
        
        if($getTime)
        return $res['day'].'/'.$res['month'].'/'.$res['year'].' '.$res['hour'].':'.$res['min'].':'.$res['sec'];
        else 
        return $res['day'].'/'.$res['month'].'/'.$res['year'];        
    }

    static function datetime2dateUSSRWithMonth($sDatetime='2007-12-31 00:00:00', $getTime=false)    
    {
        if(!$sDatetime) return false;
        $res = func::parse_datetime($sDatetime);
        switch(intval($res['month']))
        {
            case '1': $res['month'] = 'января'; break;
            case '2': $res['month'] = 'февраля'; break;
            case '3': $res['month'] = 'марта'; break;
            case '4': $res['month'] = 'апреля'; break;
            case '5': $res['month'] = 'мая'; break;
            case '6': $res['month'] = 'июня'; break;
            case '7': $res['month'] = 'июля'; break;
            case '8': $res['month'] = 'августа'; break;
            case '9': $res['month'] = 'сентября'; break;
            case '10': $res['month'] = 'октября'; break;
            case '11': $res['month'] = 'ноября'; break;
            case '12': $res['month'] = 'декабря'; break;
            default: break;
        }
        if($getTime)
        return intval($res['day']).' '.$res['month'].' '.$res['year'].' '.$res['hour'].':'.$res['min'];
        else 
        return  intval($res['day']).' '.$res['month'].' '.$res['year'];        
    }
    
    static function getfilesize($nSize, $bAddSizeLang = true) 
    {
        $aUnits = array('байт', 'Кб', 'Мб', 'Гб', 'Тб');
        for ($i = 0; $nSize >= 1024; $i++) { $nSize /= 1024; }
        return round($nSize, 2).($bAddSizeLang?' '.$aUnits[$i]:'');
    }

    static function checkLoginName($sUsername, $isRequired = false)
    {
        if($isRequired && empty($sUsername) ) return false;

        $arr = array();
        $arr[] = '\x21';           // !
        $arr[] = '\x23-\x26'; // # $ % &
        $arr[] = '\x3d';           // =
        $arr[] = '\x40';          // @
        $arr[] = '\x5b';          // [
        $arr[] = '\x5d';          // ]
        $arr[] = '\x5d';          // ]
        $arr[] = '\x88';          // €


        if (preg_match('/[^(\w)|(\s)|'.implode('|', $arr).']/',$sUsername))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    static function getLoginHash(&$sLogin)
    {
        return ( crc32(strtolower($sLogin)).strlen($sLogin) );
    }

    static function generateRandomName($lettersCount, $bUseUpperLetters = true, $bUseNumbers = true)
    {
        $arr = array('a','b','c','d','e','f',
                     'g','h','i','j','k','l',
                     'm','n','o','p','r','s',
                     't','u','v','x','y','z');
        
        if($bUseUpperLetters) {
            $arr = array_merge($arr, 
            array( 'A','B','C','D','E','F',
                   'G','H','I','J','K','L',
                   'M','N','O','P','R','S',
                   'T','U','V','X','Y','Z') );
        }

        if($bUseNumbers) {
            $arr = array_merge($arr, 
            array( '1','2','3','4','5','6', '7','8','9','0') );
        }

        $pass = "";

        for($i = 0; $i < $lettersCount; $i++){
            $index = mt_rand(0, count($arr) - 1);

            $pass .= $arr[$index];
        }
        return $pass;
    }

    /**
     * генерирует случайную последовательность символов
     */
    static function generator($iLength=10) {
        if ($iLength>32) {
            $iLength=32;
        }
        return substr(md5(uniqid(mt_rand(),true)),0,$iLength);
    }
    
    /**
     * функция генерации meta
     * генерирует описание(mdescription) и ключевые слова(mkeywords)
     * @param string текст
     * @param array результаты
     * @param integer требуемое кол-во ключевых слов
     */
    static function generateMeta($sText, &$aResult = array(), $nKeywordsCount = 20) 
    {
        $quotes = array( "\x27", "\x22", "\x60", "\t","\n","\r","'",",",".","/","¬","#",";",":","@","~","[","]","{","}","=","-","+",")","(","*","&","^","%","$","<",">","?","!", '"' );
        $fastquotes = array( "\x27", "\x22", "\x60", "\t","\n","\r",'"',"'", "\\", '\r', '\n', "/","{","}","[","]");

        $sText = preg_replace ("'\[hide\](.*?)\[/hide\]'si", "", $sText);
        $sText = preg_replace ("'\[attachment=(.*?)\]'si", "", $sText);
        $sText = preg_replace ("'\[page=(.*?)\](.*?)\[/page\]'si", "", $sText);
        $sText = str_replace( "{PAGEBREAK}", "", $sText );

        $sText = str_replace($fastquotes, '', trim(strip_tags(str_replace('<br />', ' ', stripslashes($sText)))));

        $aResult['mdescription'] = substr($sText, 0, 190);

        $sText = str_replace($quotes, '', $sText );

        $aWords = explode(" ", $sText);
        $aTemp = array();
        foreach ($aWords as $word) {
            if (strlen($word) > 4) $aTemp [] = $word;
        }

        $aWords = array_count_values ($aTemp);
        arsort($aWords);

        $aResult['mkeywords'] = implode (", ", array_slice( array_keys($aWords), 0, $nKeywordsCount) );
    }
    
    /**
     * функция транслитерации cyr->lat
     * @param string текст для транслитерации
     * @param string кодировка входящий строки
     * @param string кодировка выходящий строки  
     * @return string
    */
    static function translit($text, $encIn = 'utf-8', $encOut = 'utf-8')
    {
        $text = iconv($encIn, 'utf-8', $text);
        $cyr = array(
        "Щ",  "Ш", "Ч", "Ц","Ю", "Я", "Ж", "А","Б","В","Г","Д","Е","Ё","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х", "Ь","Ы","Ъ","Э","Є","Ї",
        "щ",  "ш", "ч", "ц","ю", "я", "ж", "а","б","в","г","д","е","ё","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х", "ь","ы","ъ","э","є","ї");
        $lat = array(
        "Shh","Sh","Ch","C","Ju","Ja","Zh","A","B","V","G","D","Je","Jo","Z","I","J","K","L","M","N","O","P","R","S","T","U","F","Kh","'","Y","`","E","Je","Ji",
        "shh","sh","ch","c","ju","ja","zh","a","b","v","g","d","je","jo","z","i","j","k","l","m","n","o","p","r","s","t","u","f","kh","'","y","`","e","je","ji"
        );

        for($i=0; $i<count($cyr); $i++){
            $c_cyr = $cyr[$i];
            $c_lat = $lat[$i];
            $text = str_replace($c_cyr, $c_lat, $text);
        }
        
        $text = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]e/", "\${1}e", $text);
        $text = preg_replace("/([qwrtpsdfghklzxcvbnmQWRTPSDFGHKLZXCVBNM]+)[jJ]/", "\${1}'", $text);
        $text = preg_replace("/([eyuioaEYUIOA]+)[Kk]h/", "\${1}h", $text);
        $text = preg_replace("/^kh/", "h", $text);
        $text = preg_replace("/^Kh/", "H", $text);
        $text = preg_replace('/[\?&]+/', '', $text);
        $text = preg_replace('/[\s,\?&]+/', '-', $text);

        return iconv('utf-8', $encOut, $text);
    }

    static function hrefActivate($text) 
    {                                                         
        $parser = new CLinksParser(false, false);
        return $parser->parse($text);
    }   
    
    static function cleanComment($text)
    {
        $text = preg_replace ("/(\<script)(.*?)(script>)/si", '', "$text");
        $text = strip_tags($text);
        $text = str_replace ("<!--", "&lt;!--", $text);
        $text = preg_replace ("/(\<)(.*?)(--\>)/mi", "". nl2br ("\\2")."", $text);
        return Func::hrefActivate($text);
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

    static function php2js($a=false,$noNumQuotes=false)
    {
        if (is_null($a)) return 'null';
        if ($a === false) return 'false';
        if ($a === true) return 'true';
        if (is_scalar($a))
        {
            if (is_float($a)) {
              // Always use "." for floats.
              $a = str_replace(",", ".", strval($a));
            }

            // All scalars are converted to strings to avoid indeterminism.
            // PHP's "1" and 1 are equal for all PHP operators, but
            // JS's "1" and 1 are not. So if we pass "1" or 1 from the PHP backend,
            // we should get the same result in the JS frontend (string).
            // Character replacements for JSON.
            static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
            array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
            if($noNumQuotes && is_numeric($a)) {
                return $a;
            } else {
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
            }
        }
        $isList = true;
        for ($i = 0, reset($a); $i < count($a); $i++, next($a))
        {
            if (key($a) !== $i)
            {
                $isList = false;
                break;
            }
        }
        $result = array();
        if ($isList)
        {
            foreach ($a as $v) $result[] = self::php2js($v);
                return '[' . join(',', $result) . ']';
        }
        else
        {
            foreach ($a as $k => $v) $result[] = self::php2js($k).': '.self::php2js($v);
            return '{' . join(',', $result) . '}';
        }
    }

    /** возвращает массив чисел из которых была составлена сумма битов
     * @param int $nSum: сумма битов
     * @return array
    */
    static function bit2source($nSum)
    {
        $str = strrev(decbin($nSum));
        $nLength = strlen($str);
        $arr = array();            
        for($i = 0; $i < $nLength; $i++)
        {
            if($str[$i]) {
                $arr[] = pow(2, $i);
            }
        }
        return $arr;
    }
    
    static function tt_start( &$time_start, &$mem_start )
    {
        $mtime = microtime();           //Считываем текущее время 
        $mtime = explode(" ",$mtime);   //Разделяем секунды и миллисекунды
        // Составляем одно число из секунд и миллисекунд
        // и записываем стартовое время в переменную  
        $time_start = $mtime[1] + $mtime[0];
        
        $mem_start = memory_get_usage();
    }
    
    static function tt_finish( $time_start, $mem_start )
    {                               
        $mtime = microtime(true);   
        $totaltime = ($mtime - $time_start);//Вычисляем разницу
                                    
        // Выводим не экран 
        printf ("<br />время: <b>%f секунд</b>, память: <b>%d</b> - <b>%d</b><br />", $totaltime, $mem_start, memory_get_usage() );  
    }
    
    static function extensionLoaded($ext, $update = false)
    {                               
        static $cache = array();
        if (!isset($cache[$ext]) || $update) {
            $cache[$ext] = extension_loaded($ext);
        }
        return $cache[$ext];
    }  
    
    static function truncate($string, $length = 80, $etc = '...', $break_words = false)
    {
        if ($length == 0)
            return '';

        if (mb_strlen($string) > $length) {
            $length -= mb_strlen($etc);
            if (!$break_words)
                $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1));
          
            return mb_substr($string, 0, $length).$etc;
        } else
            return $string;
    }
    
    static function image_type_to_extension($type , $dot)
    {
        if ( !function_exists('image_type_to_extension') ) {
            $e = array ( 1 => 'gif', 'jpg', 'png', 'swf', 'psd', 'bmp',
                'tiff', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc',
                'aiff', 'wbmp', 'xbm');    
            $type = (int)$type;
            return ($dot ? '.' : '') . $e[$type];
        } else {                      
            return image_type_to_extension($type, $dot);
        }
    }

}
