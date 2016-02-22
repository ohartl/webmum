<?php

abstract class AbstractModel
{

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
	protected $attributeDbAttributeMapping = array();


	/**
	 * Setup db attribute mapping
	 *
	 * @param array $childMapping
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	protected function setupDbMapping($childMapping = array())
	{
		return array_replace(
			array(
				'id' => static::$idAttribute,
			),
			$childMapping
		);
	}


	/**
	 * Format or do other things before saving
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function preSave($data)
	{
		return $data;
	}


	/**
	 * Hold all data from a model
	 *
	 * @var mixed
	 */
	protected $data = array();


	/**
	 * Constructor.
	 *
	 * @param array $data
	 */
	protected function __construct($data)
	{
		$this->attributeDbAttributeMapping = $this->setupDbMapping();

		if(isset($data[static::$idAttribute])){
			$id = is_numeric($data[static::$idAttribute]) && strpos($data[static::$idAttribute], ',') === false
				? intval($data[static::$idAttribute])
				: $data[static::$idAttribute];

			$this->setId($id);
		}
	}


	/**
	 * Create a model from data
	 *
	 * @param array $data
	 *
	 * @return static|null The Model
	 */
	public static function create($data)
	{
		if(count($data) > 0){
			return new static($data);
		}

		return null;
	}


	/**
	 * Create a model collection from data
	 *
	 * @param array $multiData
	 *
	 * @return ModelCollection|static[]
	 */
	public static function createMultiple($multiData = array())
	{
		$collection = new ModelCollection();

		foreach($multiData as $data){
			$model = static::create($data);

			if(!is_null($model)){
				if(is_null($model->getId())){
					$collection->add($model);
				}
				else{
					$collection->add($model, $model->getId());
				}
			}
		}

		return $collection;
	}


	/**
	 * @see create
	 *
	 * @param array $data
	 *
	 * @return AbstractModel|null
	 */
	public static function createAndSave($data)
	{
		$model = static::create($data);

		if(!is_null($model) && $model->save()){
			return $model;
		}

		return null;
	}


	/**
	 * @see createMultiple
	 *
	 * @param array $multiData
	 *
	 * @return ModelCollection|static[]
	 */
	public static function createMultipleAndSave($multiData = array())
	{
		$collection = new ModelCollection();

		foreach($multiData as $data){
			$model = static::createAndSave($data);

			if(!is_null($model)){
				$collection->add($model);
			}
		}

		return $collection;
	}


	/**
	 * Create a model from mysqli result
	 *
	 * @param mysqli_result $result
	 *
	 * @return static|null
	 */
	public static function createFromDbResult($result)
	{
		if($result->num_rows === 0){
			return null;
		}

		return static::create($result->fetch_assoc());
	}


	/**
	 * Create a model collection from mysqli result
	 *
	 * @param mysqli_result $result
	 *
	 * @return ModelCollection|static[]
	 */
	public static function createMultipleFromDbResult($result)
	{
		$rows = array();

		while($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}

		return static::createMultiple($rows);
	}


	/**
	 * @param string $attribute
	 * @param mixed $value
	 */
	public function setAttribute($attribute, $value)
	{
		$this->data[$attribute] = $value;
	}


