<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Tests;

use PHPUnit\Framework\TestCase;
use Porthorian\PDOWrapper\Models\DatabaseModel;
use Porthorian\PDOWrapper\DBPool;

class DBTest extends TestCase
{
	public const TEST_DB = 'test';

	public function setUp() : void
	{
		$model = new DatabaseModel(self::TEST_DB, '127.0.0.1', 'test_user', 'test_password');
		$model->setDSN('sqlite::memory:');
		$this->assertNull(DBPool::addPool($model));
	}

	public function tearDown() : void
	{
		$this->assertNull(DBPool::removePool(self::TEST_DB));
	}
}
