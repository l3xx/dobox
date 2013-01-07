<?php
  
/**
 * Способы очистки входящих параметров.
 */

define('TYPE_NOCLEAN',  0); // без изменений
define('TYPE_BOOL',     1); // boolean
define('TYPE_INT',      2); // integer
define('TYPE_UINT',     3); // unsigned integer
define('TYPE_NUM',      4); // number
define('TYPE_UNUM',     5); // unsigned number
define('TYPE_UNIXTIME', 6); // unix datestamp (unsigned integer)
define('TYPE_STR',      7); // trimmed string
define('TYPE_NOTRIM',   8); // string - no trim
define('TYPE_NOHTML',   9); // trimmed string with HTML made safe
define('TYPE_ARRAY',   10); // array
define('TYPE_FILE',    11); // file
define('TYPE_BINARY',  12); // binary string
define('TYPE_NOHTMLCOND', 13); // trimmed string with HTML made safe if determined to be unsafe
define('TYPE_NOTAGS',  14); // trimmed string, stripped tags

define('TYPE_ARRAY_BOOL',     101);
define('TYPE_ARRAY_INT',      102);
define('TYPE_ARRAY_UINT',     103);
define('TYPE_ARRAY_NUM',      104);
define('TYPE_ARRAY_UNUM',     105);
define('TYPE_ARRAY_UNIXTIME', 106);
define('TYPE_ARRAY_STR',      107);
define('TYPE_ARRAY_NOTRIM',   108);
define('TYPE_ARRAY_NOHTML',   109);
define('TYPE_ARRAY_ARRAY',    110);
define('TYPE_ARRAY_FILE',     11);  // An array of "Files" behaves differently than other <input> arrays. TYPE_FILE handles both types.
define('TYPE_ARRAY_BINARY',   112);
define('TYPE_ARRAY_NOHTMLCOND',113);
define('TYPE_ARRAY_NOTAGS',   114);

define('TYPE_ARRAY_KEYS_INT', 202);
define('TYPE_ARRAY_KEYS_STR', 207);

define('TYPE_CONVERT_SINGLE', 100); // value to subtract from array types to convert to single types
define('TYPE_CONVERT_KEYS',   200); // value to subtract from array => keys types to convert to single types

/**
* Класс для работы с входящими данными (GET, POST, COOKIE ...)
*/
class CInputCleaner
{
    /**
    * Таблица сокращений названий суперглобальных массивов
    * @var array
    */
    var $superglobal_lookup = array(
        'g' => '_GET',
        'p' => '_POST',
        'r' => '_REQUEST',
        'c' => '_COOKIE',
        's' => '_SERVER',
        'e' => '_ENV',
        'f' => '_FILES'
    );
    var $mc;
 
    /**
    * Конструктор
    */
    public function __construct()
    {
        if (!is_array($GLOBALS))
        {
            die('<strong>Fatal Error:</strong> Invalid URL.');
        }

        // обрабатываем ситуацию конфликта данных из куков с данными из _GET и _POST, и берем данные не из куков
        foreach (array_keys($_COOKIE) AS $varname) {
            unset($_REQUEST["$varname"]);
            if(isset($_POST["$varname"])) {
                $_REQUEST["$varname"] =& $_POST["$varname"];
            }
            else if(isset($_GET["$varname"])) {
                $_REQUEST["$varname"] =& $_GET["$varname"];
            }
        }
        $this->mc = get_magic_quotes_gpc();
    }

    /**
     * Блокируем копирование/клонирование объекта
     */
    protected function __clone() 
    {
        
    }
    
    /**
     * Делает возможным только один экземпляр этого класса
     * @return CInputCleaner объект
     */
    static protected $oInstance=null;  
    static public function i() {
        if (isset(self::$oInstance) and (self::$oInstance instanceof self)) {
        } else {
            self::$oInstance = new self();
        }
        return self::$oInstance;
    }
    
    /**
    * Приводим данные в массиве к безопасной форме
    * @param array Массив данных, которые необходимо обработать
    * @param array Массив имя переменной и тип, которые необходимо извлечь
    * @return array
    */
    function &clean_array(&$source, $variables, &$return = array(), $no_emtpy_vars = array())
    {
        $check_empty = !empty($no_emtpy_vars);
        if($check_empty) { $errors = &Errors::i(); }
        
        foreach ($variables as $varname => $vartype)
        {
            $return["$varname"] =& $this->clean($source["$varname"], $vartype, isset($source["$varname"]));
            if($check_empty && (!isset($source["$varname"]) || empty($return["$varname"])) && in_array($varname, $no_emtpy_vars)) {
                $errors->field($varname, 'empty');
            }
        }      
        return $return;
    }

