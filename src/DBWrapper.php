<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper;

use Porthorian\PDOWrapper\Exception\InvalidConfigException;
use Porthorian\PDOWrapper\Models\DBResult;
use Porthorian\PDOWrapper\Interfaces\DatabaseInterface;
use Porthorian\PDOWrapper\Interfaces\QueryInterface;

class DBWrapper
{
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
		$out_count = $pool->rowCount();

		return $query->fetchAllResults();
	}

	public static function insert(string $table, array $params, &$last_insert_id = 0, ?string $database = null) : bool
	{
		$pool = self::getDBPool($database);

		$query = $pool->query(DatabaseLib::generateInsertSQL($table, $params), $params);
		if (!$query->isInitialized())
		{
			return false;
		}
		$last_insert_id = $pool->getLastInsertID();
		return true;
	}

	public static function update(string $table, array $set_params, array $where_params, ?string $database = null) : bool
	{
		$pool = self::getDBPool($database);

		$prepared = [];
		$sql = DatabaseLib::generateUpdateSQL($table, $set_params, $where_params, $prepared);

		$query = $pool->query($sql, $prepared);
		if (!$query->isInitialized())
		{
			return false;
		}
		return true;
	}

	public static function delete(string $table, array $where_params, ?string $database = null) : bool
	{
		$pool = self::getDBPool($database);

		$prepared = [];
		$sql = DatabaseLib::generateDeleteSQL($table, $where_params, $prepared);

		$query = $pool->query($sql, $prepared);
		if (!$query->isInitialized())
		{
			return false;
		}
		return true;
	}

	/**
	* Start a transaction to wrap all queries till the transaction ends.
	* @param $database - The database you wanna execute this function on
	* @return bool
	*/
	public static function startTransaction(?string $database = null) : bool
	{
		$pool = self::getDBPool($database);

		return $pool->beginTransaction();
	}

	/**
	* Commits all queries in the transaction and ends the transaction.
	* @param $database - The database you wanna execute this function on
	* @return bool
	*/
	public static function commitTransaction(?string $database = null) : bool
	{
		$pool = self::getDBPool($database);

		return $pool->commitTransaction();
	}

	/**
	* Rollback all queries in the transaction and ends the transaction.
	* @param $database - The database you wanna execute this function on
	* @return bool
	*/
	public static function rollbackTransaction(?string $database = null) : bool
	{
		$pool = self::getDBPool($database);

		return $pool->rollbackTransaction();
	}

	public static function quote(string $value) : ?string
	{
		$pool = self::getDBPool(null);

		return $pool->quote($value);
	}

	private static function getDBPool(?string $database = null) : DatabaseInterface
	{
		if ($database === null)
		{
			$database = (DBPool::getPoolCreds()[0])?->getDBName();
		}

		$pool = DBPool::getDBI($database);
		if ($pool === null)
		{
			throw new InvalidConfigException('Database pool connection does not exist.');
		}
		return $pool;
	}
}
