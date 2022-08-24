<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Tests\Suite;

use Porthorian\PDOWrapper\Tests\DBTest;
use Porthorian\PDOWrapper\DBPool;
use Porthorian\PDOWrapper\DatabasePDO;
use Porthorian\PDOWrapper\Interfaces\QueryInterface;
use Porthorian\PDOWrapper\DBWrapper;

class DatabasePDOTest extends DBTest
{
	public function setUp() : void
	{
		parent::setUp();
		DBWrapper::factory('CREATE TABLE test(
			KEYID INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			num INT(10) NOT NULL,
			boo BOOLEAN,
			nulled VARCHAR(255) NULL
		)');
	}

	public function tearDown() : void
	{
		DBWrapper::factory('DROP TABLE test');
		parent::tearDown();
	}

	public function testBindOtherValues()
	{
		$model = DBPool::getPools(self::TEST_DB)->getDatabaseModel();

		$pdo = new DatabasePDO($model);
		$pdo->connect();

		$this->assertTrue($pdo->isConnected());

		$boolean = true;
		$pdo->query('INSERT INTO test (name, num, boo, nulled) VALUES (?,?,?,?)', ['hello', 54, $boolean, null]);
		$query = $pdo->query('SELECT * FROM test');
		$this->assertInstanceOf(QueryInterface::class, $query);

		$result = $query->fetchResult();

		$this->assertIsString($result['name']);
		$this->assertIsInt($result['num']);
		$this->assertTrue($result['boo'] === ($boolean ? 1 : 0));
		$this->assertNull($result['nulled']);
	}
}