    /**
    * Приводим данные GET-POST-COOKIE к безопасной форме
    * @param string Сокращение для суперглобального массива: g, p, c, r или f (get, post, cookie, request или files)
    * @param array Массив имя переменной и тип, которые необходимо извлечь
    * @return array
    */
    function &clean_array_gpc($source, $variables, &$return = array(), $no_emtpy_vars = array())
    {
        $sg =& $GLOBALS[$this->superglobal_lookup["$source"]];
        $check_empty = !empty($no_emtpy_vars);
        if($check_empty) { $errors = &Errors::i(); }
        
        foreach ($variables as $varname => $vartype)
        {
            $return["$varname"] =& $this->clean(
                $sg["$varname"],
                $vartype,
                isset($sg["$varname"])
            );                       
                        
            if($check_empty && (!isset($sg["$varname"]) || empty($return["$varname"])) && in_array($varname, $no_emtpy_vars)) {
                $errors->field($varname, 'empty');
            }
        }
        
        return $return;
    }
    
    function &getm($variables, &$return = array(), $no_emtpy_vars = array())
    {
        return $this->clean_array_gpc('g', $variables, $return, $no_emtpy_vars);
    }

    function &postm($variables, &$return = array(), $no_emtpy_vars = array())
    {
        return $this->clean_array_gpc('p', $variables, $return, $no_emtpy_vars);
    }
    
    /**
    * Приводим одну переменную GET-POST-COOKIE к безопасной форме и возвращаем её
    * @param array Сокращение для суперглобального массива: g, p, c, r или f (get, post, cookie, request или files)
    * @param string Имя переменной
    * @param integer Тип переменной
    * @return mixed
    */
    function &clean_gpc($source, $varname, $vartype = TYPE_NOCLEAN)
    {
        $sg =& $GLOBALS[$this->superglobal_lookup["$source"]];
        return $this->clean(
            $sg["$varname"],
            $vartype,
            isset($sg["$varname"])
        );
    }

    function &get($varname, $vartype = TYPE_NOCLEAN)
    {
        return $this->clean_gpc('g', $varname, $vartype);
    }

    function &getpost($varname, $vartype = TYPE_NOCLEAN)
    {
        if(isset($GLOBALS[$this->superglobal_lookup['g']]["$varname"])) {
            return $this->clean_gpc('g', $varname, $vartype);
        }
        return $this->clean_gpc('p', $varname, $vartype);
    }    
    
    function &post($varname, $vartype = TYPE_NOCLEAN)
    {
        return $this->clean_gpc('p', $varname, $vartype);
    }
    
    function &cookie($varname, $vartype = TYPE_NOCLEAN)
    {
        return $this->clean_gpc('c', $varname, $vartype);
    }

    function &postget($varname, $vartype = TYPE_NOCLEAN)
    {
        if(isset($GLOBALS[$this->superglobal_lookup['p']]["$varname"])) {
            return $this->clean_gpc('p', $varname, $vartype);
        }
        return $this->clean_gpc('g', $varname, $vartype);
    } 
    
     function id($varname = 'rec', $source = 0, $vartype = TYPE_UINT)
    {
        if($source === 0) {
            return $this->clean_gpc('g', $varname, $vartype);
        } else {
            switch($source)
            {
                case 'p': { return $this->clean_gpc('p', $varname, $vartype); } break;
                case 'gp': { return $this->clean_gpc((isset($_GET["$varname"])?'g':'p'), $varname, $vartype); } break;
                case 'pg': { return $this->clean_gpc((isset($_POST["$varname"])?'p':'g'), $varname, $vartype); } break;
            }
        }
    }
    
    /**
    * Приводим одну переменную к безопасной форме и возвращаем её
    * @param mixed Значение
    * @param integer Тип
    * @param boolean Требуется ли очистка переменной либо ее инициализация
    * @return mixed Переменная в безопасной форме
    */
    function &clean(&$var, $vartype = TYPE_NOCLEAN, $exists = true)
    {              
        if ($exists)
        {
            if ($vartype < TYPE_CONVERT_SINGLE)
            {
                $this->do_clean($var, $vartype);
            }
            else if (is_array($var))
            {
                if ($vartype >= TYPE_CONVERT_KEYS)
                {
                    $var = array_keys($var);
                    $vartype -=  TYPE_CONVERT_KEYS;
                }
                else
                {
                    $vartype -= TYPE_CONVERT_SINGLE;
                }

                foreach (array_keys($var) AS $key)
                {
                    $this->do_clean($var["$key"], $vartype);
                }
            }
            else
            {
                $var = array();
            }
            return $var;
        }
        else
        {
            if ($vartype < TYPE_CONVERT_SINGLE)
            {
                switch ($vartype)
                {
                    case TYPE_INT:
                    case TYPE_UINT:
                    case TYPE_NUM:
                    case TYPE_UNUM:
                    case TYPE_UNIXTIME:
                    {
                        $var = 0;
                        break;
                    }
                    case TYPE_STR:
                    case TYPE_NOHTML:
                    case TYPE_NOTRIM:
                    case TYPE_NOHTMLCOND:
                    case TYPE_NOTAGS:
                    {
                        $var = '';
                        break;
                    }
                    case TYPE_BOOL:
                    {
                        $var = 0;
                        break;
                    }
                    case TYPE_ARRAY:
                    case TYPE_FILE:
                    {
                        $var = array();
                        break;
                    }
                    case TYPE_NOCLEAN:
                    {
                        $var = null;
                        break;
                    }
                    default:
                    {
                        $var = null;
                    }
                }
            }
            else
            {
                $var = array();
            }

            return $var;
        }
    }

