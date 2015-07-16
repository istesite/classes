<?php
include_once "dbSqlite.class.php";
$db = new dbSqlite('newDB.sqlite');

//$db->query("CREATE TABLE files (id INTEGER PRIMARY KEY, filename TEXT, content BLOB);");

/*
for($i=0; $i<= 100; $i++){
	$db->query('insert into files (filename, content) VALUES ("test'.$i.'" , "'.$i.'-fdafdfdfd")');
	echo $i."\t";
}
*/

$result = $db->fetchArray("SELECT * FROM files");
print_r($result, true);