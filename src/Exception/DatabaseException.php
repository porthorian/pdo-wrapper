<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Exception;

use \Exception;
use \Throwable;

class DatabaseException extends Exception
{
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct($message, 45, $previous);
	}
}
