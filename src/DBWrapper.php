<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper;

use Porthorian\PDOWrapper\Models\DBResult;
use Porthorian\PDOWrapper\Interfaces\DatabaseInterface;
use Porthorian\PDOWrapper\Interfaces\QueryInterface;
use Porthorian\PDOWrapper\Util\DatabaseLib;

class DBWrapper
{
	private static ?string $DEFAULT_DB = null;

	/**
	 * Set the default database for all querys done via this wrapper.
	 * @return void
	 */
	public static function setDefaultDB(string $database) : void
	{
		static::$DEFAULT_DB = $database;
	}

	/**
	 * Executes your SQL query than returns a query interface object that can be used in its buffered state.
	 * @return QueryInterface
	 */
	public static function factory(string $sql, array $params = [], ?string $database = null) : QueryInterface
	{
		$pool = self::getDBPool($database);

		return $pool->query($sql, $params);
	}

	/**
	* Executes a SQL query prepared then it throws it into an Object that is implemented by Iterator and Countable
	* This allows you to loop through a large query with out having to allocate additional memory to store the results.
	* It only grabs the row/result for a single row on each loop.
	* @param $sql - The sql you run
	* @param $params - Values that you wanna insert into the prepared statement
	* @param $database - The database you wanna execute this function on
	* @return DBResult
	*/
	public static function PResult(string $sql, array $params = [], ?string $database = null) : DBResult
	{
		$pool = self::getDBPool($database);

		$query = $pool->query($sql, $params);

		return $query->getDBResult();
	}

	/**
	* Returns all results in the query
	* @param $sql - The sql you run
	* @param $params - Values that you wanna insert into the prepared statement
	* @param $database - The database you wanna execute this function on
	* @return array
	*/
	public static function PExecute(string $sql, array $params = [], ?int &$out_count = 0, ?string $database = null) : array
	{
		$pool = self::getDBPool($database);

		$query = $pool->query($sql, $params);
		$out_count = $query->rowCount();

		return $query->fetchAllResults();
	}

	/**
	* Only returns a single result
	* @param $sql - The sql you run
	* @param $params - Values that you wanna insert into the prepared statement
	* @param $database - The database you wanna execute this function on
	* @return array
	*/
	public static function PSingle(string $sql, array $params = [], ?int &$out_count = 0, ?string $database = null) : array
	{
		$pool = self::getDBPool($database);

		$query = $pool->query($sql, $params);
		$out_count = $query->rowCount();

		return $query->fetchResult();
	}

	/**
	* Executing Raw Sql
	* @param $sql - The sql you run
	* @param $database - The database you wanna execute this function on
	* @return array
	*/
	public static function execute(string $sql, ?int &$out_count = 0, ?string $database = null) : array
	{
		$pool = self::getDBPool($database);

		$query = $pool->query($sql);
		$out_count = $query->rowCount();

		return $query->fetchAllResults();
	}

	/**
	 * @return string|int - The last inserted id that was done.
	 */
	public static function insert(string $table, array $params, ?string $database = null) : string|int
	{
		$pool = self::getDBPool($database);

		$query = $pool->query(DatabaseLib::generateInsertSQL($table, $params), $params);
		if (!$query->isInitialized())
		{
			throw new DatabaseException('Query object failed to initialize and is not set.');
		}
		return $pool->getLastInsertID();
	}

	/**
	 * @return int - The amount of rows that were affected.
	 */
	public static function update(string $table, array $set_params, array $where_params, ?string $database = null) : int
	{
		$pool = self::getDBPool($database);

		$prepared = [];
		$sql = DatabaseLib::generateUpdateSQL($table, $set_params, $where_params, $prepared);

		$query = $pool->query($sql, $prepared);
		if (!$query->isInitialized())
		{
			throw new DatabaseException('Query object failed to initialize and is not set.');
		}

		return $query->rowCount();
	}

	/**
	 * @return int - The amount of rows that were affected.
	 */
	public static function delete(string $table, array $where_params, ?string $database = null) : int
	{
		$pool = self::getDBPool($database);

		$prepared = [];
		$sql = DatabaseLib::generateDeleteSQL($table, $where_params, $prepared);

		$query = $pool->query($sql, $prepared);
		if (!$query->isInitialized())
		{
			throw new DatabaseException('Query object failed to initialize and is not set.');
		}
		return $query->rowCount();
	}

	/**
	* Start a transaction to wrap all queries till the transaction ends.
	* @param $database - The database you wanna execute this function on
	* @return bool
	*/
	public static function startTransaction(?string $database = null) : bool
	{
		return  self::getDBPool($database)->beginTransaction();
	}

	/**
	* Commits all queries in the transaction and ends the transaction.
	* @param $database - The database you wanna execute this function on
	* @return bool
	*/
	public static function commitTransaction(?string $database = null) : bool
	{
		return self::getDBPool($database)->commitTransaction();
	}

	/**
	* Rollback all queries in the transaction and ends the transaction.
	* @param $database - The database you wanna execute this function on
	* @return bool
	*/
	public static function rollbackTransaction(?string $database = null) : bool
	{
		return self::getDBPool($database)->rollbackTransaction();
	}

	/**
	 * Quote a string to be escaped to avoid sql injection
	 * @return null|string - The escaped string on success.
	 */
	public static function qstr(string $value) : ?string
	{
		return self::getDBPool(null)->quote($value);
	}

	private static function getDBPool(?string $database = null) : DatabaseInterface
	{
		$error_message = 'Database pool connection does not exist.';

		if ($database === null && static::$DEFAULT_DB !== null)
		{
			$database = static::$DEFAULT_DB;
		}

		if ($database === null)
		{
			$pool = array_values(DBPool::getPools())[0] ?? null;
			if (!$pool?->isConnectionAvailable())
			{
				throw new DatabaseException($error_message);
			}

			static::setDefaultDB($pool->getDatabaseModel()->getDBName());
			return $pool->getConnection();
		}

		$pool = DBPool::getDBI($database);
		if ($pool === null)
		{
			throw new DatabaseException($error_message);
		}
		return $pool;
	}
}
