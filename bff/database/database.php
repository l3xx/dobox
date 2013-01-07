<?php

/**
* @version  2.3
* @modified 2009-01-26
*/

/**
 * PgSQl class
 * $oDb = new CDatabase();
 * $oDb->connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
 * $arr = $oDb->select($query); // get table into array
 * $arr = $oDb->one_row($query); // get row table into array
 * $data = $oDb->one_data($query); // get one field
 * $oDb->execute($query);  // execute update, insert, delete and other queries
 */

class CDatabase
{
    /**
     * @var string The Data Source Name, or DSN, contains the information required to connect to the database.
     * @see http://www.php.net/manual/en/function.PDO-construct.php
     */
    public $connectionString;
    public $username='';
    public $password='';
    
    private $_dbtype = '';
    private $_attributes = array();
    private $_pdo;
    
    var $error = '';
    var $useCHARSET = true;
    var $defaultCHARSET = 'UTF8';
//    var $defaultCHARSET = 'cp1251';
    var $bMultiInsert = true; // Поддерживается ли множественная вставка?    

    /** statistic vars */
    var $bShowStatistic = true;
    var $bMakeStatistic = true;
    var $aStatistic = array();
    var $nQueryCount = 0;
    var $nExecuteCount = 0;
    var $nStatisticTotalTime = 0;
    private $_stat_mktime = 0;
    private $_stat_mem = 0;

    public function __construct($dbtype = '', $dsn='', $username='', $password='')
    {
        /**
        * dsn @example:
        * "pgsql:host=DB_HOST_SYSGEN dbname=DB_NAME_SYSGEN"
        * "mysql:host=DB_HOST_SYSGEN;dbname=DB_NAME_SYSGEN"
        */
        $this->_dbtype = $dbtype;  //pgsql, mysql, mysqli
        $this->connectionString = $dsn;
        $this->username = $username;
        $this->password = $password;
        
        if(!func::extensionLoaded('pdo')) {
            if (!dl('pdo.so')) {
                trigger_error('PDO extension is not loaded');
                exit;
            }
        }
    }       
    
    //connection functions
    function connect()
    {
        if($this->_pdo === null)
        {
            if(empty($this->connectionString))
                throw new Exception('CDatabase.connectionString cannot be empty.');
                
            try
            {
                $this->_pdo = new PDO($this->connectionString, $this->username, $this->password, $this->_attributes);
                $this->_pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
                
                if($this->useCHARSET)
                    $this->setCharset($this->defaultCHARSET);
                
                $this->execute('SELECT @bbs_editpass_encode_key := "'.BBS_EDITPASS_ENCODE_KEY.'"; ');
                
                return true;
            }
            catch(PDOException $e) { 
                echo $e->getMessage().'<br />';   
                echo 'Сайт не работает по техническим причинам. Просим прощения за доставленные неудобства.';
                exit;
            }  
        }
        return false;
    }    

    function initConnection()
    {
        if(isset($this->_pdo))
            return false;
        
        $this->connect();
    }
    
    function isConnected() // not implemented
    {
        
    }
    
    function disconnect() 
    {
        $this->_pdo = null;
        return true;
    }
    
    function error($sText, $nBacktraceLevel = 1)
    {
        $msg = $this->_pdo->errorInfo(); 
        $msg = "[$sText] ( ".(@$msg[0].'.'.@$msg[1]).' : '.(isset($msg[2]) ? $msg[2] : '').' )';
            
        $aBacktrace = debug_backtrace();
        if(isset($aBacktrace[$nBacktraceLevel]))
            $msg .= "<br /> {$aBacktrace[$nBacktraceLevel]['file']} [{$aBacktrace[$nBacktraceLevel]['line']}]";

        trigger_error($msg, E_USER_ERROR);
        func::log($msg);
    }
                   
