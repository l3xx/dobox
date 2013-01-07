<?php

require ('Lite/Lite.php');

class CLiteCache extends Cache_Lite
{
    protected $groupName = 'default';
    
    public function init($aOptions = array(null))
    {
        foreach($aOptions as $key => $value) {
            $this->setOption($key, $value);
        }
    }
    
    public function setGroup($group) 
    {
        $this->groupName = $group;
    }

	public function get($id)
	{
		return parent::get($id, $this->groupName);
	}

	public function set($id, $value, $expire=0)
	{
		return $this->save($value, $id, $this->groupName);
	}

	public function add($id, $value, $expire=0)
	{
		return (false === $this->get($id) ? $this->set($id, $value, $expire) : false);
	}

	public function delete($id)
	{
        return $this->remove($id, $this->groupName, true);
	}

	public function flush()
	{
		$this->clean();
	}
}

