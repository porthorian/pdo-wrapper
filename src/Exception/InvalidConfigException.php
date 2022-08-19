<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Exception;

use \Exception;
use \Throwable;

class InvalidConfigException extends Exception
{
	public function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct($message, 47, $previous);
	}
}
