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
		$model = new DatabaseModel(self::TEST_DB, getenv('DB_HOST'), 'root', 'test_password');
		$model->setPort((int)getenv('DB_PORT'));
		$this->assertNull(DBPool::addPool($model));
	}

	public function tearDown() : void
	{
		$this->assertNull(DBPool::removePool(self::TEST_DB));
	}
}
