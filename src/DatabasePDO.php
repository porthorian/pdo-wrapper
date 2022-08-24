<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper;

use \PDO;
use \PDOStatement;
use \PDOException;
use Porthorian\PDOWrapper\Interfaces\DatabaseInterface;
use Porthorian\PDOWrapper\Interfaces\QueryInterface;
use Porthorian\PDOWrapper\Exception\DatabaseException;
use Porthorian\PDOWrapper\Models\QueryResult;
use Porthorian\PDOWrapper\Models\DatabaseModel;

class DatabasePDO implements DatabaseInterface
{
	/**
	 * Connection to the Database
	 */
	protected ?PDO $pdo = null;
	/**
	* The current query being acted on in the object.
	*/
	protected PDOStatement $query;

	protected DatabaseModel $model;

	public function __construct(DatabaseModel $model)
	{
		$this->model = $model;
	}

	/**
	 * @param $timeout - Optional - Number of seconds for a timeout to occur.
	 * @throws DatabaseException
	 * @return void
	*/
	public function connect(int $timeout = 1) : void
	{
		$dsn = $this->model->getDSN();
		try
		{
			$this->pdo = new PDO($dsn, $this->model->getUser(), $this->model->getPassword(), [
				PDO::ATTR_TIMEOUT          => $timeout, //Seconds
				PDO::ATTR_EMULATE_PREPARES => false,
				PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION
			]);
		}
		catch (PDOException $e)
		{
			$this->disconnect();
			throw new DatabaseException('PDO Connection failed', $e);
		}
	}

	/**
	* Do we have PDO Object connected?
	* @return bool
	*/
	public function isConnected() : bool
	{
		return $this->pdo !== null;
	}

	/**
	* Null out the PDO object, which terminates the connection, unless the connection is persistant.
	* In our case we do not use persistant connections.
	* @return void
	*/
	public function disconnect() : void
	{
		$this->pdo = null;
	}

	/**
	* Executes an SQL prepared statement
	* https://www.php.net/manual/en/pdo.prepare.php
	* @param $sql - The SQL Statement to execute
	* @param $values - The values to bind corresponding question marks to.
	* @return QueryInterface
	*/
	public function query(string $sql, array $values = []) : QueryInterface
	{
		$result = new QueryResult(null);
		if (!$this->isConnected())
		{
			throw new DatabaseException($this->model->getDBName() . ' is not connected.');
		}

		try
		{
			$this->query = $this->pdo->prepare($sql);

			$param_position = 1;
			foreach ($values as $value)
			{
				$this->bind($param_position++, $value);
			}

			if ($this->execute())
			{
				return new QueryResult($this->query);
			}
		}
		catch (PDOException $e)
		{
			throw new DatabaseException('Failed to execute query against schema '.$this->model->getDBName(), $e);
		}

		return $result;
	}

	/**
	* Returns the ID of the last inserted row, or the last value from a sequence object
	* https://www.php.net/manual/en/pdo.lastinsertid.php
	* If you are in a transaction this will return 0
	* @return string|int
	*/
	public function getLastInsertID() : string|int
	{
		return $this->pdo->lastInsertId();
	}

	/**
	* Start our transaction
	* https://www.php.net/manual/en/pdo.begintransaction.php
	* @return bool
	*/
	public function beginTransaction() : bool
	{
		if ($this->inTransaction())
		{
			$this->commitTransaction();
			throw new DatabaseException('Unable to start a new transaction as there is already one that exists.');
		}
		return $this->pdo->beginTransaction();
	}

	/**
	* Commit all the queries in the transaction and end the transaction
	* https://www.php.net/manual/en/pdo.commit.php
	* @return bool
	*/
	public function commitTransaction() : bool
	{
		return $this->pdo->commit();
	}

	/**
	* Rollback all the queries in the transaction and end the transaction
	* https://www.php.net/manual/en/pdo.rollback.php
	* @return bool
	*/
	public function rollbackTransaction() : bool
	{
		return $this->pdo->rollBack();
	}

	/**
	* Are we in a transaction?
	* https://www.php.net/manual/en/pdo.intransaction.php
	* @return bool
	*/
	public function inTransaction() : bool
	{
		return $this->pdo->inTransaction();
	}

	/**
	* Escape a string to be compliant with a sql statement.
	* @param $value - That you wanna ensure no sql escaping.
	* @return string
	*/
	public function quote(string $value) : string
	{
		$quote = $this->pdo->quote($value);

		/**
		* Returns false if the driver does not support quoting this way.
		*/
		if ($quote === false)
		{
			throw new DatabaseException('Driver does not support escaped quoting.');
		}
		return $quote;
	}

	////
	// Private Routines
	////

	/**
	* Binds a value to a corresponding question mark placeholder in the SQL statement that was used to prepare the statement.
	* https://www.php.net/manual/en/pdostatement.bindvalue.php
	* @param $param - Which question mark should we bind this value to.
	* @param $value - The value that is getting binded to the question mark
	*/
	private function bind(int $param, mixed $value) : bool
	{
		switch (true)
		{
			case is_int($value):
				$type = PDO::PARAM_INT;
			break;

			case is_bool($value):
				$type = PDO::PARAM_BOOL;
			break;

			case is_null($value):
				$type = PDO::PARAM_NULL;
			break;

			default:
				$type = PDO::PARAM_STR;
			break;
		}
		return $this->query->bindValue($param, $value, $type);
	}

	/**
	* Execute the prepared statement.
	* https://www.php.net/manual/en/pdostatement.execute.php
	*/
	private function execute()
	{
		if (!$this->isConnected())
		{
			throw new DatabaseException($this->model->getDBName() . ' is no longer connected');
		}
		return $this->query->execute();
	}
}
