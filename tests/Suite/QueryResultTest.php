<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Tests\Suite;

use Porthorian\PDOWrapper\Tests\DBTest;
use Porthorian\PDOWrapper\Models\QueryResult;
use Porthorian\PDOWrapper\Models\DBResult;

class QueryResultTest extends DBTest
{
	public function testUninitalized()
	{
		$result = new QueryResult(null);

		$this->assertFalse($result->isInitialized());

		$this->assertEmpty($result->fetchResult());
		$this->assertIsArray($result->getResults());

		$this->assertEmpty($result->fetchAllResults());
		$this->assertIsArray($result->getResults());

		$this->assertEmpty($result->getResults());

		$this->assertInstanceOf(DBResult::class, $result->getDBResult());
		$this->assertInstanceOf(DBResult::class, $result->getResults());
	}
}
