<?php

abstract class AbstractRedirect extends AbstractModel
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
	 * @var ModelCollection
	 */
	protected $conflictingUsers = null;


	/**
	 * @inheritdoc
	 */
	protected static function initModel()
	{
		if(is_null(static::$attributeDbAttributeMapping)){
			static::$table = Config::get('schema.tables.aliases', 'aliases');
			static::$idAttribute = Config::get('schema.attributes.aliases.id', 'id');

			static::$attributeDbAttributeMapping = array(
				'id' => Config::get('schema.attributes.aliases.id', 'id'),
				'source' => Config::get('schema.attributes.aliases.source', 'source'),
				'destination' => Config::get('schema.attributes.aliases.destination', 'destination'),
				'multi_hash' => Config::get('schema.attributes.aliases.multi_source', 'multi_source'),
			);
		}
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

		$source = stringToEmails($data[static::attr('source')]);
		$destination = stringToEmails($data[static::attr('destination')]);

		if(get_called_class() === 'Alias' || get_called_class() === 'Redirect'){
			$source = $source[0];
		}

		if(get_called_class() === 'Alias' || get_called_class() === 'MultiAlias'){
			$destination = $destination[0];
		}

		$this->setSource($source);
		$this->setDestination($destination);

		if(Config::get('options.enable_multi_source_redirects', false)){
			$this->setMultiHash($data[static::attr('multi_hash')]);
		}
	}


	/**
	 * @inheritdoc
	 */
	public static function create($data)
	{
		if(get_called_class() !== 'AbstractRedirect'){
			return parent::create($data);
		}

		$hasMultipleSources = array_key_exists(static::attr('source'), $data)
			&& strpos($data[static::attr('source')], ',') !== false;

		$hasMultipleDestinations = array_key_exists(static::attr('destination'), $data)
			&& strpos($data[static::attr('destination')], ',') !== false;

		if(Config::get('options.enable_multi_source_redirects', false) && $hasMultipleSources
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
		return $this->getAttribute('multi_hash');
	}


	/**
	 * @param string $value
	 */
	public function setMultiHash($value)
	{
		$this->setAttribute('multi_hash', $value);
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
			if(count($emailParts) === 2){
				$domains[] = $emailParts[1];
			}
		}

		return array_unique($domains);
	}


	/**
	 * @return ModelCollection
	 */
	public function getConflictingUsers()
	{
		if(is_null($this->conflictingUsers)){
			$sources = $this->getSource();

			if(is_string($sources)){
				$sources = array($sources);
			}

			$this->conflictingUsers = new ModelCollection();
			foreach($sources as $source){
				$user = User::findByEmail($source);
				if(!is_null($user)){
					$this->conflictingUsers->add($user);
				}
			}
		}

		return $this->conflictingUsers;
	}


	/**
	 * @param string $template
	 *
	 * @return array|string
	 */
	public function getConflictingMarkedSource($template = "<u>%email%</u>")
	{
		$conflictingUsers = $this->getConflictingUsers();

		$sources = $this->getSource();

		if(is_string($sources)){
			$sources = array($sources);
		}

		foreach($conflictingUsers as $user){
			if(($key = array_search($user->getEmail(), $sources)) !== false){
				$sources[$key] = str_replace('%email%', $sources[$key], $template);
			}
		}

		return $sources;
	}


	/**
	 * @inheritdoc
	 */
	public static function findAll($orderBy = null)
	{
		if(is_null($orderBy)){
			$orderBy = array(static::attr('source'));
		}

		return parent::findAll($orderBy);
	}


	/**
	 * @return string
	 */
	private static function generateRedirectBaseQuery()
	{
		if(Config::get('options.enable_multi_source_redirects', false)){
			return "SELECT r.* FROM (
	SELECT
		GROUP_CONCAT(g.`".static::$idAttribute."` ORDER BY g.`".static::$idAttribute."` SEPARATOR ',') AS `".static::$idAttribute."`,
		GROUP_CONCAT(g.`".static::attr('source')."` SEPARATOR ',') AS `".static::attr('source')."`,
		g.`".static::attr('destination')."`,
		g.`".static::attr('multi_hash')."`
	FROM `".static::$table."` AS g
	WHERE g.`".static::attr('multi_hash')."` IS NOT NULL
	GROUP BY g.`".static::attr('multi_hash')."`
UNION
	SELECT
		s.`".static::$idAttribute."`,
		s.`".static::attr('source')."`,
		s.`".static::attr('destination')."`,
		s.`".static::attr('multi_hash')."`
	FROM `".static::$table."` AS s
	WHERE s.`".static::attr('multi_hash')."` IS NULL
) AS r";
		}
		else{
			return "SELECT * FROM `".static::$table."`";
		}
	}


	public static function findMultiAll($orderBy = null)
	{
		static::initModel();

		if(is_null($orderBy)){
			$orderBy = array(static::attr('source'));
		}

		$sql = static::generateRedirectBaseQuery()
			.Database::helperOrderBy($orderBy);

		return static::findAllRaw($sql);
	}


	public static function findMultiWhere($conditions = array(), $conditionConnector = 'AND', $orderBy = null, $limit = 0)
	{
		$sql = static::generateRedirectBaseQuery()
			.Database::helperWhere($conditions, $conditionConnector)
			.Database::helperOrderBy($orderBy)
			.Database::helperLimit($limit);

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
		static::initModel();

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
