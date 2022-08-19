<?php

declare(strict_types=1);

namespace Porthorian\PDOWrapper\Models;

class DatabaseModel
{
	protected string $dbname;
	protected string $host;
	protected string $user;
	protected string $password;
	protected string $charset;

	protected string $dsn;

	public function __construct(string $dbname, string $host, string $user, string $password, string $charset = 'UTF8')
	{
		$this->setDBName($dbname);
		$this->setHost($host);
		$this->setUser($user);
		$this->setPassword($password);
		$this->setCharset($charset);

		$this->setDSN('mysql:host=' . $this->getHost() . ';dbname=' . $this->getDBName() . ';charset=' . $this->getCharset());
	}

	public function getDBName() : string
	{
		return $this->dbname;
	}
	public function getHost() : string
	{
		return $this->host;
	}
	public function getUser() : string
	{
		return $this->user;
	}
	public function getPassword() : string
	{
		return $this->password;
	}
	public function getCharset() : string
	{
		return $this->charset;
	}
	public function getDSN() : string
	{
		return $this->dsn;
	}

	// Setters
	public function setDBName(string $dbname) : void
	{
		$this->dbname = $dbname;
	}
	public function setHost(string $host) : void
	{
		$this->host = $host;
	}
	public function setUser(string $user) : void
	{
		$this->user = $user;
	}
	public function setPassword(string $password) : void
	{
		$this->password = $password;
	}
	public function setCharset(string $charset)
	{
		$this->charset = $charset;
	}
	public function setDSN(string $dsn) : void
	{
		$this->dsn = $dsn;
	}
}
