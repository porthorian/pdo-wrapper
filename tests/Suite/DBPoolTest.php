<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Tests\Suite;

use PHPUnit\Framework\TestCase;
use Porthorian\PDOWrapper\DBPool;
use Porthorian\PDOWrapper\Exception\DatabaseException;
use Porthorian\PDOWrapper\Models\DatabaseModel;

class DBPoolTest extends TestCase
{
	public function testConnectDatabase()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testDisconnectDatabase()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testGetDBI()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testIsDatabaseConnectionAvailable()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testGetPoolErrors()
	{
		$this->markTestSkipped('Incomplete');
	}

	public function testAddPoolCred()
	{
		$db_name = 'test';
		$model = new DatabaseModel($db_name, '127.0.0.1', 'test_user', 'test_password');

		$this->assertNull(DBPool::addPoolCred($model));
	}

	/**
	 * @depends testAddPoolCred
	 */
	public function testGetPoolCreds()
	{
		$this->assertInstanceOf(DatabaseModel::class, DBPool::getPoolCreds('test'));

		$creds = DBPool::getPoolCreds();
		$this->assertIsArray($creds);
		$this->assertNotEmpty($creds);
		$this->assertCount(1, $creds);

		$this->expectException(DatabaseException::class);
		DBPool::getPoolCreds('unknown');
	}
}