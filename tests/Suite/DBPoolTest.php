<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Tests\Suite;

use Porthorian\PDOWrapper\Tests\DBTest;
use Porthorian\PDOWrapper\DBPool;
use Porthorian\PDOWrapper\Interfaces\DatabaseInterface;
use Porthorian\PDOWrapper\Models\DatabaseModel;
use Porthorian\PDOWrapper\Exception\DatabaseException;
use Porthorian\PDOWrapper\Exception\InvalidConfigException;

class DBPoolTest extends DBTest
{
	public function testConnectDatabase()
	{
		$this->assertNull(DBPool::connectDatabase(self::TEST_DB));
		$this->assertTrue(DBPool::isDatabaseConnectionAvailable(self::TEST_DB));

		$db_model = new DatabaseModel('random', '126', '324324', '343434');
		$pool = new DBPool($db_model);

		$this->expectException(DatabaseException::class);
		$pool->connect(1);
	}

	public function testDisconnectDatabase()
	{
		$this->assertNull(DBPool::disconnectDatabase(self::TEST_DB));

		$this->assertFalse(DBPool::isDatabaseConnectionAvailable(self::TEST_DB));
	}

	public function testGetDBI()
	{
		$this->assertNull(DBPool::connectDatabase(self::TEST_DB));
		$dbi = DBPool::getDBI(self::TEST_DB);
		$this->assertInstanceOf(DatabaseInterface::class, $dbi);
	}

	public function testIsDatabaseConnectionAvailable()
	{
		$this->assertNull(DBPool::connectDatabase(self::TEST_DB));
		$this->assertTrue(DBPool::isDatabaseConnectionAvailable(self::TEST_DB));
		$this->assertNull(DBPool::disconnectDatabase(self::TEST_DB));
		$this->assertFalse(DBPool::isDatabaseConnectionAvailable(self::TEST_DB));
	}

	public function testGetPools()
	{
		$this->assertInstanceOf(DBPool::class, DBPool::getPools(self::TEST_DB));

		$creds = DBPool::getPools();
		$this->assertIsArray($creds);
		$this->assertNotEmpty($creds);
		$this->assertCount(1, $creds);

		$this->expectException(InvalidConfigException::class);
		DBPool::getPools('unknown');
	}
}
