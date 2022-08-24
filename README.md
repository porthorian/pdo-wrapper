# PDO-Wrapper
A Mysql Singleton PDO Database Wrapper for ease of use.

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/Porthorian/pdo-wrapper/main/LICENSE)
[![PHP Version Require](http://poser.pugx.org/porthorian/pdo-wrapper/require/php)](https://packagist.org/packages/porthorian/pdo-wrapper)
[![Latest Stable Version](http://poser.pugx.org/porthorian/pdo-wrapper/v)](https://packagist.org/packages/porthorian/pdo-wrapper)
[![PHP Tests](https://github.com/Porthorian/pdo-wrapper/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/Porthorian/pdo-wrapper/actions/workflows/php.yml)

## Usage

### Adding a Database Pool for connecting.
```php
<?php

declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use Porthorian\PDOWrapper\DatabaseModel;
use Porthorian\PDOWrapper\DBPool;
use Porthorian\PDOWrapper\DBWrapper;

$dbname = 'the-schema-you-will-connect-to';
$host = '127.0.0.1';
$user = 'your-amazing-username';
$password = 'your-secret-password';
$model = new DatabaseModel($dbname, $host, $user, $password);

//$model->setPort(3306); //Using another port? you can set that on the model.
DBPool::addPool($model);

/**
 * You can opt to connect to the database if you want to do that now or wait till the first call of that dbname.
 * DBWrapper will connect to it automatically.
 * Example:
 */
// DBPool::connectDatabase($dbname);

/**
 * Wanna set a DefaultDatabase that isn't the first database pool added?
 * You can do that with DBWrapper.
 */
// DBWrapper::setDefaultDB($dbname);
```

### Using the client

DBWrapper will be your entry point to almost all queries that you will probably ever have to do. There are a variety of functions that do different things that allow you to execute your SQL related queries.

These are the functions that are commonly used but there are more inside DBWrapper that are static functions.

* execute - Execute a raw sql statement with no prepared statement support. Fetches all the rows from the query and loads it into memory
* PExecute - Used to executed prepared statements with delinated by ?. The result set is exactly like execute.
* PResult - This is an iterator based. Meaning it does not load the result set into memory it will use a mysql cursor to iterate through the rows.

```php
<?php

declare(strict_types=1);

require(__DIR__.'/../vendor/autoload.php');

use Porthorian\PDOWrapper\DBWrapper;

/**
 * This query will return an multidimensional array
 * Each record/row returned will be returned in the way the query has outputted them.
 */
$results = DBWrapper::PExecute('SELECT * FROM your_table WHERE x = ?', [$your_statement]);

foreach ($results as $result)
{
	var_dump($result); // The row being returned as an array. All values pertaining to the row will be here.
}
```

### Exception Handling
All errors are handled by an exception.
* Any SQL related error will throw a DatabaseException with the parent exception containing a PDOException with the information.
* Any configuration error will throw a InvalidArgumentException
