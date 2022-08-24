<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Tests\Suite;

use Porthorian\PDOWrapper\Tests\DBTest;
use Porthorian\PDOWrapper\DBPool;
use Porthorian\PDOWrapper\DatabasePDO;
use Porthorian\PDOWrapper\Exception\DatabaseException;
use Porthorian\PDOWrapper\Interfaces\QueryInterface;

class DatabasePDOTest extends DBTest
{
	public function testBindOtherValues()
	{
		$model = DBPool::getPools(self::TEST_DB)->getDatabaseModel();

		$pdo = new DatabasePDO($model);
		$pdo->connect();

		$this->assertTrue($pdo->isConnected());

		$result = $pdo->query('SELECT * FROM test WHERE name = ? OR name = ? OR name = ?', [2, true, null]);
		$this->assertInstanceOf(QueryInterface::class, $result);
	}
}
