<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Interfaces;

interface DatabaseInterface
{
	/**
	* @param $dbname - The database schema to use.
	*/
	public function __construct(string $dbname);

	/**
	* Do we have an Object connected?
	* @return bool
	*/
	public function isConnected() : bool;

	/**
	* Terminates the connection, unless the connection is persistant.
	* In our case we do not use persistant connections.
	* @return void
	*/
	public function disconnect() : void;

	/**
	* Executes an SQL prepared statement
	* @param $sql - The SQL Statement to execute
	* @param $values - The values to bind corresponding question marks to.
	* @return bool
	*/
	public function query(string $sql, array $values = []) : QueryInterface;

	/**
	* Returns the ID of the last inserted row, or the last value from a sequence object
	* @return string|int
	*/
	public function getLastInsertID();

	/**
	* Start our transaction
	* @return bool
	*/
	public function beginTransaction() : bool;

	/**
	* Commit all the queries in the transaction and end the transaction
	* @return bool
	*/
	public function commitTransaction() : bool;

	/**
	* Rollback all the queries in the transaction and end the transaction
	* @return bool
	*/
	public function rollbackTransaction() : bool;

	/**
	* Are we in a transaction?
	* @return bool
	*/
	public function inTransaction() : bool;

	/**
	* Escape a string to be compliant with a sql statement.
	* @param $value - That you wanna ensure no sql escaping.
	* @return string|null on failure
	*/
	public function quote(string $value) : ?string;
}
