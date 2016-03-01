<?php

class Database
{

	/**
	 * @var Database
	 */
	protected static $instance = null;


	/**
	 * @var mysqli
	 */
	protected $db;


	/**
	 * @var string
	 */
	protected $config;


	/**
	 * @var string
	 */
	protected $lastQuery;


	protected function __construct($host, $user, $password, $database)
	{
		if(!static::isInitialized()){
			$this->config = $database;

			$this->db = new mysqli($host, $user, $password, $database);
			if($this->db->connect_errno > 0){
				$this->db = null;
				die('Unable to connect to database ['.$this->db->connect_error.']');
			}
		}
	}


	protected function __clone()
	{
	}


	/**
	 * @return Database
	 */
	public static function getInstance()
	{
		return static::$instance;
	}


	/**
	 * @param Database $instance
	 */
	protected static function setInstance($instance)
	{
		static::$instance = $instance;
	}


	/**
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 */
	public static function init($host, $user = null, $password = null, $database = null)
	{
		if(!static::isInitialized()){
			if(is_array($host)){
				if(isset($host['host']) && isset($host['user']) && isset($host['password']) && isset($host['database'])){
					$user = $host['user'];
					$password = $host['password'];
					$database = $host['database'];
					$host = $host['host'];
				}
				else{
					throw new InvalidArgumentException;
				}
			}

			static::setInstance(
				new static($host, $user, $password, $database)
			);
		}
	}


	/**
	 * @return bool
	 */
	public static function isInitialized()
	{
		return !is_null(static::$instance);
	}


	/**
	 *
	 */
	public static function mustBeInitialized()
	{
		if(!static::isInitialized()){
			die('Database has not been initialized.');
		}
	}


	/**
	 * Die with error and executed sql query
	 *
	 * @param string $errorMessage
	 * @param string|null $sql
	 */
	public function dieOnDatabaseError($errorMessage, $sql = null)
	{
		die('There was an error running the query ['.$errorMessage.']'.(!is_null($sql) ? ' with statement "'.$sql.'"' : ''));
	}


	/**
	 * Die if query not successful
	 */
	public function mustBeSuccessful()
	{
		if($this->db->errno !== 0){
			$this->dieOnDatabaseError($this->db->error, $this->lastQuery);
		}
	}


	/**
	 * Execute query
	 *
	 * @param string $query
	 *
	 * @return bool|mysqli_result
	 */
	public function query($query)
	{
		$this->lastQuery = $query;

		$result = $this->db->query($query);

		$this->mustBeSuccessful();

		return $result;
	}


	/**
	 * @return mixed
	 */
	public function getInsertId()
	{
		return $this->db->insert_id;
	}


	/**
	 * Escape string
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public function escape($input)
	{
		return $this->db->real_escape_string($input);
	}


	/**
	 * @param string $table
	 * @param array $conditions
	 * @param string $conditionConnector
	 * @param null $orderBy
	 * @param int $limit
	 *
	 * @return bool|mysqli_result
	 */
	public function select($table, $conditions = array(), $conditionConnector = 'AND', $orderBy = null, $limit = 0)
	{
		return $this->query(
			sprintf(
				"SELECT * FROM `%s` %s%s%s",
				$table,
				static::helperWhere($conditions, $conditionConnector),
				static::helperOrderBy($orderBy),
				static::helperLimit($limit)
			)
		);
	}


	/**
	 * Insert into table
	 *
	 * @param string $table
	 * @param array $values
	 *
	 * @return mixed
	 */
	public function insert($table, $values)
	{
		if(count($values) === 0){
			return null;
		}

		$this->query(
			sprintf(
				"INSERT INTO `%s` (%s) VALUES %s",
				$table,
				static::helperAttributeList(array_keys($values)),
				static::helperValueList(array_values($values))
			)
		);

		return $this->getInsertId();
	}

	/**
	 * Update table
	 *
	 * @param string $table
	 * @param array $values
	 * @param array $conditions
	 * @param string $conditionConnector
	 */
	public function update($table, $values, $conditions = array(), $conditionConnector = 'AND')
	{
		if(count($values) === 0){
			return;
		}

		$sqlValues = array();
		foreach($values as $attribute => $value){
			$sqlValues[] = array($attribute, '=', $value);
		}

		$this->query(
			sprintf(
				"UPDATE `%s` SET %s %s",
				$table,
				static::helperConditionList($sqlValues, ','),
				static::helperWhere($conditions, $conditionConnector)
			)
		);
	}


	/**
	 * Count in table
	 *
	 * @param string $table
	 * @param string $byAttribute
	 * @param array $conditions
	 * @param string $conditionConnector
	 *
	 * @return int
	 */
	public function count($table, $byAttribute, $conditions = array(), $conditionConnector = 'AND')
	{
		$result = $this->query(
			sprintf(
				"SELECT COUNT(`%s`) FROM `%s` %s",
				$byAttribute,
				$table,
				static::helperWhere($conditions, $conditionConnector)
			)
		);

		return intval($result->fetch_array(MYSQLI_NUM)[0]);
	}


