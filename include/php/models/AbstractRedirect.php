<?php

abstract class AbstractRedirect extends AbstractModel
{
	use DomainLimitTrait;

	/**
	 * @inheritdoc
	 */
	public static $table = DBT_ALIASES;

	/**
	 * @inheritdoc
	 */
	public static $idAttribute = DBC_ALIASES_ID;


	/**
	 * @inheritdoc
	 */
	protected function setupDbMapping($childMapping = array())
	{
		$thisMapping = array(
			'source' => DBC_ALIASES_SOURCE,
			'destination' => DBC_ALIASES_DESTINATION,
		);

		if(defined('DBC_ALIASES_MULTI_SOURCE')){
			$thisMapping['multiHash'] = DBC_ALIASES_MULTI_SOURCE;
		}

		return array_replace(
			parent::setupDbMapping($thisMapping),
			$childMapping
		);
	}


	/**
	 * @inheritdoc
	 */
	protected function preSave($data)
	{
		$data = parent::preSave($data);

		$data['source'] = emailsToString($data['source']);
		$data['destination'] = emailsToString($data['destination']);

		return $data;
	}


	/**
	 * @inheritdoc
	 */
	protected function __construct($data)
	{
		parent::__construct($data);

		$source = stringToEmails($data[DBC_ALIASES_SOURCE]);
		$destination = stringToEmails($data[DBC_ALIASES_DESTINATION]);

		if(static::class === Alias::class || static::class === Redirect::class){
			$source = $source[0];
		}

		if(static::class === Alias::class || static::class === MultiAlias::class){
			$destination = $destination[0];
		}

		$this->setSource($source);
		$this->setDestination($destination);

		if(defined('DBC_ALIASES_MULTI_SOURCE')){
			$this->setMultiHash($data[DBC_ALIASES_MULTI_SOURCE]);
		}
	}


	/**
	 * @inheritdoc
	 */
	public static function create($data)
	{
		if(static::class !== AbstractRedirect::class){
			return parent::create($data);
		}

		$hasMultipleSources = array_key_exists(DBC_ALIASES_SOURCE, $data)
			&& strpos($data[DBC_ALIASES_SOURCE], ',') !== false;

		$hasMultipleDestinations = array_key_exists(DBC_ALIASES_DESTINATION, $data)
			&& strpos($data[DBC_ALIASES_DESTINATION], ',') !== false;

		if(defined('DBC_ALIASES_MULTI_SOURCE') && $hasMultipleSources
		){
			if($hasMultipleDestinations){
				return MultiRedirect::create($data);
			}
			else{
				return MultiAlias::create($data);
			}
		}
		else{
			if($hasMultipleDestinations){
				return Redirect::create($data);
			}
			else{
				return Alias::create($data);
			}
		}
	}


	/**
	 * @return array|string
	 */
	public function getSource()
	{
		return $this->getAttribute('source');
	}


	/**
	 * @param string|array $value
	 */
	public function setSource($value)
	{
		if(is_array($value)){
			$this->setAttribute('source', array_map('strtolower', $value));
		}
		else{
			$this->setAttribute('source', strtolower($value));
		}
	}


	/**
	 * @return array|string
	 */
	public function getDestination()
	{
		return $this->getAttribute('destination');
	}


	/**
	 * @param string|array $value
	 */
	public function setDestination($value)
	{
		if(is_array($value)){
			$this->setAttribute('destination', array_map('strtolower', $value));
		}
		else{
			$this->setAttribute('destination', strtolower($value));
		}
	}

	/**
	 * @return string
	 */
	public function getMultiHash()
	{
		return $this->getAttribute('multiHash');
	}


	/**
	 * @param string $value
	 */
	public function setMultiHash($value)
	{
		$this->setAttribute('multiHash', $value);
	}


	/**
	 * @return array
	 */
	protected function getDomain()
	{
		$sources = $this->getSource();
		if(is_string($sources)){
			$sources = array($sources);
		}

		$domains = array();
		foreach($sources as $source){
			$emailParts = explode('@', $source);
			if(count($emailParts) === 2) {
				$domains[] = $emailParts[1];
			}
		}

		return array_unique($domains);
	}


	/**
	 * @inheritdoc
	 */
	public static function findAll($orderBy = array(DBC_ALIASES_SOURCE))
	{
		return parent::findAll($orderBy);
	}


	/**
	 * @return string
	 */
	private static function generateRedirectBaseQuery()
	{
		if(defined('DBC_ALIASES_MULTI_SOURCE')){
			return "SELECT r.* FROM (
	SELECT
		GROUP_CONCAT(g.`".static::$idAttribute."` ORDER BY g.`".static::$idAttribute."` SEPARATOR ',') AS `".static::$idAttribute."`,
		GROUP_CONCAT(g.`".DBC_ALIASES_SOURCE."` SEPARATOR ',') AS `".DBC_ALIASES_SOURCE."`,
		g.`".DBC_ALIASES_DESTINATION."`,
		g.`".DBC_ALIASES_MULTI_SOURCE."`
	FROM `".static::$table."` AS g
	WHERE g.`".DBC_ALIASES_MULTI_SOURCE."` IS NOT NULL
	GROUP BY g.`".DBC_ALIASES_MULTI_SOURCE."`
UNION
	SELECT
		s.`".DBC_ALIASES_ID."`,
		s.`".DBC_ALIASES_SOURCE."`,
		s.`".DBC_ALIASES_DESTINATION."`,
		s.`".DBC_ALIASES_MULTI_SOURCE."`
	FROM `".static::$table."` AS s
	WHERE s.`".DBC_ALIASES_MULTI_SOURCE."` IS NULL
) AS r";
		}
		else{
			return "SELECT * FROM `".static::$table."`";
		}
	}


	public static function findMultiAll($orderBy = array(DBC_ALIASES_SOURCE))
	{
		$sql = static::generateRedirectBaseQuery()
			.static::sqlHelperOrderBy($orderBy);

		return static::findAllRaw($sql);
	}


	public static function findMultiWhere($conditions = array(), $conditionConnector = 'AND', $orderBy = null, $limit = 0)
	{
		$sql = static::generateRedirectBaseQuery()
			.static::sqlHelperWhere($conditions, $conditionConnector)
			.static::sqlHelperOrderBy($orderBy)
			.static::sqlHelperLimit($limit);

		if($limit === 1){
			return static::findRaw($sql);
		}

		return static::findAllRaw($sql);
	}


	public static function findMultiWhereFirst($conditions = array(), $conditionConnector = 'AND', $orderBy = null)
	{
		return static::findMultiWhere($conditions, $conditionConnector, $orderBy, 1);
	}


	public static function findMulti($id)
	{
		return static::findMultiWhereFirst(array(static::$idAttribute, $id));
	}


	/**
	 * @param array|User|null $limitedBy
	 *
	 * @return ModelCollection|static[]
	 */
	public static function getMultiByLimitedDomains($limitedBy = null)
	{
		return static::filterModelCollectionByLimitedDomains(static::findMultiAll(), $limitedBy);
	}
}
