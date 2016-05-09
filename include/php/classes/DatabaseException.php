<?php

class DatabaseException extends Exception
{
	/** @var string */
	protected $query;

	/**
	 * Set the executed SQL query
	 *
	 * @param string $query
	 *
	 * @return $this
	 */
	public function setQuery($query)
	{
		$this->query = $query;

		return $this;
	}

	/**
	 * Get the executed SQL query
	 *
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}
}