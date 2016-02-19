<?php

class Domain extends AbstractModel
{

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
		global $db;

		if(!$result = $db->query("SELECT COUNT(`".DBC_USERS_ID."`) FROM `".DBT_USERS."` WHERE `".DBC_USERS_DOMAIN."` = '{$this->getDomain()}'")){
			dbError($db->error);
		}

		return $result->fetch_array(MYSQLI_NUM)[0];
	}


	/**
	 * @return int
	 */
	public function countRedirects()
	{
		global $db;

		if(!$result = $db->query("SELECT COUNT(`".DBC_ALIASES_ID."`) FROM `".DBT_ALIASES."` WHERE `".DBC_ALIASES_SOURCE."` LIKE '%@{$this->getDomain()}%'")){
			dbError($db->error);
		}

		return $result->fetch_array(MYSQLI_NUM)[0];
	}
}
