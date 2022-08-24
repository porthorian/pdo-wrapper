<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Tests\Suite;

use Porthorian\PDOWrapper\Tests\DBTest;
use Porthorian\PDOWrapper\DBWrapper;
use Porthorian\PDOWrapper\Models\DBResult;

class DBResultTest extends DBTest
{
	public function setUp() : void
	{
		parent::setUp();
		DBWrapper::execute('CREATE TABLE test(
			KEYID INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT
		)');
	}

	public function tearDown() : void
	{
		DBWrapper::execute('DROP TABLE test');
		parent::tearDown();
	}

	public function testIteration()
	{
		$max = 10;
		for ($i = 0; $i < $max; $i++)
		{
			DBWrapper::execute('INSERT INTO test (KEYID) VALUES(null)');
		}

		$factory = DBWrapper::factory('SELECT * FROM test');
		$iterator = new DBResult($factory);

		$this->assertCount($max, $iterator);
		$this->assertEquals(1, $iterator->getRecord()['KEYID']);
		$counter = 0;
		$max_iteration = 3;
		for ($i = 0; $i < $max_iteration; $i++)
		{
			$pointer_expected = 0;
			// Rewind will be executed 2 times, but requery should only happen once.
			foreach ($iterator as $key => $values)
			{
				$this->assertEquals($pointer_expected++, $key);
				$this->assertEquals($pointer_expected, $values['KEYID']);
				$counter++;
			}
		}

		$this->assertEquals($max_iteration * $max, $counter);
	}
}
