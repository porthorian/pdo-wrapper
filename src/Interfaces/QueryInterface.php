<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Interfaces;

use Porthorian\PDOWrapper\Models\DBResult;

interface QueryInterface
{
	/**
	 * Set the unprepared query
		 * @return self
		 */
	public function withQueryString(string $query_string) : self;

	/**
		 * Get the the query string that is unprepared.
		 * @return string
		 */
	public function getQueryString() : string;

	/**
	* Whether the query was successful or not.
	* @return bool
	*/
	public function isInitialized() : bool;

	/**
	* Get the value of whatever is stored inside $results
	* @return mixed Array|DBResult - Iterator/Countable data
	*/
	public function getResults();

	/**
	* Creates an Iterator object and only fetches 1 row at a time.
	* @return DBResult
	*/
	public function getDBResult() : DBResult;

	/**
	* Returns all the results from the query.
	* @return array
	*/
	public function fetchAllResults() : array;

	/**
	* Returns a single result
	* @return array
	*/
	public function fetchResult() : array;

	/**
	* The Count of the affected rows from the query.
	* @return int
	*/
	public function rowCount() : int;

	/**
	 * Requery the prepared statement again to get updated results.
	 * @return void.
	 */
	public function requery() : void;
}
