<?php

class User extends AbstractModel
{
	use DomainLimitTrait;

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
	 * @var ModelCollection|AbstractRedirect[]
	 */
	protected $redirects = null;


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
		if(defined('DBC_USERS_MAILBOXLIMIT')){

			$sql = "SELECT DEFAULT(".DBC_USERS_MAILBOXLIMIT.") FROM `".static::$table."` LIMIT 1";

			$result = Database::getInstance()->query($sql);

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
	 * Is user limited by domain limits?
	 *
	 * @return bool
	 */
	public function isDomainLimited()
	{
		global $adminDomainLimits;

		return defined('ADMIN_DOMAIN_LIMITS_ENABLED')
		&& isset($adminDomainLimits) && isset($adminDomainLimits[$this->getEmail()]);
	}


	/**
	 * Get domain limits, returns an empty array if user has no limits or ADMIN_DOMAIN_LIMITS_ENABLED is disabled
	 *
	 * @return array
	 */
	public function getDomainLimits()
	{
		global $adminDomainLimits;

		if($this->isDomainLimited()){
			if(!is_array($adminDomainLimits[$this->getEmail()])){
				throw new InvalidArgumentException('Config value of admin domain limits for email "'.$this->getEmail().'" needs to be of type array.');
			}

			return $adminDomainLimits[$this->getEmail()];
		}

		return array();
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
	 * @return ModelCollection|AbstractRedirect[]
	 */
	public function getRedirects()
	{
		if(is_null($this->redirects)){
			$this->redirects = AbstractRedirect::findMultiWhere(
				array(DBC_ALIASES_DESTINATION, 'LIKE', '%'.$this->getEmail().'%')
			);
		}

		return $this->redirects;
	}


	/**
	 * @return ModelCollection|AbstractRedirect[]
	 */
	public function getAnonymizedRedirects()
	{
		$redirects = $this->getRedirects();

		foreach($redirects as $redirect){
			$emails = $redirect->getDestination();

			if(is_array($emails) && count($emails) > 1){
				$redirect->setDestination(array($this->getEmail(), '&hellip;'));
			}
		}

		return $redirects;
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
