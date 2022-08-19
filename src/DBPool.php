<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper;

use \Exception;
use Porthorian\PDOWrapper\Interfaces\DatabaseInterface;
use Porthorian\PDOWrapper\Models\DatabaseModel;
use Porthorian\PDOWrapper\Exception\DatabaseException;
use Porthorian\PDOWrapper\Exception\InvalidConfigException;

class DBPool
{
	/**
	 * Stores our database connections throughout the request.
	 * Allows for multiple databases to be accessible.
	 */
	private static array $db_pool_instances = [];

	////
	// Public methods
	////

	private DatabaseModel $model;
	private ?DatabaseInterface $connection = null;

	public function __construct(DatabaseModel $model)
	{
		$this->model = $model;
	}

	public function connect(int $timeout = 1) : void
	{
		$this->connection = null; // Wipe the existing connection if there is one.

		try
		{
			$pdo = DBFactory::create($this->getDatabaseModel());
			try
			{
				$pdo->connect($timeout);
			}
			catch (DatabaseException)
			{
				$pdo->connect($timeout + 2);
			}

			if (!$pdo->isConnected())
			{
				throw new DatabaseException('Failed to validate connection to the database.');
			}
			$this->connection = $pdo;
		}
		catch (Exception $e)
		{
			throw new DatabaseException('Failed to connect to database', $e);
		}
	}

	public function disconnect() : void
	{
		if ($this->connection === null)
		{
			return;
		}

		if ($this->connection->isConnected())
		{
			$this->connection->disconnect();
		}

		$this->connection = null;
	}

	public function isConnectionAvailable() : bool
	{
		return $this->connection !== null;
	}

	public function getDatabaseModel() : DatabaseModel
	{
		return $this->model;
	}

	public function getConnection() : ?DatabaseInterface
	{
		return $this->connection;
	}

	////
	// Public static methods
	////

	/**
	* @param $database - Connect to selected database.
	* @return bool
	*/
	public static function connectDatabase(string $database, int $timeout = 1) : void
	{
		if (static::isDatabaseConnectionAvailable($database))
		{
			static::disconnectDatabase($database);
		}

		(static::$db_pool_instances[$database] ?? null)->connect($timeout);
	}

	/**
	* @param $database - Disconnect from selected database.
	* @return void
	*/
	public static function disconnectDatabase(string $database) : void
	{
		(static::$db_pool_instances[$database] ?? null)?->disconnect();
	}

	/**
	* @return DatabaseInterface|null on failure
	*/
	public static function getDBI(string $database) : ?DatabaseInterface
	{
		if (!static::isDatabaseConnectionAvailable($database))
		{
			static::connectDatabase($database);
		}
		return (static::$db_pool_instances[$database] ?? null)?->getConnection();
	}

	/**
	* @param $database - Is the selected database connected currently?
	* @return bool
	*/
	public static function isDatabaseConnectionAvailable(string $database) : bool
	{
		if (!isset(static::$db_pool_instances[$database]))
		{
			return false;
		}

		return static::$db_pool_instances[$database]->isConnectionAvailable();
	}

	/**
	 * Add a Database pool with the credentials
	 * @throws InvalidConfigException
	 * @return void
	 */
	public static function addPool(DatabaseModel $model) : void
	{
		static::$db_pool_instances[$model->getDBName()] = new DBPool($model);
	}

	/**
	 * Remove the database pool.
	 * @return void
	 */
	public static function removePool(string $database) : void
	{
		unset(static::$db_pool_instances[$database]);
	}

	/**
	 * Get all the database pools with credentials
	 * @throws DatabaseException
	 * @return DBPool[]|DBPool
	 */
	public static function getPools(string $database = '') : array|DBPool
	{
		if ($database != '')
		{
			if (!isset(static::$db_pool_instances[$database]))
			{
				throw new InvalidConfigException('Database pool not found.');
			}

			return static::$db_pool_instances[$database];
		}

		return static::$db_pool_instances;
	}
}
