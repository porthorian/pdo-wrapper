<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Models;

use \PDO;
use \PDOStatement;
use \Iterator;
use Porthorian\PDOWrapper\Interfaces\QueryInterface;
use Porthorian\PDOWrapper\Models\DBResult;

class QueryResult implements QueryInterface
{
	private $query;
	private string $query_string;
	private int $count = 0;
	protected $results = [];

	public function __construct(?PDOStatement $query)
	{
		$this->query = $query;
		if ($query !== null)
		{
			$this->count = $query->rowCount();
		}
	}
	
	public function withQueryString(string $query_string) : self
	{
		$this->query_string = $query_string;
		return $this;
	}

	public function getQueryString() : string
	{
		return $this->query_string;
	}

	public function isInitialized() : bool
	{
		return $this->query !== null;
	}

	/**
	* Get the value of whatever is stored inside $results
	* @return mixed Iterator - Iterator/Countable data
	*/
	public function getResults() : Iterator|array
	{
		return $this->results;
	}

	/**
	* Creates an Iterator object and only fetches 1 row at a time.
	* @return DBResult
	*/
	public function getDBResult() : DBResult
	{
		$this->results = new DBResult($this);
		return $this->results;
	}

	/**
	* Returns all the results from the query.
	* @return array
	*/
	public function fetchAllResults() : array
	{
		if (!$this->isInitialized())
		{
			return [];
		}

		$results = $this->query->fetchAll(PDO::FETCH_ASSOC);
		$this->results = is_array($results) === true ? $results : [];
		return $this->results;
	}

	/**
	* Returns a single result
	* @return array
	*/
	public function fetchResult() : array
	{
		if (!$this->isInitialized())
		{
			return [];
		}

		$results = $this->query->fetch(PDO::FETCH_ASSOC);
		$this->results = is_array($results) === true ? $results : [];
		return $this->results;
	}

	/**
	* The Count of the affected rows from the query.
	* @return int
	*/
	public function rowCount() : int
	{
		return $this->count;
	}

	/**
	 * Requery the prepared statement again to get updated results.
	 * @return void.
	 */
	public function requery() : void
	{
		if (!$this->isInitialized())
		{
			return;
		}

		$this->query->closeCursor();
		$this->query->execute();

		$this->count = $this->query->rowCount();
	}
}
