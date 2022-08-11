<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper;

class DBPool
{
	/**
	* Stores our database connections throughout the request.
	* Allows for multiple databases to be accessible.
	*/
	private static array $db_pool = [];

	/**
	 * @var DatabaseModel[]
	 */
	private static array $db_pool_creds = [];

	/**
	* Stores our pool errors related to the database connection
	*/
	private static array $db_pool_errors = [];

	/**
	* @param $database - Connect to selected database.
	* @return bool
	*/
	public static function connectDatabase(string $database) : bool
	{
		if (self::isDatabaseConnectionAvailable($database))
		{
			self::disconnectDatabase($database);
		}

		$pdo = new DatabasePDO($database);
		if ($pdo->isConnected())
		{
			self::$db_pool[$database] = $pdo;
			return true;
		}
		self::addPoolError($pdo->getInternalErrors(), $database);
		return false;
	}

	/**
	* @param $database - Disconnect from selected database.
	* @return void
	*/
	public static function disconnectDatabase(string $database) : void
	{
		if (self::isDatabaseConnectionAvailable($database))
		{
			$pdo = self::$db_pool[$database];
			if ($pdo->isConnected())
			{
				$pdo->disconnect();
			}
		}

		unset(self::$db_pool[$database]);
	}

	/**
	* @return DatabasePDO|null on failure
	*/
	public static function getDBI(string $database)
	{
		if (!self::isDatabaseConnectionAvailable($database))
		{
			self::connectDatabase($database);
		}
		return self::$db_pool[$database] ?? null;
	}

	/**
	* @param $database - Is the selected database connected currently?
	* @return bool
	*/
	public static function isDatabaseConnectionAvailable(string $database) : bool
	{
		return isset(self::$db_pool[$database]);
	}

	/**
	* Gets all the errors from the DBPool
	* @param $database - Optional - get selected database errors.
	* @return array
	*/
	public static function getPoolErrors(string $database = '') : array
	{
		if ($database != '')
		{
			return self::$db_pool_errors[$database] ?? [];
		}
		return self::$db_pool_errors;
	}

	public static function addDBPool(DatabaseModel $model) : void
	{
		static::$db_pool_creds[$model->getDBName()] = $model;
	}

	public static function getDBPools() : array
	{
		return static::$db_pool_creds;
	}

	/**
	* @param $message - Message to add to DBPool::error
	* @return void
	*/
	private static function addPoolError(string $message, string $database) : void
	{
		self::$db_pool_errors[$database][] = $message;
	}
}
