<?php

class User extends AbstractModel
{

	/**
	 * @inheritdoc
	 */
	public static $table = DBT_USERS;

	/**
	 * @inheritdoc
	 */
	public static $idAttribute = DBC_USERS_ID;


	const ROLE_USER = 'user';
	const ROLE_ADMIN = 'admin';


	/**
	 * @var AbstractRedirect
	 */
	protected $conflictingRedirect = null;


	/**
	 * @inheritdoc
	 */
	protected function setupDbMapping($childMapping = array())
	{
		$thisMappings = array(
			'username' => DBC_USERS_USERNAME,
			'domain' => DBC_USERS_DOMAIN,
			'password_hash' => DBC_USERS_PASSWORD,
		);

		if(defined('DBC_USERS_MAILBOXLIMIT')){
			$thisMappings['mailboxLimit'] = DBC_USERS_MAILBOXLIMIT;
		}

		return array_replace(
			parent::setupDbMapping($thisMappings),
			$childMapping
		);
	}


	/**
	 * @inheritdoc
	 */
	protected function __construct($data)
	{
		parent::__construct($data);

		$this->setUsername($data[DBC_USERS_USERNAME]);
		$this->setDomain($data[DBC_USERS_DOMAIN]);
		$this->setPasswordHash($data[DBC_USERS_PASSWORD]);
		$this->setMailboxLimit(defined('DBC_USERS_MAILBOXLIMIT') ? intval($data[DBC_USERS_MAILBOXLIMIT]) : 0);

		$this->setAttribute('role', static::getRoleByEmail($this->getEmail()));
	}


	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->getAttribute('username');
	}


	/**
	 * @param string $value
	 */
	public function setUsername($value)
	{
		$this->setAttribute('username', strtolower($value));
	}


	/**
	 * @return string
	 */
	public function getDomain()
	{
		return $this->getAttribute('domain');
	}


	/**
	 * @param string $value
	 */
	public function setDomain($value)
	{
		$this->setAttribute('domain', strtolower($value));
	}


	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->getUsername().'@'.$this->getDomain();
	}


	/**
	 * @return string
	 */
	public function getPasswordHash()
	{
		return $this->getAttribute('password_hash');
	}


	/**
	 * @param string $value
	 */
	public function setPasswordHash($value)
	{
		$this->setAttribute('password_hash', $value);
	}


	/**
	 * @return int
	 */
	public function getMailboxLimit()
	{
		return $this->getAttribute('mailboxLimit');
	}


	/**
	 * @param int $value
	 */
	public function setMailboxLimit($value)
	{
		$this->setAttribute('mailboxLimit', $value);
	}


	/**
	 * Get mailbox limit default via database default value
	 *
	 * @return int
	 */
	public static function getMailboxLimitDefault()
	{
		global $db;

		if(defined('DBC_USERS_MAILBOXLIMIT')){

			$sql = "SELECT DEFAULT(".DBC_USERS_MAILBOXLIMIT.") FROM `".static::$table."` LIMIT 1";
			if(!$result = $db->query($sql)){
				dbError($db->error, $sql);
			}

			if($result->num_rows === 1){
				$row = $result->fetch_array();

				return intval($row[0]);
			}
		}

		return 0;
	}


	/**
	 * @return string
	 */
	public function getRole()
	{
		return $this->getAttribute('role');
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


	/**
	 * @return AbstractRedirect
	 */
	public function getConflictingRedirect()
	{
		if(is_null($this->conflictingRedirect)){
			$this->conflictingRedirect = AbstractRedirect::findWhereFirst(
				array(DBC_ALIASES_SOURCE, $this->getEmail())
			);
		}

		return $this->conflictingRedirect;
	}


	/**
	 * Change this users password, throws Exception if password is invalid.
	 *
	 * @param string $password
	 * @param string $passwordRepeated
	 *
	 * @throws Exception
	 */
	public function changePassword($password, $passwordRepeated)
	{
		Auth::validateNewPassword($password, $passwordRepeated);

		$passwordHash = Auth::generatePasswordHash($password);

		$this->setPasswordHash($passwordHash);
		$this->save();
	}


	/**
	 * @inheritdoc
	 */
	public static function findAll($orderBy = array(DBC_USERS_DOMAIN, DBC_USERS_USERNAME))
	{
		return parent::findAll($orderBy);
	}


	/**
	 * @param string $email
	 *
	 * @return static|null
	 */
	public static function findByEmail($email)
	{
		$emailInParts = explode("@", $email);
		if(count($emailInParts) !== 2){
			return null;
		}
		$username = $emailInParts[0];
		$domain = $emailInParts[1];

		return static::findWhereFirst(
			array(
				array('username', $username),
				array('domain', $domain)
			)
		);
	}

}
