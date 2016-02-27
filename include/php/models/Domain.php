<?php

class Domain extends AbstractModel
{
	use DomainLimitTrait;

	/**
	 * @inheritdoc
	 */
	public static $table = DBT_DOMAINS;

	/**
	 * @inheritdoc
	 */
	public static $idAttribute = DBC_DOMAINS_ID;


	/**
	 * @inheritdoc
	 */
	protected function setupDbMapping($childMapping = array())
	{
		return array_replace(
			parent::setupDbMapping(
				array(
					'domain' => DBC_DOMAINS_DOMAIN,
				)
			),
			$childMapping
		);
	}


	/**
	 * @inheritdoc
	 */
	protected function __construct($data)
	{
		parent::__construct($data);

		$this->setDomain($data[DBC_DOMAINS_DOMAIN]);
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
	 * @return int
	 */
	public function countUsers()
	{
		return User::countWhere(array(DBC_USERS_DOMAIN, $this->getDomain()));
	}


	/**
	 * @return int
	 */
	public function countRedirects()
	{
		return AbstractRedirect::countWhere(array(DBC_ALIASES_SOURCE, 'LIKE', "%@{$this->getDomain()}%"));
	}

}