    //statistic functions
    private function getMemoryUsage()
    {
        if (function_exists('memory_get_usage'))
        {
            return memory_get_usage();
        }
        elseif(substr(PHP_OS,0,3)=='WIN')
        {
            // Windows workaround
            $output = array();

            exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);
            return substr($output[5], strpos($output[5], ':') + 1);
        }
        else
        {
            return '';
        }
    }   

    public function getStatistic()
    {
        if($this->bShowStatistic)
            debug($this->aStatistic);

        return 'query: '.$this->nQueryCount.' <br /> execute: '.$this->nExecuteCount.'<br /> total:'.($this->nQueryCount+$this->nExecuteCount).' <br /> time using CDatabase.php: '.($this->nStatisticTotalTime).' ';
    }
    
    function statStart()
    {
        if(!$this->bMakeStatistic) return;
        
        $this->_stat_mktime = microtime();
        $this->_stat_mem = $this->getMemoryUsage();
        $this->nQueryCount++; 
    }

    function statFinish($query)
    {
        if(!$this->bMakeStatistic) return;
        
        $time = (microtime() - $this->_stat_mktime);
        
        $this->nStatisticTotalTime += $time;
        $this->aStatistic[] = array('query' => $query, 
            'time' => number_format($time, 4), 
            'memory_start'  => $this->_stat_mem, 
            'memory_finish' => $this->getMemoryUsage());        
    }
    
    function setCharset($CHARSET='cp1251')
    {
        switch($this->getDriverName())
        {
            case 'pgsql':
                    $this->_pdo->exec('SET CLIENT_ENCODING TO '.$CHARSET);
            break;
            case 'mysqli':
            case 'mysql':
                    $this->_pdo->exec('SET NAMES '.$CHARSET);
            break;
        }
    }

   
    /**
     * Выполняем запрос (update, insert, delete)
     * @param string
     * @param array массив парамметров для постановки
     * @param boolean возвращать ли кол-во обработанных строк                                                  
     * @return bool
     */
    function execute($sql, $aParams=null, $bReturnRowCount = true)
    {
        $this->statStart();              
        
        $res = 0;               
        try
        {                                                    
            $sth = $this->_pdo->prepare($sql);
            if(!empty($aParams)) {     
                $p = current($aParams);
                if(sizeof($p) == 3) {
                    /* $aParams = array( array(':carrot', $carrot, PDO::PARAM_STR),
                                         array(':category', $category, PDO::PARAM_INT), ... )
                    */
                    foreach($aParams as $p)
                        $sth->bindParam($p[0], $p[1], $p[2]);
                    
                    $res = $sth->execute(); 
                } else if(sizeof($p) == 2) {
                    /* $aParams = array( array($carrot, PDO::PARAM_STR),
                                         array($category, PDO::PARAM_INT), ... )
                    */
                    $i = 1;
                    foreach($aParams as $p) {
                        $sth->bindParam($i++, $p[0], $p[1]);
                    }
                    
                    $res = $sth->execute(); 
                } else {
                    /* $aParams = array( $carrot, $category, ... ) или
                       $aParams = array( ':carrot'=>$carrot, ':category'=>$category, ... ) */                    
                    $res = $sth->execute($aParams); 
                }
            } else {
                $res = $sth->execute($aParams); 
            }
            if($res !== FALSE) {
                $res = ($bReturnRowCount ? $sth->rowCount() : $sth->fetch(PDO::FETCH_ASSOC) ); 
            }                                                              
        }
        catch(Exception $e) {
            $this->error($e->getMessage());
        }

        $this->statFinish($sql);

        return $res;
    }

    /**
     * Get last insert id
     * @param string sequence name (tableName_FieldName)
     * @return integer
     */     
    function insert_id($sTableName='', $sColumnName='id', $sSequencePostfix='_seq')
    {                        
        try {
            return (integer)$this->_pdo->lastInsertId($sTableName.($sColumnName?'_'.$sColumnName:'').$sSequencePostfix);
        }
        catch(Exception $e) {
            $this->error($e->getMessage());
            return 0;
        }
    }

    /**
     * Get many rows of table (query result)
     * @param string  
     * @param long - PDO::FETCH_NUM, PDO::FETCH_ASSOC, PDO::FETCH_BOTH, PDO::FETCH_OBJ
     * @return array
     */
    function select($sql, $fetchType = PDO::FETCH_ASSOC)
    {
        $this->statStart();   
        
        $res = $this->query($sql); 
        if(empty($res)) return 0;

        $data = $res->fetchAll($fetchType);  
                
        $this->statFinish($sql);

        return $data;
    }

    function select_one_column($sql)
    {
        $this->statStart();
        
        $res = $this->query($sql);                           
        if(empty($res)) return 0;

        $data = $res->fetchAll(PDO::FETCH_COLUMN);

        $this->statFinish($sql);

        return $data;
    }

    /**
     * Get one date from query result
     * @param string
     * @return string
     */
    function one_data($sql)
    {
        $this->statStart();
        
        $res = $this->query($sql);                            
        if(empty($res)) return 0;
        
        $data = $res->fetchColumn();
        
        $this->statFinish($sql);

        return $data;
    }

    /**
     * Get row from query result
     * @param string
     * @param long - PDO::FETCH_NUM;PDO::FETCH_ASSOC;PDO::FETCH_BOTH;PDO::FETCH_OBJ
     * @return bool
     */
    function one_array($sql, $fetchType = PDO::FETCH_ASSOC)
    {
        $this->statStart();
        
        $res = $this->query($sql);                             
        if(empty($res)) return 0;
        
        $data = $res->fetch($fetchType);
        
        $this->statFinish($sql);

        return($data);
    }
           
    /**
     * Get field list of table
     * @param string
     * @return mixed
     */
    function fieldsList( $sTableName )
    {
        $sTableName = CDatabase::str2sql($sTableName);
        switch($this->getDriverName())
        {
            case 'pgsql': {
                return $this->select_one_column('SELECT column_name FROM information_schema.columns WHERE table_name ='.$sTableName );
            }
            break;
            case 'mysqli':
            case 'mysql': {
                $aResult = array();
                $aFields = $this->select('DESCRIBE '.$sTableName);
                foreach($aFields as $v) {
                    $aResult[] = $v['Field'];
                }
                return $aResult;
            }
            break;
        }
    }

    /**
     * Check if is table
     * @param string
     * @return boolean
     */
    function isTable($sTableName) // not implemented (for mysql)
    {
        switch($this->getDriverName())
        {
            case 'pgsql': {
                $aResult = $this->select_one_column("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
                foreach($aResult as $v) {
                    if($v == $sTableName) return true;
                }
            } break;
            case 'mysqli':
            case 'mysql': {
                $aResult = $this->select('SHOW TABLES');
                foreach($aResult as $v) {
                    if($v['Tables_in_'.$this->currentDB] == $sTableName) return true;
                }
            } break;
        }
        return false;
    }

    /**
     * move record
     * @param integer record id
     * @param string action (up, down)
     * @param string table name with DB_PREFIX
     * @param string order field name
     * @param string id field name
     * @return boolean
     */
    function moveRecord($nRecordID, $sAction, $sTable, $sOrderField='number', $sIDField='id', $bMoreWhenZero = false)
    {
        if(!in_array($sAction, array('up', 'down'))) return false;

        $nRecordID = intval($nRecordID);
        if(!$nRecordID) return false;

        $aInfo = $this->one_array("SELECT $sIDField, $sOrderField FROM $sTable WHERE $sIDField=$nRecordID LIMIT 1");
        if(!$aInfo) return false;

        if($sAction=='down')
            $aOther = $this->one_array("SELECT * FROM $sTable WHERE $sOrderField>{$aInfo[$sOrderField]} ".($bMoreWhenZero?" AND $sOrderField>0 ":"")." ORDER BY $sOrderField ASC LIMIT 1");
        else
            $aOther = $this->one_array("SELECT * FROM $sTable WHERE $sOrderField<{$aInfo[$sOrderField]} ".($bMoreWhenZero?" AND $sOrderField>0 ":"")." ORDER BY $sOrderField DESC LIMIT 1");

        if(!$aOther) return false;

        $this->execute("UPDATE $sTable SET $sOrderField={$aInfo[$sOrderField]} WHERE $sIDField={$aOther[$sIDField]}");
        $this->execute("UPDATE $sTable SET $sOrderField={$aOther[$sOrderField]} WHERE $sIDField={$aInfo[$sIDField]}");

        return true;
    }

    function isRecordExists($nRecordID, $sTableName)
    {
        return (bool)$this->one_data('SELECT EXISTS (SELECT id FROM '.$sTableName.' WHERE id='.$this->str2sql($nRecordID).')');
    }

    function getNOW($bQuote = true)
    {                         
        static $date;
        if(!isset($date)){ 
            $date = date('Y-m-d H:i:s');
        }
        
        return ($bQuote ? $this->str2sql($date) : $date);
    }
    
    function str2sql($value, $stripslashes=true)
    {
        //Stripslashes
        if ($stripslashes && get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        
        //Quote
        return $this->_pdo->quote($value);
    }

    function getTablesList() // not implemented (for mysql) 
    {
        switch($this->getDriverName())
        {
            case 'pgsql': {
                return $this->select_one_column("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
            } break;
            case 'mysqli':
            case 'mysql': {
                $aResult = $this->select('SHOW TABLES');
                $aTables = array();
                foreach($aResult as $v) {
                    $aTables[] = $v['Tables_in_dyncatalog'];
                }
                return $aTables;
            } break;
        }
    }    

    function prepareUpdateQuery(&$sQueryData, $aData, $mNoPrepareKeys = array())
    {
        if(empty($aData)) return '';
        
        $queryData = array();
        foreach($aData as $key=>$value)
        {
            if(!empty($value) || $value==0) {
                $queryData[] = $key.' = '.(!empty($mNoPrepareKeys) && ($mNoPrepareKeys === true || in_array($key, $mNoPrepareKeys)) ? $value : $this->str2sql($value)).' ';
            }
        }
        $sQueryData = join(', ', $queryData);
    }
    
    function prepareInsertQuery(&$sFields, &$sValues, $aData)
    {
        $fields = array();
        $values = array();
        
        foreach($aData as $key=>$value)
        {
            if((!empty($value) || $value==0) && !is_array($value))
            {
                $fields[] = $key;
                $values[] = $this->str2sql($value);
            }
        }
        
        $sFields = join(', ', $fields);
        $sValues = join(', ', $values);
    }
        
    function prepareLimit($nOffset, $nLimit)
    {
        switch($this->getDriverName())
        {
            case 'pgsql': {
                return " LIMIT $nLimit OFFSET $nOffset ";
            }
            break;
            case 'mysqli':
            case 'mysql': 
            default:
            {
                return " LIMIT $nOffset,$nLimit ";
            }
            break;
        }
    }
    
    /**
    * Строит IN или NOT IN sql строку сравнения
    * @param string название колонки для сравнения
    * @param array массив значений - разрешенных (IN) или запрещенных (NOT IN)
    * @param boolean true - NOT IN (), false - IN ()
    * @param boolean true - разрешить массив $aValues быть пустым, эта функция вернет 1=1 или 1=0
    * @param boolean приводит значения к integer
    */
    function prepareIN($sField, $aValues, $bNot = false, $bAllowEmptySet = true, $bIntegers = true)
    {
        if(!sizeof($aValues))
        {
            if(!$bAllowEmptySet) {
                $this->error('No values specified for SQL IN comparison');
            }
            else {
                return (($bNot) ? '1=1' : '1=0');
            }
        }

        if(!is_array($aValues)) {
            $aValues = array($aValues);
        }

        if(sizeof($aValues) == 1) {
            @reset($aValues);
            return $sField . ($bNot ? ' <> ' : ' = ') . ($bIntegers ? intval(current($aValues)): $this->str2sql( current($aValues) ) );
        }
        else {
            if($bIntegers) {
                $aValues = array_map('intval', $aValues);
            } else {
                $aValues = array_map(array($this, 'str2sql'), $aValues);
            }
            return $sField . ($bNot ? ' NOT IN ' : ' IN ') . '(' . implode(',', $aValues) . ')';
        }
    }

    /**
    * Выполняет множественную вставку
    * @param string таблица
    * @param array многомерный массив для вставки.
    * @return bool false - если запрос не выполнялся.
    */
    function multiInsert($sTable, &$aData, $aStrKeys = false)
    {
        if(!sizeof($aData))
            return false;

        if ($this->bMultiInsert)
        {
            $aResult = array();
            foreach($aData as $id=>$data)
            {
                // Если массив не многомерный выполняем нормальный insert запрос
                if (!is_array($data))
                {
                    $fields = array();
                    $values = array();
                    foreach ($aData as $key => $var)
                    {
                        $fields[] = $key;
                        $values[] = $this->str2sql($var);
                    }
                    
                    $this->execute('INSERT INTO '.$sTable.' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')');
                    return true;
                }

                $values = array();
                foreach ($data as $key => $var)
                    $values[] = $this->str2sql($var);

                $aResult[] = '('.implode(', ', $values).')';
            }
            
            if(!empty($aResult))
                $this->execute('INSERT INTO '.$sTable.' ('.implode(', ', array_keys($aData[0])).') VALUES '.implode(', ', $aResult));
        }
        else
        {
            foreach($aData as $record)
            {
                if(!is_array($record))
                    return false;

                $fields = array();
                $values = array();
                foreach ($record as $key => $var)
                {
                    $fields[] = $key;
                    $values[] = $this->str2sql($var);
                }

                $this->execute('INSERT INTO '.$sTable.' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')');
            }
        }

        return true;
    }
    
    function rotateTablednd($sTable, $sAdditionalQuery = '', $sIDField = 'id', $sOrderField = 'num', $bTree = false, $sPIDField = 'pid')
    {
                          
        do{
            /*
            * dragged  - перемещаемый елемент
            * target   - елемент 'до' или 'после' которого, оказался перемещаемый елемент (сосед)
            * position - новая позиция перемещаемого елемента относительно 'target' елемента
            */              
            
            $nDraggedID = intval(str_replace('dnd-', '', (!empty($_POST['dragged'])?$_POST['dragged']:'') ));
            if($nDraggedID<=0) break;
            
            $nNeighboorID = intval(str_replace('dnd-', '', (!empty($_POST['target'])?$_POST['target']:'') )); 
            if($nNeighboorID<=0) break;

            if(!$sPosition = func::POST('position', true))
                break;
            elseif(!in_array($sPosition, array('after', 'before')))
                break;    
            
            //сортируем
            $aNeighboorData = $this->one_array("SELECT $sIDField, $sOrderField".($bTree?", $sPIDField":'')." FROM $sTable WHERE $sIDField=$nNeighboorID $sAdditionalQuery LIMIT 1");
            if(!$aNeighboorData) return false;
            
            if($sPosition == 'before') { //before
                $this->execute("UPDATE $sTable SET $sOrderField = (CASE WHEN $sIDField=$nDraggedID THEN {$aNeighboorData[$sOrderField]} ELSE $sOrderField+1 END) 
                                WHERE ($sOrderField>={$aNeighboorData[$sOrderField]} OR $sIDField=$nDraggedID) 
                                      ".($bTree?" AND $sPIDField = ".$aNeighboorData[$sPIDField]:'')." $sAdditionalQuery");
            } else { // after
                $this->execute("UPDATE $sTable SET $sOrderField = (CASE WHEN $sIDField=$nDraggedID THEN {$aNeighboorData[$sOrderField]}+1 ELSE $sOrderField+1 END) 
                                WHERE ($sOrderField>{$aNeighboorData[$sOrderField]} OR $sIDField=$nDraggedID) 
                                      ".($bTree?" AND $sPIDField = ".$aNeighboorData[$sPIDField]:'')." $sAdditionalQuery");            
            }       
            
            return true;

        } while(false);                    
        
        return false;
    }

    /**
     * Converts rowset to the forest.
     * 
     * @param array $rows       Two-dimensional array of resulting rows.
     * @param string $idName    Name of ID field.
     * @param string $pidName   Name of PARENT_ID field.
     * @return array            Transformed array (tree).
     */
    function transformRowsToTree($rows, $idName, $pidName, $childrenName = 'childnodes')
    {
        $children = array(); // children of each ID
        $ids = array();
        // Collect who are children of whom.
        foreach ($rows as $i=>$r) {
            $row =& $rows[$i];
            $id = $row[$idName];
            if ($id === null) {
                // Rows without an ID are totally invalid and makes the result tree to 
                // be empty (because PARENT_ID = null means "a root of the tree"). So 
                // skip them totally.
                continue;
            }
            $pid = $row[$pidName];
            if ($id == $pid) $pid = null;
            $children[$pid][$id] =& $row;
            if (!isset($children[$id])) $children[$id] = array();
            $row[$childrenName] =& $children[$id];
            $ids[$id] = true;
        }
        // Root elements are elements with non-found PIDs.
        $tree = array();
        foreach ($rows as $i=>$r) {
            $row =& $rows[$i];
            $id = $row[$idName];
            $pid = $row[$pidName];
            if ($pid == $id) $pid = null;
            if (!isset($ids[$pid])) {
                $tree[$row[$idName]] =& $row;
            }
            //unset($row[$idName]); 
            //unset($row[$pidName]);
        }
        return $tree;
    }
    
    function getAdjacencyListParentsID($sTable, $nID, $nDepth=0, $sIDField = 'id', $sPIDField = 'pid')
    {
          if(!$nDepth) $nDepth = 20;
          
          $fields = array(); 
          $joins  = array(); 
          $where  = ''; 
          for($i=0; $i<$nDepth; $i++) 
          {
            // Алиасы для таблицы.
            $alias     =       't'.sprintf("%02d", $i);
            $aliasPrev = $i>0? 't'.sprintf("%02d", $i-1) : null;
            // Список полей для алиаса.
            $fields[] = "$alias.$sPIDField";

            // LEFT JOIN только для второй и далее таблиц!
            if ($aliasPrev)
              $joins[] = "LEFT JOIN $sTable $alias ON ($alias.$sIDField = $aliasPrev.$sPIDField)";
            else
              $joins[] = "$sTable $alias";
            // Условие поиска.
            if(!$i) {
                $where = "$alias.$sIDField = $nID"; 
            }
          }
          
          $sql = 'SELECT '. join(', ', $fields).' FROM '.join(' ', $joins).' WHERE '.$where;
          $tmp = $this->one_array($sql, PDO::FETCH_NUM); 
          $res = array();
          if(!empty($tmp))
          {            
              foreach($tmp as $k=>$v) {
                  if(!empty($v)){
                      $res[] = $v;
                  } else break;
              }
          }
          return $res;
    }
    
    function getAdjacencyListChildrensID($sTable, $nID, $nDepth=0, $sIDField = 'id', $sPIDField = 'pid')
    {
          if(!$nDepth) $nDepth = 20;
          
          $fields = array(); 
          $joins  = array(); 
          $where  = ''; 
          for($i=0; $i<$nDepth; $i++) 
          {
            // Алиасы для таблицы.
            $alias     =       't'.sprintf("%02d", $i);
            $aliasPrev = $i>0? 't'.sprintf("%02d", $i-1) : null;
            // Список полей для алиаса.
            $fields[] = "$alias.$sIDField";

            // LEFT JOIN только для второй и далее таблиц!
            if ($aliasPrev)
              $joins[] = "LEFT JOIN $sTable $alias ON ($alias.$sPIDField = $aliasPrev.$sIDField)";
            else
              $joins[] = "$sTable $alias";
            // Условие поиска.
            if(!$i) {
                $where = "$alias.$sIDField = $nID"; 
            }
          }
          
          $sql = 'SELECT '. join(', ', $fields).' FROM '.join(' ', $joins).' WHERE '.$where;
          $tmp = $this->select($sql, PDO::FETCH_NUM);
          $res = array();
          if(!empty($tmp))
          {            
              foreach($tmp as $t) {
                  foreach($t as $k=>$v) {
                      if(!empty($v)){
                          if($v!=$nID) $res[] = $v;
                      } else break;
                  }
              }
          }
          return $res;
    }
    
    //PDO     
    
    /**
    * @param string sql запрос
    * @return PDOStatement объект
    */
    private function query($sql)
    {
        try {   
            $res = $this->_pdo->query($sql);
            if(!$res) $this->error("SELECT failed in SQL: $sql", 2);
            return $res;
        } 
        catch(Exception $e) {
            $this->error($e->getMessage(), 2);
            return 0;  
        }
    }
    
    public function getPdoInstance()
    {
        return $this->_pdo;
    }
    
    public function getPersistent()
    {
        return $this->_pdo->getAttribute(PDO::ATTR_PERSISTENT);
    }

    public function setPersistent($value)
    {
        return $this->_pdo->setAttribute(PDO::ATTR_PERSISTENT,$value);
    }

    public function getDriverName()
    {
        static $cache;
        if(!isset($cache))
            $cache = mb_strtolower($this->_pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        return $cache;
    }
    
    public function isMySQL()
    {
        switch($this->getDriverName())
        {
            case 'mysqli':
            case 'mysql': {
                return true;
            } break;
        }
        return false;
    }

    public function setAttribute($name,$value)
    {
        if($this->_pdo instanceof PDO)
            $this->_pdo->setAttribute($name, $value);
        else
            $this->_attributes[$name]=$value;
    }  
    
    function prepareFulltextQuery($sQ, $sFields = false)
    {
        // избавляемся от знаков *
        $sQ = str_replace('*', '', $sQ);  
        
        // избавляемся от знаков -
        if(strpos($sQ, '-')!==false) { 
            $sQ = str_replace('-', ' ', $sQ);
            $sQ = ereg_replace(" +", " ", $sQ); // и от двойных пробелов
            $sQ = rtrim($sQ); // и от последнего пробела
        }
        
        // добавляем к каждому слову *
        if(strpos($sQ, ' ')!==false) {
            $aWords = explode(' ', $sQ);
            $sQ = '';
            foreach($aWords as $v) {
                if(strlen($v) > 2) {
                    $sQ .= $v.'* ';
                }
            }
        } else {
            $sQ .= '*';
        }
        
        if($sFields!==false) {
            return " MATCH($sFields) AGAINST (". $this->str2sql( "$sQ" ) ." IN BOOLEAN MODE) ";
        } else {
            return $sQ;
        }
    }
  
} 