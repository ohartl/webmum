<?php

class Message
{

	const TYPE_FAIL = 'fail';
	const TYPE_ERROR = 'fail';
	const TYPE_WARNING = 'warning';
	const TYPE_SUCCESS = 'success';


	/**
	 * @var Message
	 */
	protected static $instance;


	/**
	 * Holds all messages
	 *
	 * @var array
	 */
	protected $messages = array();


	/**
	 * @codeCoverageIgnore
	 */
	private function __construct()
	{
	}


	/**
	 * @codeCoverageIgnore
	 */
	private function __clone()
	{
	}


	/**
	 * @return Message
	 *
	 * @codeCoverageIgnore
	 */
	public static function getInstance()
	{
		if(is_null(static::$instance)){
			static::$instance = new static();
		}

		return static::$instance;
	}


	/**
	 * Add a new message
	 *
	 * @param string $type Supported types: success, fail, info
	 * @param string $text
	 */
	public function add($type, $text)
	{
		if(!in_array($type, array(static::TYPE_FAIL, static::TYPE_ERROR, static::TYPE_WARNING, static::TYPE_SUCCESS))){
			throw new InvalidArgumentException;
		}

		$this->messages[] = array(
			'type' => $type,
			'message' => $text,
		);
	}


	/**
	 * Add a new success message
	 *
	 * @param string $text
	 */
	public function fail($text)
	{
		$this->add(static::TYPE_FAIL, $text);
	}


	/**
	 * Add a new success message
	 *
	 * @param string $text
	 */
	public function error($text)
	{
		$this->add(static::TYPE_ERROR, $text);
	}


	/**
	 * Add a new success message
	 *
	 * @param string $text
	 */
	public function warning($text)
	{
		$this->add(static::TYPE_WARNING, $text);
	}


	/**
	 * Add a new success message
	 *
	 * @param string $text
	 */
	public function success($text)
	{
		$this->add(static::TYPE_SUCCESS, $text);
	}


	/**
	 * Render all messages
	 *
	 * @param null|string $type null = render all
	 *
	 * @return string
	 */
	public function render($type = null)
	{
		$out = '';

		if(count($this->messages) > 0){
			$out .= '<div class="notifications">';

			foreach($this->messages as $message){
				if(is_null($type) || $type == $message['type']){
					$out .= '<div class="notification notification-'.$message['type'].'">'.$message['message'].'</div>';
				}
			}

			$out .= '</div>';
		}

		return $out;
	}

}