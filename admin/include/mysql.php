<?php

define("MY_DB_HOST", "127.0.0.1");
define("MY_DB_USER", "root");
define("MY_DB_PWD", "root");
define("MY_DB_DATABASE", "gelic_vw");

class Mysql
{
	public $Row = array();
	public $Errno = array();

	private $_mysqli;
	private $_query = array();
	private $_res = array();

	public function __construct($Database = "")
	{
		if (strlen($Database) > 0)
		{
			$this->_mysqli = new mysqli(MY_DB_HOST, MY_DB_USER, MY_DB_PWD, $Database);
			$this->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
			$this->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'NO_ZERO_IN_DATE',''))");
			$this->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'NO_ZERO_DATE',''))");
		}
		else
		{
			$this->_mysqli = new mysqli(MY_DB_HOST, MY_DB_USER, MY_DB_PWD, MY_DB_DATABASE);
			$this->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
			$this->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'NO_ZERO_IN_DATE',''))");
			$this->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'NO_ZERO_DATE',''))");
		}
	}

	public function selectDB($Database)
	{
		$this->_mysqli->select_db($Database);
	}

	public function query($query, $index = 0)
	{
		if (isset($this->_query[$index]))
			if (is_object($this->_query[$index]))
				$this->_query[$index]->free();

		$this->_query[$index] = $this->_mysqli->query($query);
		$this->Row[$index] = 0;
		$this->Errno[$index] = $this->_mysqli->errno;
	}

	public function nextRecord($index = 0)
	{
		$this->_res[$index] = $this->_query[$index]->fetch_assoc();
		$this->Row[$index] += 1;
		$this->Errno[$index] = $this->_mysqli->errno;
		return is_array($this->_res[$index]);
	}

	public function nf($index = 0)
	{
		return intval($this->_query[$index]->num_rows);
	}
	
	public function f($Name, $index = 0)
	{
		return $this->_res[$index][$Name];
	}
	
	public function li()
	{
		return intval($this->_mysqli->insert_id);
	}

	public function afrows()
	{
		return intval($this->_mysqli->affected_rows);
	}

	public function escapeString($str)
	{
		return $this->_mysqli->real_escape_string($str);
	}

	public function __destruct()
	{
		$this->_mysqli->close();
		for ($i=0; $i<count($this->_query); $i++)
			if (is_object($this->_query[$i]))
				$this->_query[$i]->free();
	}
}

?>
