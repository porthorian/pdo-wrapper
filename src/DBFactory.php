<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper;

use Porthorian\PDOWrapper\Interfaces\DatabaseInterface;
use Porthorian\PDOWrapper\Models\DatabaseModel;

class DBFactory
{
	public static function create(DatabaseModel $model) : DatabaseInterface
	{
		return new DatabasePDO($model);
	}
}
