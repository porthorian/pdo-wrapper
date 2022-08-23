<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Tests\Suite;

use Porthorian\PDOWrapper\Tests\DBTest;
use Porthorian\PDOWrapper\DBWrapper;
use Porthorian\PDOWrapper\DBPool;
use Porthorian\PDOWrapper\Models\DBResult;
use Porthorian\PDOWrapper\Interfaces\QueryInterface;
use Porthorian\PDOWrapper\Exception\DatabaseException;

class DBWrapperTest extends DBTest
{
	public function setUp() : void
	{
		parent::setUp();
		DBPool::connectDatabase(self::TEST_DB);

		DBWrapper::factory('CREATE TABLE test(
			KEYID INT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL
		)');
	}

	public function tearDown() : void
	{
		DBWrapper::factory('DROP TABLE test');
		parent::tearDown();
	}

	public function testFactory()
	{
		DBWrapper::factory('INSERT INTO test(name) VALUES(?)', ['hello_world']);

		$query = DBWrapper::factory('SELECT * FROM test');
		$this->assertInstanceOf(QueryInterface::class, $query);
		$this->assertEquals(1, $query->rowCount());
	}

	public function testPResult()
	{
		$max = 10;
		for ($i = 1; $i <= $max; $i++)
		{
			DBWrapper::PResult('INSERT INTO test(name) VALUES(?)', ['hello_world'.$i]);
		}

		$results = DBWrapper::PResult('SELECT * FROM test WHERE name LIKE ?', ['hello_world%']);
		$this->assertInstanceOf(DBResult::class, $results);
		$this->assertCount($max, $results);
		$iterator = 0;
		$last_record = $results->getRecord();
		foreach ($results as $result)
		{
			if ($iterator++ == 0)
			{
				$this->assertEquals($last_record, $result);
				continue;
			}
			$this->assertIsArray($result);
			$this->assertNotEquals($last_record, $result);
			$last_record = $result;

			$this->assertLessThanOrEqual($max, $iterator, 'Iterator has blown past the maximum detected results.');
		}

		$this->expectException(DatabaseException::class);
		DBWrapper::PResult('SELECT * FROM wrong_table');
	}

	public function testPExecute()
	{
		$max = 10;
		for ($i = 1; $i <= $max; $i++)
		{
			DBWrapper::PExecute('INSERT INTO test(name) VALUES(?)', ['hello_world'.$i]);
		}

		$results = DBWrapper::PExecute('SELECT * FROM test WHERE name LIKE ?', ['hello_world%']);
		$this->assertIsArray($results);
		$this->assertCount($max, $results);
		foreach ($results as $result)
		{
			$this->assertIsArray($result);
			$this->assertCount(2, $result);
		}

		$this->expectException(DatabaseException::class);
		DBWrapper::PExecute('SELECT * FROM wrong_table');
	}

	public function testPSingle()
	{
		for ($i = 1; $i <= 10; $i++)
		{
			DBWrapper::PExecute('INSERT INTO test(name) VALUES(?)', ['hello_world'.$i]);
		}

		$results = DBWrapper::PSingle('SELECT * FROM test WHERE name LIKE ?', ['hello_world%']);
		$this->assertIsArray($results);
		$this->assertCount(2, $results);
		$this->assertEquals(1, $results['KEYID']);
		$this->assertEquals('hello_world1', $results['name']);

		$this->expectException(DatabaseException::class);
		DBWrapper::PSingle('SELECT * FROM wrong_table');
	}

	public function testExecute()
	{
		$max = 10;
		for ($i = 1; $i <= $max; $i++)
		{
			DBWrapper::execute('INSERT INTO test(name) VALUES(\''.'hello_world'.$i.'\')');
		}

		$results = DBWrapper::execute('SELECT * FROM test');
		$this->assertIsArray($results);
		$this->assertCount($max, $results);
		foreach ($results as $result)
		{
			$this->assertIsArray($result);
			$this->assertCount(2, $result);
		}

		$this->expectException(DatabaseException::class);
		DBWrapper::execute('SELECT * FROM wrong_table');
	}

	public function testInsert()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testUpdate()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testDelete()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testStartTransaction()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testRollbackTransaction()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testQuote()
	{
		$this->markTestSkipped('Incomplete');
	}
}
