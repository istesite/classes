<?php
class dbMysql {
	protected static $conn;

	protected static $dbUser;
	protected static $dbPass;
	protected static $dbHost;
	protected static $dbName;

	protected static $lastError;
	protected static $lastQuery;

	protected static $beginTime;
	protected static $timing;


	public function __construct($host = Null, $user = Null, $pass = Null, $databaseName = Null) {
		self::$beginTime = microtime(true);
		$startTime = microtime(true);

		if($host != Null and $user != Null and $pass != Null and $databaseName != Null){
			self::setDatabase($host, $user, $pass, $databaseName);
			if(self::connect()){
				self::$timing['connection'] = round((microtime(true) - $startTime), 5);
			}
		}
	}

	public function setDatabase($host, $user, $pass, $databaseName){
		self::$dbHost = $host;
		self::$dbUser = $user;
		self::$dbPass = $pass;
		self::$dbName = $databaseName;
	}

	protected function connect() {
		self::$conn = pg_connect("host=".self::$dbHost." port=5432 dbname=".self::$dbName." user=".self::$dbUser." password=".self::$dbPass) or die('Database sunucu bağlantı hatası.');
		self::setCharCollation();
		return true;
	}

	public function setCharCollation($names = 'utf8', $char = 'utf8', $collation = 'utf8_general_ci') {
		self::query("SET NAMES '$names'");
		self::query("SET CHARACTER SET '$char'");
		self::query("SET COLLATION_CONNECTION = '$collation'");
	}

	public function exec($sql) {
		return self::query($sql);
	}

	public function query($sql) {
		$startTime = microtime(true);
		//$sql = self::clean($sql);
		self::$lastQuery = $sql;
		self::$lastError =& pg_last_error(self::$conn);

		$tur = strtolower(substr($sql, 0, 3));
		switch ($tur) {
			case "sel":
				$result = pg_query(self::$conn, $sql);
				break;

			case "ins":
				$result = pg_query(self::$conn, $sql);
				break;

			case "upd":
				$result = pg_query(self::$conn, $sql);
				break;

			case "del":
				$result = pg_query(self::$conn, $sql);
				break;

			default:
				$result = pg_query($sql, self::$conn);
				break;
		}
		unset($tur);
		self::$timing['query'] = round((microtime(true) - $startTime), 5);
		return $result;
	}

	public function fetchArray($sql, $rowIndex = Null) {
		$startTime = microtime(true);
		$random = substr(md5(time()),0,6);
		$resultsx = array();

		if (!is_null($rowIndex) and $rowIndex >= 0 and strtolower(substr($sql, 0, 3)) == 'sel') {
			$sql = 'SELECT qRL_' . $random . '.* FROM (' . $sql . ') AS qRL_' . $random . ' LIMIT ' . $rowIndex . ', 1';
			$resultsx = self::fetchArray($sql);
			$resultsx = $resultsx[0];
		}
		else {
			$sqlQuery = self::query($sql);
			while ($rows = self::fetch_assoc($sqlQuery)) {
				$resultsx[] = $rows;
			}
		}

		self::$timing['fetchArray'] = round((microtime(true) - $startTime), 5);
		return $resultsx;
	}

	public function fetchObject($sql, $rowIndex = Null) {
		$startTime = microtime(true);
		$random = substr(md5(time()),0,6);
		$resultsx = array();

		if (!is_null($rowIndex) and $rowIndex >= 0 and strtolower(substr($sql, 0, 3)) == 'sel') {
			$sql = 'SELECT qRL_' . $random . '.* FROM (' . $sql . ') AS qRL_' . $random . ' LIMIT ' . $rowIndex . ', 1';
			$resultsx = self::fetchObject($sql);
			$resultsx = $resultsx[0];
		}
		else {
			$sqlQuery = self::query($sql);
			while ($rows = self::fetch_object($sqlQuery)) {
				$resultsx[] = $rows;
			}
		}

		self::$timing['fetchObject'] = round((microtime(true) - $startTime),3);

		return $resultsx;
	}

	public function fetch_array($queryResult, $row = Null, $type = PGSQL_BOTH) {
		return pg_fetch_array($queryResult, $row, $type);
	}

	public function fetch_array_num($queryResult, $row=Null) {
		return pg_fetch_array($queryResult, $row, PGSQL_NUM);
	}

	public function fetch_object($queryResult, $row = Null) {
		return pg_fetch_object($queryResult, $row);
	}

	public function fetch_assoc($queryResult, $row = Null) {
		return pg_fetch_assoc($queryResult, $row);
	}

	public function num_rows($queryResult) {
		return pg_num_rows($queryResult);
	}

	public function numRows($sql) {
		$startTime = microtime(true);

		$numRows = self::num_rows(self::query($sql));

		self::$timing['numRows'] = round((microtime(true) - $startTime), 5);

		return $numRows;
	}

	public function numRowsCount($sql) {
		$startTime = microtime(true);

		$sql = preg_replace('/^select .*? from (.*?)/ix', 'SELECT count(*) AS dfsa1231fde5 FROM $1', $sql);
		$query = self::fetchArray($sql, 0);
		$numRows = $query['dfsa1231fde5'];

		self::$timing['numRows'] = round((microtime(true) - $startTime), 5);

		return $numRows;
	}

	public function affected_rows() {
		return pg_affected_rows(self::$conn);
	}

	public function free_result($queryResult) {
		@pg_free_result($queryResult);
		unset($queryResult);
	}

	public function insertId() {
		return self::insert_id();
	}

	public function insert_id() {
		return pg_last_oid(self::$conn);
	}

	public function result($queryResult, $rowIndex = 0, $colIndexOrName = Null) {
		if(!is_null($colIndexOrName)){
			return pg_fetch_result($queryResult, $rowIndex, $colIndexOrName);
		}
		else{
			return pg_fetch_result($queryResult, $rowIndex);
		}
	}

	protected function close() {
		return pg_close(self::$conn);
	}

	public function clean($sql) {
		//return mysql_real_escape_string($sql);

		$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
		$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

		return str_replace($search, $replace, $sql);
	}

	public function nextRow($tableName, $fieldName = 'row', $step = 10) {
		$sql = "SELECT MAX(" . $fieldName . ") AS maxRow FROM " . $tableName;
		if ($fetchResult = self::fetchArray($sql, 0)) {
			$newRow = $fetchResult["maxRow"];
			if ($newRow % $step == 0) {
				return $newRow + $step;
			}
			else {
				$modRow = $step - ($newRow % $step);

				return $newRow + $step + $modRow;
			}
		}
		else {
			return 0;
		}
	}

	public function getDatabaseName() {
		return self::$dbName;
	}

	public function getLastError() {
		return self::$lastError;
	}

	public function getLastQuery() {
		return self::$lastQuery;
	}

	public function getTiming(){
		self::$timing['total'] = round((microtime(true) - self::$beginTime), 5);
		return self::$timing;
	}

	public function __destruct(){
		self::close();
	}
}