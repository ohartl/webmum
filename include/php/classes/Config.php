<?php

class Config
{

	/**
	 * @var array
	 */
	protected static $config = array();


	private function __construct()
	{
	}


	private function __clone()
	{
	}


	/**
	 * @param array $configArray
	 */
	public static function init($configArray)
	{
		static::set(null, $configArray);
	}


	/**
	 * Set a config value using "dot" notation.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	public static function set($key, $value)
	{
		if(is_null($key)) return static::$config = $value;

		$keys = explode('.', $key);

		$array =& static::$config;
		while(count($keys) > 1){
			$key = array_shift($keys);

			if(!isset($array[$key]) || !is_array($array[$key])){
				$array[$key] = array();
			}

			$array =& $array[$key];
		}

		$array[array_shift($keys)] = $value;

		return $array;
	}


	/**
	 * Get a config value using "dot" notation.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get($key, $default = null)
	{
		if(is_null($key)) return static::$config;

		if(isset(static::$config[$key])) return static::$config[$key];

		$pointer = static::$config;
		foreach(explode('.', $key) as $segment){
			if(!is_array($pointer) || !array_key_exists($segment, $pointer)){
				return $default;
			}

			$pointer = $pointer[$segment];
		}

		return $pointer;
	}


	/**
	 * Check if a config value exists using "dot" notation.
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function has($key)
	{
		if(empty(static::$config) || is_null($key)) return false;

		if(array_key_exists($key, static::$config)) return true;

		$pointer = static::$config;
		foreach(explode('.', $key) as $segment){
			if(!is_array($pointer) || !array_key_exists($segment, $pointer)){
				return false;
			}

			$pointer = $pointer[$segment];
		}

		return true;
	}

}