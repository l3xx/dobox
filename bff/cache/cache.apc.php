<?php

class CApcCache extends Cache
{
	public function init()
	{
		parent::init();
        
		if(!func::extensionLoaded('apc')) {
            throw new Exception('CApcCache requires PHP apc extension to be loaded.');
        }
	}

	protected function getValue($key)
	{
		return apc_fetch($key);
	}

	protected function getValues($keys)
	{
		return apc_fetch($keys);
	}

	protected function setValue($key,$value,$expire)
	{
		return apc_store($key,$value,$expire);
	}

	protected function addValue($key,$value,$expire)
	{
		return apc_add($key,$value,$expire);
	}

	protected function deleteValue($key)
	{
		return apc_delete($key);
	}

	public function flush()
	{
		return apc_clear_cache('user');
	}
}
