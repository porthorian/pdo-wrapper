<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper;

use Porthorian\PDOWrapper\Models\DatabaseModel;
use Porthorian\PDOWrapper\Exception\DatabaseException;

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
		if (static::isDatabaseConnectionAvailable($database))
		{
			static::disconnectDatabase($database);
		}

		try
		{
			$pdo = new DatabasePDO($database);
			if ($pdo->isConnected())
			{
				static::$db_pool[$database] = $pdo;
				return true;
			}
		}
		catch (DatabaseException $e)
		{
			static::addPoolError($e->getMessage(), $database);
		}

		return false;
	}

	/**
	* @param $database - Disconnect from selected database.
	* @return void
	*/
	public static function disconnectDatabase(string $database) : void
	{
		if (static::isDatabaseConnectionAvailable($database))
		{
			$pdo = static::$db_pool[$database];
			if ($pdo->isConnected())
			{
				$pdo->disconnect();
			}
		}

		unset(static::$db_pool[$database]);
	}

	/**
	* @return DatabasePDO|null on failure
	*/
	public static function getDBI(string $database) : ?DatabasePDO
	{
		if (!static::isDatabaseConnectionAvailable($database))
		{
			static::connectDatabase($database);
		}
		return static::$db_pool[$database] ?? null;
	}

	/**
	* @param $database - Is the selected database connected currently?
	* @return bool
	*/
	public static function isDatabaseConnectionAvailable(string $database) : bool
	{
		return isset(static::$db_pool[$database]);
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
			return static::$db_pool_errors[$database] ?? [];
		}
		return static::$db_pool_errors;
	}

	/**
	 * Add a Database pool with the credentials
	 * @throws InvalidConfigException
	 * @return void
	 */
	public static function addPoolCred(DatabaseModel $model) : void
	{
		static::$db_pool_creds[$model->getDBName()] = $model;
	}

	/**
	 * Get all the database pools with credentials
	 * @throws DatabaseException
	 * @return DatabaseModel[]|DatabaseModel
	 */
	public static function getPoolCreds(string $database = '') : array|DatabaseModel
	{
		if ($database != '')
		{
			if (!isset(static::$db_pool_creds[$database]))
			{
				throw new DatabaseException('Pool credentials not found.');
			}

			return static::$db_pool_creds[$database];
		}
		return static::$db_pool_creds;
	}

	/**
	* @param $message - Message to add to DBPool::error
	* @return void
	*/
	private static function addPoolError(string $message, string $database) : void
	{
		static::$db_pool_errors[$database][] = $message;
	}
}
