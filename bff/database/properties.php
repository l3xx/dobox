<?php

/**
*   >> Create Tables

    DROP TABLE IF EXISTS `keyhome_properties`;
    CREATE TABLE IF NOT EXISTS `keyhome_properties` (
      `id` int(11) NOT NULL auto_increment,
      `module` varchar(100) NOT NULL,
      `category` varchar(100) NOT NULL,
      `name` char(50) NOT NULL,
      `fieldname` char(50) NOT NULL,
      PRIMARY KEY (`ID`)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;

    DROP TABLE IF EXISTS `keyhome_propertiesval`;
    CREATE TABLE IF NOT EXISTS `keyhome_propertiesval` (
      `id` int(11) NOT NULL auto_increment,
      `prop_id` int(11) NOT NULL,
      `val` char(30) NOT NULL,
      `name` char(50) NOT NULL,
      `number` int(11) default '1',
      `is_default` tinyint(1) default '0',
      `is_enabled` tinyint(1) default '1', 
      PRIMARY KEY (`ID`),
      FOREIGN KEY (`prop_id`) REFERENCES `keyhome_properties` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;

*  module -- название модуля
*  @version: 1.475
*/              

class PropertiesBase {

    public $moduleName = '';
    public $tableName = '';
    public $categoryProperty = '';
    public $refTableName = '';
    private $db = null;
    
	function PropertiesBase()
	{        
        global $oDb;
        //$this->db =&$oDb;
	}
    
    function setParams($moduleName = '', $tableName='properties', $categoryPropertyName = 'category')
    {
       $this->tableName = $tableName;
       $this->moduleName = $moduleName;
       $this->categoryProperty  = $categoryPropertyName; 
    }
    
    /** получение значений свойств по ID свойства
    * @param int propertyID: ID свойства
    * @param string defaultValue: значение свойства по умолчанию
    * @param array aExceptValuesID: ID значений не входящих в выборку  
    */
    function getOptionByPropertyID($nPropertyID, $defaultValue = '', $aExceptValuesID = array())
    {
        global $oDb;

        $sAdd2Query = '';
        if(count($aExceptValuesID)>0)
            $sAdd2Query = ' AND PV.id NOT IN ('.implode(',', $aExceptValuesID).')';
        
        $sQuery = 'SELECT PV.val as value, PV.name as name, PV.is_default as is_default
                  FROM '.DB_PREFIX.$this->tableName.' P, '.DB_PREFIX.$this->tableName.'val PV
                  WHERE P.module="'.$this->moduleName.'" AND P.id = '.$nPropertyID.' AND 
                        P.id = PV.prop_id AND PV.is_enabled = 1 '.$sAdd2Query.'
                  ORDER BY PV.number';
        $aOptions = $oDb->select($sQuery);
        
        $aResult = array();
        foreach($aOptions as $k=>$v)
        {
            $aResult['options'][$v['value']]= $v['name'];
            if(empty($defaultValue) && $v['is_default'] == '1')
                $aResult['default'] = $v['value'];
        }
        
        if(!empty($defaultValue))
            $aResult['default'] = $defaultValue;
        
        return $aResult;
    }

    /** получение <options> значений свойства по Fieldname свойства
    * @param string fieldname: Fieldname свойства
    * @param string defaultValue: значение свойства по умолчанию  
    */
    function getOptionsByPropertyFieldname($fieldname, $defaultValue = '')
    {
        global $oDb;
        
        $sQuery = 'SELECT PV.val as value, PV.name as name, PV.is_default as is_default
                  FROM '.DB_PREFIX.$this->tableName.' P, '.DB_PREFIX.$this->tableName.'val PV
                  WHERE P.module="'.$this->moduleName.'" AND P.fieldname = "'.$fieldname.'" AND P.id = PV.prop_id AND PV.is_enabled = 1
                  ORDER BY PV.number';
        $opts = $oDb->select($sQuery);
        
        foreach($opts as $k=>$v)
        {
            $result['options'][$v['value']]= $v['name'];
            if(empty($defaultValue) && $v['is_default'] == '1')
                $result['default'] = $v['value'];
        }
        
        if(!empty($defaultValue))
            $result['default'] = $defaultValue;
        
        return $result;
    }

