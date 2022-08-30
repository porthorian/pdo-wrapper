<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Util;

use \InvalidArgumentException;

class DatabaseLib
{
	/**
	* @param $table - The table you wanna generate the insert sql for
	* @param $params - ['column_name' => $value]
	* @throws InvalidArgumentException
	* @return string
	*/
	public static function generateInsertSQL(string $table, array $params) : string
	{
		$columns = '';
		$values = '';
		$count_params = count($params);
		$counter = 0;
		foreach (array_keys($params) as $column)
		{
			$columns .= static::escapeSchemaName($column);
			$values .= '?';

			if (++$counter < $count_params)
			{
				$columns .= ',';
				$values .= ',';
			}
		}

		$sql = 'INSERT INTO ' . static::escapeSchemaName($table) . ' (' . $columns . ') VALUES (' . $values . ')';

		return $sql;
	}

	/**
	* @param $table - The table you wanna generate the update sql for
	* @param $set_params - ['column_name' => $value]
	* @param $where_params - ['column_name' => $value]
	* @param $out_prepared - All the values in number index array keys array($value, $value1, $value2)
	* @throws InvalidArgumentException
	* @return string
	*/
	public static function generateUpdateSQL(string $table, array $set_params, array $where_params, ?array &$out_prepared = null) : string
	{
		$out_prepared = [];
		$set = static::generateParameters($set_params, $out_prepared, ',');
		$where = static::generateParameters($where_params, $out_prepared);

		$sql = 'UPDATE ' . static::escapeSchemaName($table) . ' SET ' . $set . ' WHERE ' . $where;
		return $sql;
	}

	/**
	* @param $table - The table you wanna generate delete sql for
	* @param $where_params - ['column_name' => $value]
	* @param $out_prepared - All the values in number index array keys [$value, $value1, $value2]
	* @throws InvalidArgumentException
	* @return string
	*/
	public static function generateDeleteSQL(string $table, array $where_params, ?array &$out_prepared = null) : string
	{
		$out_prepared = [];

		return 'DELETE FROM ' . static::escapeSchemaName($table) . ' WHERE ' . static::generateParameters($where_params, $out_prepared);
	}

	/**
	 * Escape a database table name or a column name.
	 * @param $name - Any underscored name for a column, table, or db schema.
	 * @return string
	 */
	public static function escapeSchemaName(string $name) : string
	{
		if (empty($name))
		{
			throw new InvalidArgumentException('Name must not be empty.');
		}

		$match = preg_match('/^(?![0-9])[A-Za-z0-9_]*$/', $name);
		if (!$match)
		{
			throw new InvalidArgumentException('Name may contain only alphanumerics or underscores, and may not begin with a digit.');
		}

		return '`'.$name.'`';
	}

	private static function generateParameters(array $params, array &$out_prepared, string $delimiter = 'AND') : string
	{
		$parameters = '';
		$count = 0;
		$count_params = count($params);
		foreach ($params as $column => $value)
		{
			$parameters .= static::escapeSchemaName($column) . ' = ?';
			if (++$count < $count_params)
			{
				$parameters .= " {$delimiter} ";
			}
			$out_prepared[] = $value;
		}

		return $parameters;
	}
}
