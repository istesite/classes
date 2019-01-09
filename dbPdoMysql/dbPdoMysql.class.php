<?php
class dbPdoMysql{
	protected static $conn;

	protected static $dbUser;
	protected static $dbPass;
	protected static $dbHost;
	protected static $dbName;

	protected static $lastError;
	protected static $lastQuery;

	protected static $beginTime;
	protected static $timing;

	protected static $debug = false;
	protected static $error_reporting;


	public function __construct($host = Null, $user = Null, $pass = Null, $databaseName = Null) {
		self::$error_reporting = error_reporting();
		error_reporting( self::$error_reporting & ~E_WARNING);
		self::$beginTime = microtime(true);

		if (!defined('PDO::ATTR_DRIVER_NAME')) {
			echo 'PDO unavailable'; exit;
		}

		if($host != Null and $user != Null and $pass != Null and $databaseName != Null){
			self::setDatabase($host, $user, $pass, $databaseName);
			self::connect();
		}
	}

	public function setDatabase($host, $user, $pass, $databaseName){
		self::$dbHost = $host;
		self::$dbUser = $user;
		self::$dbPass = $pass;
		self::$dbName = $databaseName;
	}

	public function connect() {
		try {
			$startTime               = microtime(TRUE);
			$con              = new PDO('mysql:host=' . self::$dbHost . ';dbname=' . self::$dbName, self::$dbUser, self::$dbPass);
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$conn = $con;
			self::$timing['connect'] = round((microtime(TRUE) - $startTime), 5);
			self::setCharCollation();
			return true;
		}
		catch (PDOException $e){
			self::$lastError = $e->getMessage();
			return false;
		}
	}

	public function setCharCollation($names = 'utf8', $char = 'utf8', $collation = 'utf8_general_ci') {
		$startTime = microtime(true);
		self::query("SET NAMES '$names'");
		self::query("SET CHARACTER SET '$char'");
		self::query("SET COLLATION_CONNECTION = '$collation'");
		self::$timing['setCharCollation'] = round((microtime(true) - $startTime), 5);
	}

	public function exec($sql) {
		return self::query($sql);
	}

	public function query($sql) {
		$con = self::$conn;
		$startTime = microtime(true);

		self::$lastQuery = $sql;
		try {
			$tur = strtolower(substr($sql, 0, 3));
			switch ($tur) {
				case "sel":
						$result = $con->prepare($sql);
						$result->execute();
					break;

				case "ins":
						$con->exec($sql);
						$result = true;
					break;

				case "upd":
						$con->exec($sql);
						$result = true;
					break;

				case "del":
						$con->exec($sql);
						$result = true;
					break;

				default:
						$result = $con->prepare($sql);
						$result->execute();
					break;
			}
			unset($tur);
		}
		catch (PDOException $e){
			self::$lastError = $e->getMessage();
			$result = false;
		}

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

	public function fetch_array($queryResult, $type = PDO::FETCH_BOTH) {
		return $queryResult->fetch($type);
	}

	public function fetch_array_num($queryResult) {
		return $queryResult->fetch(PDO::FETCH_NUM);
	}

	public function fetch_object($queryResult) {
		return $queryResult->fetch(PDO::FETCH_OBJ);
	}

	public function fetch_assoc($queryResult) {
		return $queryResult->fetch(PDO::FETCH_ASSOC);
	}

	public function num_rows($queryResult) {
		$numRows = $queryResult->rowCount();
		return $numRows;
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

		self::$timing['numRowsCount'] = round((microtime(true) - $startTime), 5);

		return $numRows;
	}

	public function affected_rows() {
		return self::$conn->rowCount();
	}

	public function free_result($queryResult) {
		do $queryResult->fetchAll();
		while ($queryResult->nextRowSet());
	}

	public function insertId() {
		return self::insert_id();
	}

	public function insert_id() {
		return self::$conn->lastInsertId();
	}

	public function result($sql, $rowIndex = 0, $colIndexOrName = Null) {
		if(!is_null($colIndexOrName)){
			$result = self::fetchArray($sql, $rowIndex);
			return $result[$colIndexOrName];
		}
		else{
			return self::fetchArray($sql, $rowIndex);
		}
	}

	protected function close() {
		return self::$conn = null;
	}

	public function clean($sql) {
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

	public function setDebug($state = false){
		self::$debug = $state;
		return true;
	}

	public function getTiming(){
		self::$timing['total'] = round((microtime(true) - self::$beginTime), 5);
		return self::$timing;
	}

	public function __destruct(){
		self::close();
		if(self::$debug){
			echo "\r\nTimes : \n<pre>" . print_r(self::getTiming(), true) . "</pre>\r\n";
			if(!is_null(self::$lastError)){
				echo "Error : \n<pre>" . print_r(self::$lastError, true) . "</pre>";
			}
		}
		error_reporting(self::$error_reporting);
	}
}