    /** Получения название значения свойства по Fieldname свойства 
    * @param string fieldname: Fieldname свойства
    * @param string selectedValue: выбранное значение свойства 
    */
    function getPropertyValueName($fieldname, $selectedValue = '')
    {
        global $oDb;
        
        $add2query = '';
        if(empty($selectedValue))
            $add2query = ' AND PV.is_default = 1';
        else
            $add2query = ' AND PV.val = "'.$selectedValue.'"';

        $sQuery = 'SELECT PV.name as name
                  FROM '.DB_PREFIX.$this->tableName.' P, '.DB_PREFIX.$this->tableName.'val PV
                  WHERE P.module="'.$this->moduleName.'" AND P.fieldname = "'.$fieldname.'" AND P.id = PV.prop_id AND PV.is_enabled = 1'.$add2query;
        $name = $oDb->one_data($sQuery);
        //если не найдено название значения, тогда возвращаем по умолчанию
        if(empty($name) && !empty($selectedValue))
        {
            return '?';
        }
        return $name;
    }
    
    /** получение свойств категории
    * @param mixed category: идентификатор категории
    * @param string defaultValue: значение свойства по умолчанию  
    */
    function getOptionsByCategory($category)
    {
        global $oDb;
        
        $sQuery = 'SELECT P.fieldname as field, PV.val as value, PV.name as name, PV.is_default as is_default
                  FROM '.DB_PREFIX.$this->tableName.' P, '.DB_PREFIX.$this->tableName.'val PV
                  WHERE P.module="'.$this->moduleName.'" AND P.category LIKE "%'.$category.'%" AND P.id = PV.prop_id AND PV.is_enabled = 1
                  ORDER BY PV.number';
        $opts = $oDb->select($sQuery); 
        
        foreach($opts as $k=>$v)
        {
            $result[$v['field']]['options'][$v['value']]= $v['name'];
            if($v['is_default'] == '1')
               $result[$v['field']]['default'] = $v['value'];
        }                       
        
        return $result;
    }

    /** получение свойств модуля
    * @param string module: идентификатор модуля
    */
    function getOptionsByModule($module='', $bDefineDefaults = true)
    {
        global $oDb;
        
        if(empty($module))
              $module = $this->moduleName;
        
        $sQuery = 'SELECT P.fieldname as field, PV.val as value, PV.name as name, PV.is_default as is_default
                  FROM '.DB_PREFIX.$this->tableName.' P, '.DB_PREFIX.$this->tableName.'val PV
                  WHERE P.module="'.$module.'" AND P.id = PV.prop_id AND PV.is_enabled = 1
                  ORDER BY PV.number';
        $opts = $oDb->select($sQuery); 
        
        foreach($opts as $k=>$v)
        {
            $result[$v['field']]['options'][$v['value']]= $v['name'];
            if($v['is_default'] == '1')
               $result[$v['field']]['default'] = $v['value'];
        }                       
        
        if($bDefineDefaults)
        {
            $result['yesno']['options'] = array('1'=> 'Да', '0'=> 'Нет');
            $result['exists']['options'] = array('1'=> 'Есть', '0'=> 'Нет');
        }
        
        return $result;
    }
    
    //-----------------------------------------------------------------------------------------------------
    // Property (свойства)
    
    /** добавление свойства
    * @param mixed category: идентификатор категории
    * @param string name: название свойства 
    */
    function addProperty($sCategory, $sName, $sFieldName)
    {
        global $oDb;
        $sQuery = 'INSERT INTO '.DB_PREFIX.$this->tableName.'
                  ('.$this->categoryProperty.', module, name, fieldname) 
                  VALUES ('.$oDb->str2sql($sCategory).', '.$oDb->str2sql($this->moduleName).', '.$oDb->str2sql($sName).', '.$oDb->str2sql($sFieldName).')';
        $oDb->execute($sQuery);
        return $oDb->insert_id(DB_PREFIX.$this->tableName,'id');
    }

