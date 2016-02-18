<?php

class User
{
	const ROLE_USER = 'user';
	const ROLE_ADMIN = 'admin';

	/**
	 * @var int|string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var string
	 */
	private $domain;

	/**
	 * @var int
	 */
	private $mailboxLimit = 0;

	/**
	 * @var string
	 */
	private $role;


	/**
	 * User constructor.
	 *
	 * @param array $userData
	 */
	function __construct($userData)
	{
		$this->id = $userData[DBC_USERS_ID];
		$this->username = $userData[DBC_USERS_USERNAME];
		$this->domain = $userData[DBC_USERS_DOMAIN];
		$this->role = static::getRoleByEmail($this->getEmail());

		if(defined('DBC_USERS_MAILBOXLIMIT')){
			$this->mailboxLimit = $userData[DBC_USERS_MAILBOXLIMIT];
		}
	}


	/**
	 * @return int|string
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}


	/**
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}


	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->username.'@'.$this->domain;
	}


	/**
	 * @return int
	 */
	public function getMailboxLimit()
	{
		return $this->mailboxLimit;
	}


	/**
	 * @return string
	 */
	public function getRole()
	{
		return $this->role;
	}


	/**
	 * @param string $email
	 *
	 * @return string
	 */
	private static function getRoleByEmail($email)
	{
		global $admins;

		if(in_array($email, $admins)){
			return static::ROLE_ADMIN;
		}

		return static::ROLE_USER;
	}
}