	/**
	 * @param string $table
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function delete($table, $attribute, $value)
	{
		$sql = sprintf(
			"DELETE FROM `%s` %s",
			$table,
			static::helperWhere(array($attribute, $value))
		);

		$this->query($sql);
	}


	/**
	 * @param string $potentialKeyword
	 *
	 * @return bool
	 */
	protected static function isKeyword($potentialKeyword)
	{
		return in_array(
			strtoupper($potentialKeyword),
			array('AS', 'ASC', 'DESC')
		);
	}


	/**
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function helperAttributeList($attributes)
	{
		$sqlAttributes = array();
		foreach($attributes as $attribute){
			if(is_string($attribute)){ // raw
				$sqlAttributes[] = $attribute;
				continue;
			}

			if(!is_array($attribute)){
				$attribute = array($attribute);
			}

			$sqlPieces = array();
			for($i = 0; $i < count($attribute); ++$i){
				if(static::isKeyword($attribute[$i])){
					$sqlPieces[] = sprintf("%s", $attribute[$i]);
				}
				elseif(isset($attribute[$i + 1]) && !static::isKeyword($attribute[$i + 1])){
					$sqlPieces[] = sprintf("`%s`.`%s`", $attribute[$i], $attribute[++$i]);
				}
				else{
					$sqlPieces[] = sprintf("`%s`", $attribute[$i]);
				}
			}

			$sqlAttributes[] = implode(" ", $sqlPieces);
		}

		return sprintf(
			"%s",
			implode(', ', $sqlAttributes)
		);
	}


	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public static function helperValue($value)
	{
		if(is_null($value) || (is_string($value) && strtoupper($value) === 'NULL')){
			return "NULL";
		}
		elseif(is_array($value)){
			return static::helperValueList($value);
		}

		return sprintf(
			"'%s'",
			static::getInstance()->escape($value)
		);
	}

	/**
	 * @param array $values
	 *
	 * @return string
	 */
	public static function helperValueList($values)
	{
		$sqlValues = array();

		foreach($values as $val){
			$sqlValues[] = static::helperValue($val);
		}

		return sprintf(
			"(%s)",
			implode(', ', $sqlValues)
		);
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
	public static function helperConditionList($conditions, $conditionConnector = 'AND')
	{
		// detect non nested array
		if(count($conditions) > 0 && !is_array($conditions[0])){
			$conditions = array($conditions);
		}

		$conditionConnector = strtoupper($conditionConnector);
		if(in_array($conditionConnector, array('AND', 'OR'))){
			$conditionConnector = " ".$conditionConnector;
		}

		$values = array();
		foreach($conditions as $val){
			switch(count($val)){
				case 1:
					// raw
					$values[] = $val;
					break;
				case 2:
					$v = static::helperValue($val[1]);
					$values[] = sprintf("`%s` = %s", $val[0], $v);
					break;
				case 3:
					$v = static::helperValue($val[2]);
					$values[] = sprintf("`%s` %s %s", $val[0], strtoupper($val[1]), $v);
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
	public static function helperWhere($conditions, $conditionConnector = 'AND')
	{
		if(count($conditions) > 0){
			return sprintf(
				" WHERE %s",
				static::helperConditionList($conditions, $conditionConnector)
			);
		}

		return "";
	}

	/**
	 * @param array|null $orderBy Examples below:
	 *        null => ""
	 *        array() => ""
	 *        array('attr1' => 'asc', 'attr2' => 'desc') => " ORDER BY `attr1` ASC, `attr2` DESC"
	 *        array('attr1') => " ORDER BY `attr1` ASC"
	 *
	 * @return string
	 */
	public static function helperOrderBy($orderBy = null)
	{
		if(is_null($orderBy) || count($orderBy) === 0){
			return "";
		}

		$values = array();
		foreach($orderBy as $key => $val){
			if(is_int($key)){
				$values[] = array($val);
			}
			else{
				$values[] = array($key, strtoupper($val));
			}
		}

		return sprintf(
			" ORDER BY %s",
			static::helperAttributeList($values)
		);
	}

	/**
	 * @param int|array $limit
	 *        0 => ""
	 *        3 => " LIMIT 3"
	 *        array(3, 4) => " LIMIT 3,4"
	 *
	 * @return string
	 */
	public static function helperLimit($limit = 0)
	{
		if(is_array($limit) && count($limit) == 2){
			$limit = $limit[0].",".$limit[1];
		}

		if(is_string($limit) || (is_int($limit) && $limit > 0)){
			return sprintf(
				" LIMIT %s",
				$limit
			);
		}

		return "";
	}
}