    /** удаление свойства
    * @param int propertyID: ID свойства 
    */
    function deleteProperty($propertyID)
    {
        global $oDb;
        return $oDb->execute('DELETE FROM '.DB_PREFIX.$this->tableName.' WHERE id='.$propertyID);
    }
 
    /** изменение принадлежности к группе и названия свойства 
    * @param int propertyID: ID свойства
    * @param mixed category: идентификатор категории
    * @param string name: название свойства 
    */
    function updateProperty($propertyID, $category, $name, $fieldname)
    {
        global $oDb;
                                                      
        $sQuery = 'UPDATE '.DB_PREFIX.$this->tableName.' 
                  SET '.$this->categoryProperty.'="'.$category.'", module="'.$this->moduleName.'", name="'.$name.'", fieldname="'.$fieldname.'" 
                  WHERE id='.$propertyID;
        $oDb->execute($sQuery);
    }

    /** получение свойствa
    * @param int propertyID: ID свойства  
    */
    function getProperty($propertyID)
    {
        global $oDb;
        
        $sQuery = 'SELECT * 
                  FROM '.DB_PREFIX.$this->tableName.'
                  WHERE id='.$propertyID.' LIMIT 1';
        return $oDb->one_array($sQuery);
    }

    /** получение массива свойств
    * @param mixed category: идентификатор категории
    * @param string sqlLimit: лимит выборки
    * @param string orderBy: сортировка выборки   
    */
    function getProperties($category='', $sqlLimit = '', $orderBy = 'name asc')
    {
        global $oDb; 
        
        $add_2query  = 'WHERE P.module="'.$this->moduleName.'"';
        if(!empty($category))
            $add_2query .=' AND '.$this->categoryProperty.' = '.$oDb->str2sql($category).' ';

        $add_2query.=' GROUP BY 1';
            
        if(!empty($orderBy))
            $add_2query .=' ORDER BY '.$orderBy; 
                
        if(!empty($sqlLimit))      
            $add_2query .=$sqlLimit;
        
        $sQuery = 'SELECT P.*, count(PV.id) as values_count  
                  FROM '.DB_PREFIX.$this->tableName.' as P LEFT JOIN '.DB_PREFIX.$this->tableName.'val as PV
                  ON P.id = PV.prop_id
                  '.$add_2query;
        return $oDb->select($sQuery);
    }
    
    /** получение кол-ва свойств (категории)
    * @param mixed category: идентификатор категории   
    */
    function getPropertiesCount($category='')
    {
        global $oDb; 
        
        $add_2query  = '';
        if(!empty($category))
            $add_2query .=' AND '.$this->categoryProperty.' = '.$oDb->str2sql($category).' ';
        
        $sQuery = 'SELECT count(*) 
                  FROM '.DB_PREFIX.$this->tableName.'
                  WHERE module="'.$this->moduleName.'" AND id!=0 '.$add_2query;
        return $oDb->one_data($sQuery);
    } 
    
    //-----------------------------------------------------------------------------------------------------
    // PropertyValue (значения)
    
