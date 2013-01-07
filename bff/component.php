<?php
  
abstract class Component
{
    var $errors   = null;
    var $security = null;
    var $db       = null;
    var $sm       = null;
    var $input    = null;
    
    protected $sett = array();
    private   $_initialized = false;  
    
    public function init()
    {
        if($this->getIsInitialized())
            return false;
            
        global $oDb, $oSm, $oSecurity;
        
        $this->errors   = &Errors::i();
        $this->security = &$oSecurity;
        $this->db       = &$oDb;
        $this->sm       = &$oSm;
        $this->input    = &CInputCleaner::i();
        
        $this->setIsInitialized();
    }
    
    public function getIsInitialized() 
    {
        return $this->_initialized;
    }
    
    public function setIsInitialized() 
    {
        $this->_initialized = true;
    }
    
    public function getSettings($mKeys)
    {
        if(is_array($mKeys))
        {
            //настройки по ключам
            $res = array();
            foreach($mKeys as $key) {
                if(isset($this->sett[$key])) {
                    $res[$key] = $this->sett[$key];
                }
            }
            return $res;
        } elseif(is_string($mKeys)) {
            //настройка по ключу
            if(isset($this->sett[$mKeys])) {
                return $this->sett[$mKeys];
            }
        }
        //все настройки
        return $this->sett;
    }
    
    public function setSettings($mKey, $mValue = false)
    {
        if(is_array($mKey)) {                     
            foreach($mKey as $key=>$value) {
                if(property_exists($this,$key)) {
                    $this->$key = $value;
                }
                $this->sett[$key] = $value;
            }
        } else {
            if(property_exists($this,$mKey)) {
                $this->$mKey = $mValue;
            }
            $this->sett[$mKey] = $mValue;
        }
    }

    
}