    /**
    * Производим непосредственную очистку данных
    * @param mixed Данные
    * @param integer Тип
    * @return mixed
    */
    function &do_clean(&$data, $type)
    {
        static $booltypes = array('1', 'yes', 'y', 'true', 'on');

        switch ($type)
        {
            case TYPE_INT:    $data = intval($data);                                   break;
            case TYPE_UINT:   $data = ($data = intval($data)) < 0 ? 0 : $data;         break;
            case TYPE_NUM:    $data = strval($data) + 0;                               break;
            case TYPE_UNUM:   $data = strval($data) + 0;
                              $data = ($data < 0) ? 0 : $data;                         break;
            case TYPE_BINARY: $data = strval($data);                                   break;
            case TYPE_STR: {  
                $data = trim(strval($data)); 
            } break;
            case TYPE_NOTRIM: $data = strval($data);                                   break;
            case TYPE_NOHTML: $data = htmlspecialchars(trim(strval($data)));           break;
            case TYPE_BOOL:   $data = in_array(strtolower($data), $booltypes) ? 1 : 0; break;
            case TYPE_ARRAY:  $data = (is_array($data)) ? $data : array();             break;
            case TYPE_NOHTMLCOND:
            {
                $data = trim(strval($data));
                //если данные еще не проквочены
                if (strcspn($data, '<>"') < strlen($data) OR (strpos($data, '&') !== false AND !preg_match('/&(#[0-9]+|amp|lt|gt|quot);/si', $data)))
                {
                    $data = htmlspecialchars($data);
                }
                break;
            }
            case TYPE_NOTAGS: { 
                $data = trim(strval($data));
                $data = strip_tags( $data );
            } break;
            case TYPE_FILE:
            {
                // perhaps redundant :p
                if (is_array($data))
                {
                    if (is_array($data['name']))
                    {
                        $files = count($data['name']);
                        for ($index = 0; $index < $files; $index++)
                        {
                            $data['name']["$index"] = trim(strval($data['name']["$index"]));
                            $data['type']["$index"] = trim(strval($data['type']["$index"]));
                            $data['tmp_name']["$index"] = trim(strval($data['tmp_name']["$index"]));
                            $data['error']["$index"] = intval($data['error']["$index"]);
                            $data['size']["$index"] = intval($data['size']["$index"]);
                        }
                    }
                    else 
                    {
                        $data['name'] = trim(strval($data['name']));
                        $data['type'] = trim(strval($data['type']));
                        $data['tmp_name'] = trim(strval($data['tmp_name']));
                        $data['error'] = intval($data['error']);
                        $data['size'] = intval($data['size']);
                    }
                }
                else
                {
                    $data = array(
                        'name'     => '',
                        'type'     => '',
                        'tmp_name' => '',
                        'error'    => 0,
                        'size'     => 4, // UPLOAD_ERR_NO_FILE
                    );
                }
                break;
            }
            case TYPE_UNIXTIME:
            {
                if (is_array($data))
                {
                    $data = $this->clean($data, TYPE_ARRAY_UINT);
                    if ($data['month'] AND $data['day'] AND $data['year']) {
                        $data = mktime($data['hour'], $data['minute'], $data['second'], $data['month'], $data['day'], $data['year']);
                    }
                    else {
                        $data = 0;
                    }
                }
                else
                {
                    $data = ($data = intval($data)) < 0 ? 0 : $data;
                }
                break;
            }
            case TYPE_NOCLEAN:
            {
                break;
            }

            default:
            {
                if (FORDEV)
                {
                    trigger_error('CInputCleaner::do_clean() Invalid data type specified', E_USER_WARNING);
                }
            }
        }

        // strip out characters that really have no business being in non-binary data
        switch ($type)
        {
            case TYPE_STR:
            case TYPE_NOTRIM:
            case TYPE_NOHTML:
            case TYPE_NOHTMLCOND:
            case TYPE_NOTAGS:
                $data = str_replace(chr(0), '', $data);
                if($this->mc) {
                    $data = stripslashes($data);
                }
        }

        return $data;
    }       
}