    /** добавление значений свойства
    * @param int propertyID: ID свойства
    * @param string val: значение свойства
    * @param string name: название свойства 
    */
    function addPropertyValue($propertyID, $val, $name)
    {
        global $oDb;
        
        $oDb->execute('INSERT INTO '.DB_PREFIX.$this->tableName.'val
                  (prop_id, val, name) VALUES ('.$propertyID.', '.$oDb->str2sql($val).', '.$oDb->str2sql($name).')');
        $nID = $oDb->insert_id(DB_PREFIX.$this->tableName.'val','id'); 

        $oDb->execute('UPDATE '.DB_PREFIX.$this->tableName.'val SET number = id WHERE id='.$nID);
        
        return $nID;        
    }

    /** изменение свойств значений свойства :)
    * @param int valueID: ID значения
    * @param string val: новое название свойства
    * @param string name: название свойства 
    */
    function updatePropertyValue($valueID, $val, $name)
    {
        global $oDb;
        
        $sQuery = 'UPDATE '.DB_PREFIX.$this->tableName.'val 
                  SET val="'.$val.'", name="'.$name.'" 
                  WHERE id = '.$valueID;
        $oDb->execute($sQuery);
    }

     /** установка значений свойства по умолчанию
     * @param int propertyID: ID свойства 
     * @param int valueID: ID значения по умолчанию 
     */
    function setPropertyValueDefault($propertyID, $valueID)
    {
        global $oDb;

        $sQuery = 'UPDATE '.DB_PREFIX.$this->tableName.'val 
                  SET is_default=0 
                  WHERE prop_id='.$propertyID;
        $oDb->execute($sQuery);     
        
        $sQuery = 'UPDATE '.DB_PREFIX.$this->tableName.'val 
                  SET is_default=1 
                  WHERE id='.$valueID.' AND prop_id='.$propertyID;
        $oDb->execute($sQuery);
    }
    
     /** инверсия "enabled" состояния значения свойства
     * @param int valueID: ID значения 
     */
    function togglePropertyValueEnabled($valueID)
    {
        global $oDb;

        $sQuery = 'UPDATE '.DB_PREFIX.$this->tableName.'val 
                  SET is_enabled=if(is_enabled,0,1) 
                  WHERE id='.$valueID;
        $oDb->execute($sQuery);
    }

    /** получение значения свойства
    * @param int propertyID: ID свойства
    * @param int valueID: ID значения   
    */
    function getPropertyValue($propertyID, $valueID)
    {
        global $oDb;
        
        $sQuery = 'SELECT PV.* 
                  FROM '.DB_PREFIX.$this->tableName.' P, '.DB_PREFIX.$this->tableName.'val PV
                  WHERE P.module="'.$this->moduleName.'" AND P.id='.$propertyID.' AND 
                        P.id = PV.prop_id AND PV.id='.$valueID.'
                  ORDER BY PV.number
                  LIMIT 1';
        return $oDb->one_array($sQuery);
    }
        
    /** получение значений свойств
    * @param int propertyID: ID свойства
    * @param array aExceptValuesID: ID значений не входящих в выборку   
    */
    function getPropertyValues($propertyID, $aExceptValuesID = array())
    {
        global $oDb;
        
        $sAdd2Query = '';
        if(count($aExceptValuesID)>0)
            $sAdd2Query = ' AND PV.id NOT IN ('.implode(',', $aExceptValuesID).')';
        
        $sQuery = 'SELECT PV.* 
                  FROM '.DB_PREFIX.$this->tableName.' P, '.DB_PREFIX.$this->tableName.'val PV
                  WHERE P.module="'.$this->moduleName.'" AND P.id='.$propertyID.' AND P.id = PV.prop_id '.$sAdd2Query.'
                  ORDER BY PV.number';
        return $oDb->select($sQuery);
    }

     /** получение значений свойствa
    * @param sring fieldname: названию поля свойства в БД  
    */
    function getPropertyValuesBy($fieldname, $category)
    {
        global $oDb;
        
        $sQuery = 'SELECT PV.* 
                  FROM '.DB_PREFIX.$this->tableName.' P, '.DB_PREFIX.$this->tableName.'val PV                                                             
                  WHERE P.module="'.$this->moduleName.'" AND P.fieldname = '.$oDb->str2sql($fieldname).' AND  P.'.$this->categoryProperty.' LIKE '.$oDb->str2sql('%'.$category.'%').' AND P.id = PV.prop_id
                  ORDER BY PV.number';
        $arr = $oDb->select($sQuery);
        $result = array();
        
        for($i=0; $i<count($arr); $i++)
            $result[ $arr[$i]['val'] ] = $arr[$i]['name'];
        
        return $result;
    }
    
    /** удаление значений свойства
    * @param int valueID: ID значения  
    */
    function deletePropertyValue($valueID)
    {
        global $oDb;  
        return $oDb->execute('DELETE FROM '.DB_PREFIX.$this->tableName.'val WHERE id='.$valueID);
    }

    /** удаление всех значений свойства
    * @param int propertyID: ID свойства  
    */
    function deletePropertyValues($propertyID)
    {
        global $oDb; 
        $sQuery  = 'DELETE FROM '.DB_PREFIX.$this->tableName.'val WHERE prop_id='.$propertyID;
        $oDb->execute($sQuery);
    }
    
    /** изменение позиции значения свойства (вниз)
    * @param int propertyID: ID свойства
    * @param int valueID: ID значения  
    */
    function movePropertyValueDown($propertyID, $valueID)
    {
        global $oDb;

        $sQuery = 'SELECT * FROM '.DB_PREFIX.$this->tableName.'val 
                  WHERE id='.$valueID.' AND prop_id='.$propertyID;
        $value = $oDb->one_array($sQuery);
        
        $sQuery = 'SELECT * FROM '.DB_PREFIX.$this->tableName.'val 
                  WHERE number>'.$value['number'].' AND prop_id='.$propertyID.' ORDER BY number ASC LIMIT 1';
        $value_higher = $oDb->one_array($sQuery);

        if(!$value_higher)
            return;

        $sQuery = 'UPDATE '.DB_PREFIX.$this->tableName.'val SET number='.$value['number'].' 
                  WHERE id='.$value_higher['id'].' AND prop_id='.$propertyID;
        $oDb->execute($sQuery);

        $sQuery = 'UPDATE '.DB_PREFIX.$this->tableName.'val SET number='.$value_higher['number'].' 
                  WHERE id='.$value['id'].' AND prop_id='.$propertyID;
        $oDb->execute($sQuery);

        return;
    }

    /** изменение позиции значения свойства (вверх)
    * @param int propertyID: ID свойства
    * @param int valueID: ID значения  
    */  
    function movePropertyValueUp($propertyID, $valueID)
    {
        global $oDb;

        $sQuery = 'SELECT * FROM '.DB_PREFIX.$this->tableName.'val 
                  WHERE id='.$valueID.' AND prop_id='.$propertyID;
        $value = $oDb->one_array($sQuery);
        
        $sQuery = 'SELECT * FROM '.DB_PREFIX.$this->tableName.'val 
                  WHERE number<'.$value['number'].' AND prop_id='.$propertyID.' ORDER BY number DESC LIMIT 1';
        $value_lower = $oDb->one_array($sQuery);

        if(!$value_lower)
            return;

        $sQuery = 'UPDATE '.DB_PREFIX.$this->tableName.'val 
                  SET number='.$value['number'].' 
                  WHERE id='.$value_lower['id'].' AND prop_id='.$propertyID;
        $oDb->execute($sQuery);

        $sQuery = 'UPDATE '.DB_PREFIX.$this->tableName.'val 
                  SET number='.$value_lower['number'].'
                  WHERE id='.$value['id'].' AND prop_id='.$propertyID;
        $oDb->execute($sQuery);
    }
    
    //------------------------------------------------------------------------
    
    function refSetTable($sRefTableName)
    {
        $this->refTableName = $sRefTableName;
    }
    
    function refGetProperyValueCount($sPropertyRefName, $sValue)
    {
        global $oDb;
        return $oDb->one_data('SELECT count(*) FROM '.DB_PREFIX.$this->refTableName.'
                   WHERE '.$sPropertyRefName.' = '.$oDb->str2sql($sValue).' ');
    }

    function refGetNextPropertyValue($nPropertyID, $nNextPropertyID)
    {
        $nPropertyID  = intval($nPropertyID);
        $nNextValueID = intval($nNextValueID);
        
        // if next-value-id is null, then set prop-ref-name to null
        // else update to next-value
        if($nNextValueID>0)
        {
           //get next value params
           $aNextValueData = $this->getPropertyValue($nPropertyID, $nNextValueID);
           return $aNextValueData['val'];
        }
        else
        {
            return 'NULL';
        }
    }
                     
    function refUpdateToPropertyValue($sPropertyRefName, $sPrevPropertyValue, $sNextPropertyValue)
    {   
        global $oDb;
        $oDb->execute('UPDATE '.DB_PREFIX.$this->refTableName.' SET '.$sPropertyRefName.' = '.$oDb->str2sql($sNextPropertyValue).'  WHERE '.$sPropertyRefName.' = '.$oDb->str2sql($sPrevPropertyValue).' ');  
    }
}  
