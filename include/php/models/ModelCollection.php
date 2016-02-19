<?php

class ModelCollection implements Iterator, ArrayAccess, Countable
{

	/**
	 * @var array|AbstractModel[]
	 */
	private $models = array();


	/**
	 * Constructor.
	 *
	 * @param array|AbstractModel[] $array
	 */
	public function __construct($array = array())
	{
		if($this->isNumericArray($array)){
			foreach($array as $model){
				$this->add($model);
			}
		}
		else{
			foreach($array as $key => $model){
				$this->add($model, $key);
			}
		}
	}


	/**
	 * @param array $array
	 *
	 * @return bool
	 */
	protected function isNumericArray($array)
	{
		return array_keys($array) === range(0, count($array) - 1)
		&& count(array_filter($array, 'is_string')) === 0;
	}


	/**
	 * Adds a model to the collection,
	 * but will not replace if it exists with that key
	 *
	 * @param AbstractModel $model
	 * @param mixed|null $key
	 */
	public function add($model, $key = null)
	{
		if(is_null($model) || !($model instanceof AbstractModel)){
			return;
		}

		if(is_null($key)){
			$this->models[] = $model;
		}
		elseif(!$this->has($key)){
			$this->models[$key] = $model;
		}
	}


	/**
	 * Replace a model with given key
	 *
	 * @param AbstractModel $model
	 * @param mixed $key
	 */
	public function replace($model, $key)
	{
		if(is_null($model) || !($model instanceof AbstractModel)){
			return;
		}

		$model[$key] = $model;
	}


	/**
	 * Delete a model by key
	 *
	 * @param mixed $key
	 */
	public function delete($key)
	{
		if($this->has($key)){
			unset($this->models[$key]);
		}
	}


	/**
	 * Check if collection has a model by key
	 *
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public function has($key)
	{
		return isset($this->models[$key]);
	}


	/**
	 * Get a model from the collection by key
	 *
	 * @param mixed $key
	 *
	 * @return AbstractModel|null
	 */
	public function get($key)
	{
		if($this->has($key)){
			return $this->models[$key];
		}

		return null;
	}


	/**
	 * Search a model in collection with a condition
	 *
	 * @param callable $callable Gives back if the search matches
	 *
	 * @return AbstractModel|null
	 */
	public function search($callable)
	{
		if(is_callable($callable)){
			foreach($this->models as $model){
				if($callable($model)){
					return $model;
				}
			}
		}

		return null;
	}


	/**
	 * Search all models in collection with a condition
	 *
	 * @param callable $callable Gives back if the search matches
	 *
	 * @return static
	 */
	public function searchAll($callable)
	{
		$collection = new static;

		if(is_callable($callable)){
			foreach($this->models as $model){
				if($callable($model)){
					$collection->add($model);
				}
			}
		}

		return $collection;
	}


	/**
	 * @inheritdoc
	 */
	public function current()
	{
		return current($this->models);
	}


	/**
	 * @inheritdoc
	 */
	public function next()
	{
		return next($this->models);
	}


	/**
	 * @inheritdoc
	 */
	public function key()
	{
		return key($this->models);
	}


	/**
	 * @inheritdoc
	 */
	public function valid()
	{
		return $this->current() !== false;
	}


	/**
	 * @inheritdoc
	 */
	public function rewind()
	{
		reset($this->models);
	}


	/**
	 * @inheritdoc
	 */
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}


	/**
	 * @inheritdoc
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}


	/**
	 * @inheritdoc
	 */
	public function offsetSet($offset, $value)
	{
		$this->add($value, $offset);
	}


	/**
	 * @inheritdoc
	 */
	public function offsetUnset($offset)
	{
		$this->delete($offset);
	}


	/**
	 * @inheritdoc
	 */
	public function count()
	{
		return count($this->models);
	}
}
