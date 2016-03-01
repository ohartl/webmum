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
	protected static $attributeDbAttributeMapping = null;


	/**
	 * Initialize Model
	 */
	abstract protected static function initModel();


	/**
	 * Get mapped db attribute
	 *
	 * @param string $name
	 * @return string
	 */
	public static function attr($name)
	{
		static::initModel();

		if(isset(static::$attributeDbAttributeMapping[$name])){
			return static::$attributeDbAttributeMapping[$name];
		}

		return false;
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
		static::initModel();

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

		if(!is_null($model)){
			$model->save();

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

		while($row = $result->fetch_assoc()){
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
	 * Find all models by raw sql
	 *
	 * @param $sql
	 * @param null|string $useSpecificModel
	 *
	 * @return ModelCollection|static[]
	 */
	public static function findAllRaw($sql, $useSpecificModel = null)
	{
		$result = Database::getInstance()->query($sql);

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
		$result = Database::getInstance()->query($sql);

		if(is_null($useSpecificModel)){
			return static::createFromDbResult($result);
		}
		elseif(class_exists($useSpecificModel)){
			return call_user_func_array(array($useSpecificModel, 'createFromDbResult'), array($result));
		}

		return null;
	}


	/**
	 * Find models by a condition
	 *
	 * @param array $conditions see helperConditionArray
	 * @param string $conditionConnector see helperConditionArray
	 * @param array|null $orderBy
	 * @param int $limit see helperLimit
	 *
	 * @return ModelCollection|static[]|AbstractModel|null
	 */
	public static function findWhere($conditions = array(), $conditionConnector = 'AND', $orderBy = null, $limit = 0)
	{
		static::initModel();

		$result = Database::getInstance()->select(static::$table, $conditions, $conditionConnector, $orderBy, $limit);

		if($limit === 1){
			return static::createFromDbResult($result);
		}

		return static::createMultipleFromDbResult($result);
	}


	/**
	 * Find all models
	 *
	 * @param array|null $orderBy see helperOrderBy
	 *
	 * @return ModelCollection|static[]
	 */
	public static function findAll($orderBy = null)
	{
		return static::findWhere(array(), 'AND', $orderBy);
	}


	/**
	 * Find first model matching a condition
	 *
	 * @param array $conditions see helperConditionArray
	 * @param string $conditionConnector see helperConditionArray
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
		static::initModel();

		return static::findWhereFirst(array(static::$idAttribute, $id));
	}


	/**
	 * Save model data to database
	 */
	public function save()
	{
		$data = $this->preSave($this->data);

		$values = array();
		foreach(static::$attributeDbAttributeMapping as $attribute => $sqlAttribute){
			if($sqlAttribute === static::$idAttribute){
				continue;
			}

			$values[$sqlAttribute] = $data[$attribute];
		}

		if(is_null($this->getId())){
			$insertId = Database::getInstance()->insert(static::$table, $values);

			$this->setId(intval($insertId));
		}
		else{
			Database::getInstance()->update(static::$table, $values, array(static::$idAttribute, $this->getId()));
		}
	}

	/**
	 * Delete model from database
	 *
	 * @return bool
	 */
	public function delete()
	{
		if(!is_null($this->getId())){

			Database::getInstance()->delete(static::$table, static::$idAttribute, $this->getId());

			return true;
		}

		return false;
	}


	/**
	 * Count models by a condition
	 *
	 * @param array $conditions see helperConditionArray
	 * @param string $conditionConnector see helperConditionArray
	 *
	 * @return int
	 */
	public static function countWhere($conditions = array(), $conditionConnector = 'AND')
	{
		static::initModel();

		return Database::getInstance()->count(static::$table, static::$idAttribute, $conditions, $conditionConnector);
	}


	/**
	 * Count all models
	 *
	 * @return int
	 */
	public static function count()
	{
		return static::countWhere();
	}

}
