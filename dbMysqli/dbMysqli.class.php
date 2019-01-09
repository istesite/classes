<?php
class dbMysqli {
	protected static $conn;

	protected static $dbUser;
	protected static $dbPass;
	protected static $dbHost;
	protected static $dbName;

	protected static $lastError;
	protected static $lastQuery;

	protected static $beginTime;
	protected static $timing;

	protected static $beginMemory;
	protected static $memorys;

	protected static $debug = false;
	protected static $error_reporting;


	public function __construct($host = Null, $user = Null, $pass = Null, $databaseName = Null) {
		self::$beginTime = microtime(true);
		self::$beginMemory = memory_get_peak_usage(true);

		self::$error_reporting = error_reporting();
		error_reporting( self::$error_reporting & ~E_WARNING);

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
		$startTime = microtime(true);
		self::$conn = mysqli_connect(self::$dbHost, self::$dbUser, self::$dbPass, self::$dbName);
		if(mysqli_connect_errno()){
			self::$lastError = "Database bağlantı hatası";
			self::$timing['connect'] = round((microtime(true) - $startTime), 5);
			return false;
		}
		self::addTiming('connect', round((microtime(true) - $startTime), 5));

		self::setCharCollation();
		return true;
	}

	public function setCharCollation($names = 'utf8', $char = 'utf8', $collation = 'utf8_general_ci') {
		$startTime = microtime(true);
		self::query("SET NAMES '$names'");
		self::query("SET CHARACTER SET '$char'");
		self::query("SET COLLATION_CONNECTION = '$collation'");
		self::addTiming('setCharCollation', round((microtime(true) - $startTime), 5));
	}

	public function exec($sql) {
		return self::query($sql);
	}

	public function query($sql) {
		$startTime = microtime(true);
		self::$lastQuery = $sql;
		self::$lastError =& mysqli_connect_error();

		$tur = strtolower(substr($sql, 0, 3));
		switch ($tur) {
			case "sel":
				$result = mysqli_query(self::$conn, $sql);
				break;

			case "ins":
				$result = mysqli_query(self::$conn, $sql);
				break;

			case "upd":
				$result = mysqli_query(self::$conn, $sql);
				break;

			case "del":
				$result = mysqli_query(self::$conn, $sql);
				break;

			default:
				$result = mysqli_query(self::$conn, $sql);
				break;
		}
		unset($tur);
		self::addTiming('query', round((microtime(true) - $startTime), 5), $sql);
		return $result;
	}

	public function fetchArray($sql, $rowIndex = Null, $field = Null) {
		$startTime = microtime(true);
		$random = substr(md5(time()),0,6);
		$resultsx = array();

		if (!is_null($rowIndex) and $rowIndex >= 0 and strtolower(substr($sql, 0, 3)) == 'sel') {
			$sql = 'SELECT qRL_' . $random . '.* FROM (' . $sql . ') AS qRL_' . $random . ' LIMIT ' . $rowIndex . ', 1';
			$resultsx = self::fetchArray($sql);

			if(!is_null($field)){
				$resultsx = $resultsx[0][$field];
			}
			else{
				$resultsx = $resultsx[0];
			}
		}
		else {
			$sqlQuery = self::query($sql);
			while ($rows = self::fetch_assoc($sqlQuery)) {
				$resultsx[] = $rows;
			}
		}

		self::addTiming('fetchArray', round((microtime(true) - $startTime), 5));
		return $resultsx;
	}

	public function fetchObject($sql, $rowIndex = Null, $field = Null) {
		$startTime = microtime(true);
		$random = substr(md5(time()),0,6);
		$resultsx = array();

		if (!is_null($rowIndex) and $rowIndex >= 0 and strtolower(substr($sql, 0, 3)) == 'sel') {
			$sql = 'SELECT qRL_' . $random . '.* FROM (' . $sql . ') AS qRL_' . $random . ' LIMIT ' . $rowIndex . ', 1';
			$resultsx = self::fetchObject($sql);
			if(!is_null($field)) {
				$resultsx = $resultsx[0][$field];
			}
			else{
				$resultsx = $resultsx[0];
			}
		}
		else {
			$sqlQuery = self::query($sql);
			while ($rows = self::fetch_object($sqlQuery)) {
				$resultsx[] = $rows;
			}
		}

		self::addTiming('fetchObject', round((microtime(true) - $startTime),5));

		return $resultsx;
	}

	public function fetch_array($queryResult, $type = MYSQLI_BOTH) {
		return mysqli_fetch_array($queryResult, $type);
	}

	public function fetch_array_num($queryResult) {
		return mysqli_fetch_array($queryResult, MYSQLI_NUM);
	}

	public function fetch_object($queryResult) {
		return mysqli_fetch_object($queryResult, self::$conn);
	}

	public function fetch_assoc($queryResult) {
		return mysqli_fetch_assoc($queryResult);
	}

	public function num_rows($queryResult) {
		return mysqli_num_rows($queryResult);
	}

	public function numRows($sql) {
		$startTime = microtime(true);

		$numRows = self::num_rows(self::query($sql));

		self::addTiming('numRows', round((microtime(true) - $startTime), 5));

		return $numRows;
	}

	public function numRowsCount($sql) {
		$startTime = microtime(true);

		$sql = preg_replace('/^select .*? from (.*?)/ix', 'SELECT count(*) AS dfsa1231fde5 FROM $1', $sql);
		$query = self::fetchArray($sql, 0);
		$numRows = $query['dfsa1231fde5'];

		self::addTiming('numRows', round((microtime(true) - $startTime), 5));

		return $numRows;
	}

	public function affected_rows() {
		return mysqli_affected_rows(self::$conn);
	}

	public function free_result($queryResult) {
		@mysqli_free_result($queryResult);
		unset($queryResult);
	}

	public function insertId() {
		return self::insert_id();
	}

	public function insert_id() {
		return mysqli_insert_id(self::$conn);
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
		return mysqli_close(self::$conn);
	}

	public function clean($sql) {
		return mysqli_real_escape_string($sql);
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

	public function addTiming($str, $time, $sql=''){
		if(isset(self::$timing[$str])){
			if(is_array(self::$timing[$str])){
				self::$timing[$str][] = $time . ' sn' . ($sql != '' ? " - [ $sql ]" : '');
			}
			else{
				self::$timing[$str] = array(self::$timing[$str]);
				self::$timing[$str][] = $time . ' sn' . ($sql != '' ? " - [ $sql ]" : '');
			}
		}
		else{
			self::$timing[$str] = $time . ' sn' . ($sql != '' ? " - [ $sql ]" : '');
		}
	}

	public function getTiming(){
		self::$timing['total'] = round((microtime(true) - self::$beginTime), 5);
		return self::$timing;
	}

	public function getMemory(){
		return (memory_get_peak_usage(true) / 1024 / 1024) . ' MB';
	}

	public function __destruct(){
		self::close();
		if(self::$debug){
			echo "\r\nTimes : \n<pre>" . print_r(self::getTiming(), true) . "</pre>\r\n";
			echo "\r\nMemory : \n<pre>" . self::getMemory() . "</pre>\r\n";
			if(!is_null(self::$lastError)){
				echo "Error : \n<pre>" . print_r(self::$lastError, true) . "</pre>";
			}
		}
		error_reporting(self::$error_reporting);
	}
}