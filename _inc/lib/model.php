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
abstract class Model 
{
	protected $registry;
	protected $hooks;

	public function __construct($registry) 
	{
		$this->registry = $registry;
		$this->hooks = registry()->get('hooks');
	}

	public function __get($key) 
	{
		return $this->registry->get($key);
	}

	public function __set($key, $value) 
	{
		$this->registry->set($key, $value);
	}
}