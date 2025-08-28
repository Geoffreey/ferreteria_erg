<?php
/*
| -----------------------------------------------------
| PRODUCT NAME: 	MODERN POS
| -----------------------------------------------------
| AUTHOR:			geoffdeep.pw
| -----------------------------------------------------
| EMAIL:			info@geoffdeep.pw
| -----------------------------------------------------
| COPYRIGHT:		RESERVED BY geoffdeep
| -----------------------------------------------------
| WEBSITE:			http://geoffdeep.pw
| -----------------------------------------------------
*/
final class Registry 
{
	private $data = array();

	public function get($key) 
	{
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}

	public function set($key, $value) 
	{
		$this->data[$key] = $value;
	}

	public function has($key) 
	{
		return isset($this->data[$key]);
	}
}