	/**
	 * @param string $attribute
	 *
	 * @return mixed|null
	 */
	public function getAttribute($attribute)
	{
		if(isset($this->data[$attribute])){
			if(is_array($this->data[$attribute])){
				return array_map('strip_tags', $this->data[$attribute]);
			}
			else{
				return strip_tags($this->data[$attribute]);
			}
		}

		return null;
	}


	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->getAttribute('id');
	}


	/**
	 * @param mixed $value
	 */
	protected function setId($value)
	{
		$this->setAttribute('id', $value);
	}


	/**
	 * @param array $attributes
	 *
	 * @return string
	 */
	protected static function sqlHelperAttributeList($attributes)
	{
		$sql = "%s";
		$values = array();

		$keywords = array('AS', 'ASC', 'DESC');

		foreach($attributes as $val){
			if(!is_array($val)){
				// raw
				$values[] = $val;
				continue;
			}

			switch(count($val)){
				case 1:
					$values[] = "`{$val[0]}`";
					break;
				case 2:
					if(in_array(strtoupper($val[1]), $keywords)){
						$values[] = "`{$val[0]}` {$val[1]}";
					}
					else{
						$values[] = "`{$val[0]}`.`{$val[1]}`";
					}
					break;
				case 3:
					if(in_array(strtoupper($val[1]), $keywords)){
						$values[] = "`{$val[0]}` {$val[1]} `{$val[2]}`";
					}
					elseif(in_array(strtoupper($val[2]), $keywords)){
						$values[] = "`{$val[0]}`.`{$val[1]}` {$val[2]}";
					}
					break;
				case 4:
					if(in_array(strtoupper($val[1]), $keywords)){
						$values[] = "`{$val[0]}` {$val[1]} `{$val[2]}`.`{$val[3]}`";
					}
					elseif(in_array(strtoupper($val[2]), $keywords)){
						$values[] = "`{$val[0]}`.`{$val[1]}` {$val[2]} `{$val[3]}`";
					}
					else{
						$values[] = "`{$val[0]}`.`{$val[1]}` `{$val[2]}`.`{$val[3]}`";
					}
					break;
				case 5:
					if(in_array(strtoupper($val[2]), $keywords)){
						$values[] = "`{$val[0]}`.`{$val[1]}` {$val[2]} `{$val[3]}`.`{$val[4]}`";
					}
					break;
			}
		}

		return sprintf($sql, implode(', ', $values));
	}


	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	protected static function sqlHelperValue($value)
	{
		global $db;
		if(is_null($value) || (is_string($value) && strtoupper($value) === 'NULL')){
			return "NULL";
		}
		elseif(is_array($value)){
			return static::sqlHelperValueList($value);
		}

		return "'{$db->escape_string($value)}'";
	}


	/**
	 * @param array $values
	 *
	 * @return string
	 */
	protected static function sqlHelperValueList($values)
	{
		$sql = "(%s)";
		$sqlValues = array();

		foreach($values as $val){
			$sqlValues[] = static::sqlHelperValue($val);
		}

		return sprintf($sql, implode(', ', $sqlValues));
	}


	/**
	 * @param array $conditions
	 *     array('attr', '=', '3') => "`attr` = '3'"
	 *     array(
	 *         array('`attr` = '3') (raw SQL) => `attr` = '3'
	 *         array('attr', 3) => `attr` = '3'
	 *         array('attr', '=', '3') => `attr` = '3'
	 *         array('attr', '<=', 3) => `attr` <= '3'
	 *         array('attr', 'LIKE', '%asd') => `attr` LIKE '%asd'
	 *         array('attr', 'IS', null) => `attr` IS NULL
	 *         array('attr', 'IS NOT', null) => `attr` IS NOT NULL
	 *     )
	 * @param string $conditionConnector AND, OR
	 *
	 * @return string
	 */
	protected static function sqlHelperConditionList($conditions, $conditionConnector = 'AND')
	{
		$values = array();

		// detect non nested array
		if(count($conditions) > 0 && !is_array($conditions[0])){
			$conditions = array($conditions);
		}

		$conditionConnector = strtoupper($conditionConnector);
		if(in_array($conditionConnector, array('AND', 'OR'))){
			$conditionConnector = " ".$conditionConnector;
		}

		$sql = "`%s` %s %s";

		foreach($conditions as $val){
			switch(count($val)){
				case 1:
					// raw
					$values[] = $val;
					break;
				case 2:
					$v = static::sqlHelperValue($val[1]);
					$values[] = sprintf($sql, $val[0], "=", $v);
					break;
				case 3:
					$v = static::sqlHelperValue($val[2]);
					$values[] = sprintf($sql, $val[0], strtoupper($val[1]), $v);
					break;
			}
		}

		return implode($conditionConnector." ", $values);
	}


	/**
	 * @param array $conditions
	 * @param string $conditionConnector AND, OR
	 *
	 * @return string
	 */
	protected static function sqlHelperWhere($conditions, $conditionConnector = 'AND')
	{
		if(count($conditions) > 0){
			$sql = " WHERE %s";

			return sprintf($sql, static::sqlHelperConditionList($conditions, $conditionConnector));
		}

		return "";
	}

	/**
	 * @param array|null $orderBy Examples below:
	 *        null => ""
	 *        array() => ""
	 *        array('attr1' => 'asc', 'attr2' => 'desc') => " ORDER BY `attr1` ASC, `attr2` DESC "
	 *        array('attr1') => " ORDER BY `attr1` ASC "
	 *
	 * @return string
	 */
	protected static function sqlHelperOrderBy($orderBy = null)
	{
		if(!is_null($orderBy) && count($orderBy) > 0){
			$sql = " ORDER BY %s";

			$values = array();
			foreach($orderBy as $key => $val){
				if(is_int($key)){
					$values[] = array($val);
				}
				else{
					$values[] = array($key, strtoupper($val));
				}
			}

			return sprintf($sql, static::sqlHelperAttributeList($values));
		}

		return "";
	}


	/**
	 * @param int|array $limit
	 *        0 => ""
	 *        3 => " LIMIT 3 "
	 *        array(3, 4) => " LIMIT 3,4 "
	 *
	 * @return string
	 */
	protected static function sqlHelperLimit($limit = 0)
	{
		$sql = " LIMIT %s";

		if(is_string($limit) || (is_int($limit) && $limit > 0)){
			return sprintf($sql, $limit);
		}
		elseif(is_array($limit) && count($limit) == 2){
			return sprintf($sql, $limit[0].",".$limit[1]);
		}

		return "";
	}


	/**
	 * Find all models by raw sql
	 *
	 * @param $sql
	 * @param null|string $useSpecificModel
	 *
	 * @return ModelCollection|static[]
	 */
	public static function findAllRaw($sql, $useSpecificModel = null)
	{
		global $db;

		if(!$result = $db->query($sql)){
			dbError($db->error, $sql);
		}

		if(is_null($useSpecificModel)){
			return static::createMultipleFromDbResult($result);
		}
		elseif(class_exists($useSpecificModel)){
			return call_user_func_array(array($useSpecificModel, 'createMultipleFromDbResult'), array($result));
		}

		return new ModelCollection();
	}


	/**
	 * Find a model by raw sql
	 *
	 * @param $sql
	 * @param null|string $useSpecificModel
	 *
	 * @return AbstractModel
	 */
	public static function findRaw($sql, $useSpecificModel = null)
	{
		global $db;

		if(!$result = $db->query($sql)){
			dbError($db->error, $sql);
		}

		if(is_null($useSpecificModel)){
			return static::createFromDbResult($result);
		}
		elseif(class_exists($useSpecificModel)){
			return call_user_func_array(array($useSpecificModel, 'createFromDbResult'), array($result));
		}

		return null;
	}


	/**
	 * Find all models
	 *
	 * @param array|null $orderBy see sqlHelperOrderBy
	 *
	 * @return ModelCollection|static[]
	 */
	public static function findAll($orderBy = null)
	{
		$sql = "SELECT * FROM `".static::$table."`"
			.static::sqlHelperOrderBy($orderBy);

		return static::findAllRaw($sql);
	}


	/**
	 * Find models by a condition
	 *
	 * @param array $conditions see sqlHelperConditionArray
	 * @param string $conditionConnector see sqlHelperConditionArray
	 * @param array|null $orderBy
	 * @param int $limit see sqlHelperLimit
	 *
	 * @return ModelCollection|static[]|AbstractModel|null
	 */
	public static function findWhere($conditions = array(), $conditionConnector = 'AND', $orderBy = null, $limit = 0)
	{
		$sql = "SELECT * FROM `".static::$table."`"
			.static::sqlHelperWhere($conditions, $conditionConnector)
			.static::sqlHelperOrderBy($orderBy)
			.static::sqlHelperLimit($limit);

		if($limit === 1){
			return static::findRaw($sql);
		}

		return static::findAllRaw($sql);
	}


	/**
	 * Find first model matching a condition
	 *
	 * @param array $conditions see sqlHelperConditionArray
	 * @param string $conditionConnector see sqlHelperConditionArray
	 * @param array|null $orderBy
	 *
	 * @return AbstractModel|null
	 */
	public static function findWhereFirst($conditions = array(), $conditionConnector = 'AND', $orderBy = null)
	{
		return static::findWhere($conditions, $conditionConnector, $orderBy, 1);
	}


	/**
	 * Find a model by id
	 *
	 * @param mixed $id
	 *
	 * @return AbstractModel|null
	 */
	public static function find($id)
	{
		return static::findWhereFirst(array(static::$idAttribute, $id));
	}


	/**
	 * Save model data to database
	 *
	 * @return bool
	 */
	public function save()
	{
		global $db;

		$data = $this->preSave($this->data);

		if(is_null($this->getId())){
			// insert

			$attributes = array();
			$values = array();

			foreach($this->attributeDbAttributeMapping as $attribute => $sqlAttribute){
				if($sqlAttribute === static::$idAttribute){
					continue;
				}

				$attributes[] = array($sqlAttribute);
				$values[] = $data[$attribute];
			}

			$sql = "INSERT INTO `".static::$table."`"
				." (".static::sqlHelperAttributeList($attributes).")"
				." VALUES ".static::sqlHelperValueList($values);
		}
		else{
			// update

			$values = array();
			foreach($this->attributeDbAttributeMapping as $attribute => $sqlAttribute){
				if($sqlAttribute === static::$idAttribute){
					continue;
				}

				$values[] = array($sqlAttribute, '=', $data[$attribute]);
			}

			$sql = "UPDATE `".static::$table."`"
				." SET ".static::sqlHelperConditionList($values, ',')
				.static::sqlHelperWhere(array(static::$idAttribute, $this->getId()));
		}


		if($stmt = $db->prepare($sql)){
			if($stmt->execute()){
				if(is_null($this->getId())){
					$this->setId(intval($db->insert_id));
				}

				return true;
			}
			else{
				dbError($db->error, $sql);
			}
		}

		return false;
	}

	/**
	 * Delete model from database
	 *
	 * @return bool
	 */
	public function delete()
	{
		global $db;

		if(!is_null($this->getId())){
			$sql = "DELETE FROM `".static::$table."`"
				.static::sqlHelperWhere(array(static::$idAttribute, $this->getId()));

			if($stmt = $db->prepare($sql)){
				if($stmt->execute()){
					return true;
				}
				else{
					dbError($db->error, $sql);
				}
			}
		}

		return false;
	}

}
