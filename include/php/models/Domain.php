<?php

class Domain extends AbstractModel
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


	/**
	 * @inheritdoc
	 */
	protected static function initModel()
	{
		if(is_null(static::$attributeDbAttributeMapping)){
			static::$table = Config::get('schema.tables.domains', 'domains');
			static::$idAttribute = Config::get('schema.attributes.domains.id', 'id');

			static::$attributeDbAttributeMapping = array(
				'id' => Config::get('schema.attributes.domains.id', 'id'),
				'domain' => Config::get('schema.attributes.domains.domain', 'domain'),
			);
		}
	}


	/**
	 * @inheritdoc
	 */
	protected function __construct($data)
	{
		parent::__construct($data);

		$this->setDomain($data[static::attr('domain')]);
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
		return User::countWhere(
			array(User::attr('domain'), $this->getDomain())
		);
	}


	/**
	 * @return int
	 */
	public function countRedirects()
	{
		return AbstractRedirect::countWhere(
			array(AbstractRedirect::attr('source'), 'LIKE', "%@{$this->getDomain()}%")
		);
	}

}
