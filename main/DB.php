<?php
/*
class DB
{
	private $connection;
	public __construct($config)
	{
		$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	}	

	function mysql_entities_fix_string($string)
	{
		return htmlentities($this->mysql_fix_string($string));
	}

	function mysql_fix_string($string)
	{
		if (get_magic_quotes_gpc()) 
			$string = stripslashes($string);
		return $this->connection->real_escape_string($string);
	}

	static function SanitizeString($var)
	{
		$var = strip_tags($var);
		$var = htmlentities($var);
		return stripslashes($var);
	}
}*/