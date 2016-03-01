<?php

class User extends AbstractModel
{
	use DomainLimitTrait;


	/**
	 * Db table for find methods
	 *
	 * @var string
	 */
	public static $table;


	/**
	 * Db id attribute for find methods
	 *
	 * @var string
	 */
	public static $idAttribute;


	/**
	 * Mapping model attributes and database attributes for saving
	 *
	 * @var array
	 */
	protected static $attributeDbAttributeMapping = null;


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
	protected static function initModel()
	{
		if(is_null(static::$attributeDbAttributeMapping)){
			static::$table = Config::get('schema.tables.users', 'users');
			static::$idAttribute = Config::get('schema.attributes.users.id', 'id');

			static::$attributeDbAttributeMapping = array(
				'id' => Config::get('schema.attributes.users.id', 'id'),
				'username' => Config::get('schema.attributes.users.username', 'username'),
				'domain' => Config::get('schema.attributes.users.domain', 'domain'),
				'password_hash' => Config::get('schema.attributes.users.password', 'password'),
				'mailbox_limit' => Config::get('schema.attributes.users.mailbox_limit'),
			);
		}
	}


	/**
	 * @inheritdoc
	 */
	protected function __construct($data)
	{
		parent::__construct($data);

		$this->setUsername($data[static::attr('username')]);
		$this->setDomain($data[static::attr('domain')]);
		$this->setPasswordHash($data[static::attr('password_hash')]);
		$this->setMailboxLimit(Config::get('options.enable_mailbox_limits', false)
			? intval($data[static::attr('mailbox_limit')])
			: 0
		);

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
		return $this->getAttribute('mailbox_limit');
	}


	/**
	 * @param int $value
	 */
	public function setMailboxLimit($value)
	{
		$this->setAttribute('mailbox_limit', $value);
	}


	/**
	 * Get mailbox limit default via database default value
	 *
	 * @return int
	 */
	public static function getMailboxLimitDefault()
	{
		static::initModel();

		if(Config::get('options.enable_mailbox_limits', false)){

			$sql = "SELECT DEFAULT(".static::attr('mailbox_limit').") FROM `".static::$table."` LIMIT 1";

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
		if(in_array($email, Config::get('admins', array()))){
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
		$adminDomainLimits = Config::get('admin_domain_limits', array());

		return Config::get('options.enable_admin_domain_limits', false)
		&& is_array($adminDomainLimits) && isset($adminDomainLimits[$this->getEmail()]);
	}


	/**
	 * Get domain limits, returns an empty array if user has no limits or ADMIN_DOMAIN_LIMITS_ENABLED is disabled
	 *
	 * @return array
	 */
	public function getDomainLimits()
	{
		if($this->isDomainLimited()){
			$adminDomainLimits = Config::get('admin_domain_limits', array());

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
				array(AbstractRedirect::attr('source'), $this->getEmail())
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
				array(AbstractRedirect::attr('destination'), 'LIKE', '%'.$this->getEmail().'%')
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
	public static function findAll($orderBy = null)
	{
		if(is_null($orderBy)){
			$orderBy = array(static::attr('domain'), static::attr('username'));
		}

		return parent::findAll($orderBy);
	}


	/**
	 * @param string $email
	 *
	 * @return User|null
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
				array(static::attr('username'), $username),
				array(static::attr('domain'), $domain)
			)
		);
	}

}
