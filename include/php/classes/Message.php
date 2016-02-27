<?php

/**
 * Created by PhpStorm.
 * User: oliver
 * Date: 26.02.2016
 * Time: 22:47
 */
class Message
{

	const TYPE_FAIL = 'fail';
	const TYPE_ERROR = 'fail';
	const TYPE_WARNING = 'warning';
	const TYPE_SUCCESS = 'success';


	/**
	 * Holds all messages
	 *
	 * @var array
	 */
	protected static $messages = array();


	/**
	 * Add a new message
	 *
	 * @param string $type Supported types: success, fail, info
	 * @param string $text
	 */
	public static function add($type, $text)
	{
		if(!in_array($type, array(static::TYPE_FAIL, static::TYPE_ERROR, static::TYPE_WARNING, static::TYPE_SUCCESS))){
			throw new InvalidArgumentException;
		}

		static::$messages[] = array(
			'type' => $type,
			'message' => $text,
		);
	}


	/**
	 * Add a new success message
	 *
	 * @param string $text
	 */
	public static function fail($text)
	{
		static::add(static::TYPE_FAIL, $text);
	}


	/**
	 * Add a new success message
	 *
	 * @param string $text
	 */
	public static function error($text)
	{
		static::add(static::TYPE_ERROR, $text);
	}


	/**
	 * Add a new success message
	 *
	 * @param string $text
	 */
	public static function warning($text)
	{
		static::add(static::TYPE_WARNING, $text);
	}


	/**
	 * Add a new success message
	 *
	 * @param string $text
	 */
	public static function success($text)
	{
		static::add(static::TYPE_SUCCESS, $text);
	}


	/**
	 * Render all messages
	 *
	 * @param null|string $type null = render all
	 *
	 * @return string
	 */
	public static function render($type = null)
	{
		$out = '';

		if(count(static::$messages) > 0){
			$out .= '<div class="notifications">';
			foreach(static::$messages as $message){
				if(is_null($type) || $type == $message['type']){
					$out .= '<div class="notification notification-'.$message['type'].'">'.$message['message'].'</div>';
				}
			}
			$out .= '</div>';
		}

		return $out;
	}

}