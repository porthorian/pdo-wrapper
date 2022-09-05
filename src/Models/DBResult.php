<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Models;

use \Iterator;
use \Countable;
use Porthorian\PDOWrapper\Interfaces\QueryInterface;

class DBResult implements Iterator, Countable
{
	private $pdo_object;

	private int $pointer = -1;
	private array $record = [];
	private int $total_count = 0;

	public function __construct(QueryInterface $pdo_object)
	{
		$this->pdo_object = $pdo_object;
		/**
		* Get the count immidately, before another query is executed.
		*/
		$this->total_count = $pdo_object->rowCount();

		/**
		* Populate the first entry before the loop
		*/
		if ($this->count() >= 1)
		{
			$this->next();
		}
		else if ($this->count() === 0)
		{
			/*
			* No entry don't attempt to start the loop.
			*/
			$this->pointer = 0;
		}
	}

	/**
	* Get the current row on this index/key for the loop
	* @return array
	*/
	public function getRecord() : array
	{
		return $this->record;
	}

	////
	// Iterator Functions
	////

	/**
	* Lets grab the current record.
	* Do not use this function, use getRecord instead.
	* @return mixed
	*/
	public function current() : mixed
	{
		return $this->getRecord();
	}

	/**
	* Current index/key for the loop
	* @return int
	*/
	public function key() : int
	{
		return $this->pointer;
	}

	/**
	* Have we reached the end of our iterator?
	* @return bool
	*/
	public function valid() : bool
	{
		return ($this->pointer < $this->count());
	}

	/**
	* Moves the pointer to the next result
	* @return void
	*/
	public function next() : void
	{
		$this->record = $this->pdo_object->fetchResult();
		$this->pointer++;
	}

	/**
	* Executed at the beginning of the loop
	* @return void
	*/
	public function rewind() : void
	{
		/**
		 * This gets executed when someone has ran foreach twice on the object.
		 */
		if ($this->pointer != 0 && $this->pointer >= $this->count())
		{
			/**
			* PDO has very limited support when it comes rewinding cursors. So we sadly have to requery the prepared statement.
			* User may get different results if the data has changed depending on the query.
			* If you have to loop over a result set more than once, just add it to an array. Or use fetchAll.
			*/
			$this->pdo_object->requery();
			$this->pointer = -1;
			$this->next();
		}
	}

	////
	// Countable Functions
	////

	/**
	* Gets the total amount of rows that will be returned from the query.
	* @return int
	*/
	public function count() : int
	{
		return $this->total_count;
	}
}
