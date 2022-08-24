<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Tests\Suite;

use \InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Porthorian\PDOWrapper\Util\DatabaseLib;

class DatabaseLibTest extends TestCase
{
	public function testGenerateInsertSQL()
	{
		$values = ['name' => 'hello_world1', 'new_name' => 'hello'];

		$sql = DatabaseLib::generateInsertSQL('table', $values);

		$expected_sql = 'INSERT INTO `table` (`name`,`new_name`) VALUES (?,?)';

		$this->assertEquals($expected_sql, $sql);

		$this->expectException(InvalidArgumentException::class);
		DatabaseLib::generateInsertSQL('\'table', $values);
	}

	public function testGenerateUpdateSQL()
	{
		$where_array = ['name' => 'hello_world1', 'new_name' => 'hello'];
		$set_array = ['name' => 'hello_world'];

		$sql = DatabaseLib::generateUpdateSQL('foobar', $set_array, $where_array, $prepared);

		$expected_sql = 'UPDATE `foobar` SET `name` = ? WHERE `name` = ? AND `new_name` = ?';
		$this->assertEquals($expected_sql, $sql);
		$index = 0;
		foreach ([$set_array, $where_array] as $array)
		{
			foreach ($array as $value)
			{
				$this->assertEquals($value, $prepared[$index++]);
			}
		}

		$this->expectException(InvalidArgumentException::class);
		DatabaseLib::generateUpdateSQL('\table  ', $set_array, $where_array, $prepared);
	}

	public function testGenerateDeleteSQL()
	{
		$where_array = ['name' => 'hello_world1', 'new_name' => 'hello'];
		$sql = DatabaseLib::generateDeleteSQL('delete_me', $where_array, $prepared);
		$index = 0;
		foreach ($where_array as $value)
		{
			$this->assertEquals($value, $prepared[$index++]);
		}

		$expected_sql = 'DELETE FROM `delete_me` WHERE `name` = ? AND `new_name` = ?';
		$this->assertEquals($expected_sql, $sql);

		$this->expectException(InvalidArgumentException::class);
		DatabaseLib::generateDeleteSQL('\'hello_worlcxd', $where_array);
	}

	public function testEmptyTableNameInsert()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Name must not be empty.');
		DatabaseLib::generateInsertSQL('', []);
	}

	public function testEmptyTableNameUpdate()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Name must not be empty.');
		DatabaseLib::generateUpdateSQL('', [], [], $prepared);
	}

	public function testEmptyTableNameDelete()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Name must not be empty.');
		DatabaseLib::generateDeleteSQL('', [], $prepared);
	}
}
