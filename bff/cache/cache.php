<?php

abstract class Cache
{
	public $keyPrefix = '9874378764';
    
	public function init()
	{ 
        
	}
    
    /**
     * @param string имя драйвера кеша
     * @param mixed false или array()
     * @return Cache объект
     */         
    static function &singleton($driver='apc', $init = true)
    {
        static $instances = array();

        $signature = serialize(array($driver,$init));
        if (empty($instances[$signature])) {
            $instances[$signature] = Cache::factory($driver);
            if($init!==false) {
                $instances[$signature]->init($init);
            }
        }

        return $instances[$signature];
    }

    /**
     * Доступные драйвера: apc, eaccelerator, lite
     * @return Cache объект
     */                                           
    static function factory($driver)
    {                                
        if(empty($driver) || $driver == 'none'){
            return new Cache();
        }
        
        if(file_exists(PATH_CORE.'cache/cache.'.$driver.'.php')) {
            include_once PATH_CORE.'cache/cache.'.$driver.'.php';
        }

        $class = 'C'.$driver.'Cache'; 
        
        return new $class();
    }
    
	protected function generateUniqueKey($key)
	{
		return md5($this->keyPrefix.$key);
	}

	/**
     * Возвращает значение из кеша по специальному ключу.
	 * @param string ключ
	 * @return mixed значение, false - если в кеше нет записи по заданному ключу, истек срок хранения или изменилась зависимость.
	 */
	public function get($id = null)
	{
		if( ( $value = $this->getValue( $this->generateUniqueKey($id) ) ) !== false)
		{
			$data = unserialize($value);
			if(!is_array($data)) {
				return false;
            }
			if( !($data[1] instanceof ICacheDependency) || !$data[1]->getHasChanged() )
			{
				return $data[0];
			}
		}
		return false;
	}

	/**
	 * Возвращает несколько значений из кеша по ключам.
	 * @param array список ключей
	 * @return array список значений: пары (ключ, значение).
	 * false - если записи нет или истек срок её храниения
	 */
	public function mget($ids)
	{
		$uniqueIDs=array();
		$results=array();
		foreach($ids as $id)
		{
			$uniqueIDs[$id] = $this->generateUniqueKey($id);
			$results[$id] = false;
		}
        
		$values = $this->getValues($uniqueIDs);
		foreach($uniqueIDs as $id=>$uniqueID)
		{
			if(!isset($values[$uniqueID]))
				continue;
                
			$data = unserialize($values[$uniqueID]);
			if(is_array($data) && (!($data[1] instanceof ICacheDependency) || !$data[1]->getHasChanged()))
			{                                                                                       
				$results[$id] = $data[0];
			}
		}
		return $results;
	}

	/**
     * Сохраняем значение по ключу.
     * Заменяем(значение и срок хранения) если значение под данным ключем уже существует.
	 * @param string ключ
	 * @param mixed значение
	 * @param integer срок хранения в секундах. 0 - без ограничения срока хранения.
	 * @param ICacheDependency зависимость записи в кеше. Если зависимость меняется, запись помечается как "невалидная".
	 * @return boolean true - значение успешно сохранено в кеш, false - нет.
	 */
	public function set($id, $value, $expire=0, $dependency=null)
	{
		if($dependency!==null)
			$dependency->evaluateDependency();  
            
		$data = array($value, $dependency);
        
		return $this->setValue($this->generateUniqueKey($id), serialize($data), $expire);
	}

	/**
     * Сохраняем значение по ключу, если записи с таким ключем еще нет.
	 * @param string ключ
	 * @param mixed значение
	 * @param integer срок хранения в секундах. 0 - без ограничения срока хранения.
     * @param ICacheDependency зависимость записи в кеше. Если зависимость меняется, запись помечается как "невалидная".
     * @return boolean true - значение успешно сохранено в кеш, false - нет.
	 */
	public function add($id, $value, $expire=0, $dependency=null)
	{
		if($dependency!==null)
			$dependency->evaluateDependency();

		$data = array($value, $dependency);

		return $this->addValue($this->generateUniqueKey($id), serialize($data), $expire);
	}

	/**
	 * Удаляем запись из кеша по ключу.
	 * @param string ключ
	 * @return boolean если не возникло ошибок в процессе удаления
	 */
	public function delete($id)
	{                                                                                      
		return $this->deleteValue($this->generateUniqueKey($id));
	}

	/**
     * Удаляем все записи из кеша.
     * Для имплементации в классах наследниках.
	 */
	public function flush()
	{                                                                       
	}

	/**
	 * Возвращает значение из кеша по ключу.  
     * Для имплементации в классах наследниках. 
	 * @param string уникальный ключ
	 * @return mixed значение, false - если в кеше нет записи по заданному ключу, истек срок хранения.        
	 */
	protected function getValue($key)
	{
	}

	/**
     * Возвращает несколько значений из кеша по ключам.
     * Для имплементации в классах наследниках. 
     * @param array список ключей
     * @return array список значений: ключ=>значение.
	 */
	protected function getValues($keys)
	{
		$results=array();
		foreach($keys as $key) {
			$results[$key] = $this->getValue($key);
        }
		return $results;
	}

	/**
     * Сохраняем значение по ключу.  
	 * Для имплементации в классах наследниках.
	 * @param string уникальный ключ
	 * @param string значение
	 * @param integer срок хранения в секундах. 0 - без ограничения срока хранения. 
	 * @return boolean true - значение успешно сохранено в кеш, false - нет.     
	 */
	protected function setValue($key, $value, $expire)
	{
	}

	/**
     * Сохраняем значение по ключу, если записи с таким ключем еще нет.
     * Для имплементации в классах наследниках.
     * @param string ключ
     * @param mixed значение
     * @param integer срок хранения в секундах. 0 - без ограничения срока хранения.                                       
     * @return boolean true - значение успешно сохранено в кеш, false - нет.
	 */
	protected function addValue($key, $value, $expire)
	{
	}

	/**
     * Удаляем запись из кеша по ключу.
     * Для имплементации в классах наследниках.
     * @param string ключ
     * @return boolean если не возникло ошибок в процессе удаления
	 */
	protected function deleteValue($key)
	{
	}

}
