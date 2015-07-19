<?php
include_once "dbSqlite.class.php";
$db = new dbSqlite('domainler');

/*$db->query("CREATE TABLE videos (
	id INTEGER PRIMARY KEY,
   dailymotion_id TEXT,
   title TEXT,
   descr TEXT,
   tags TEXT,
   lang TEXT,
   video_url TEXT,
   orj_id INTEGER,
   indate INTEGER,
   status INTEGER,
   dailymotion_TEXT,
   dailymotion_channel TEXT,
   dailymotion_counter INTEGER,
   duration INTEGER);");*/

/*
for($i=0; $i<= 100; $i++){
	$db->query('insert into files (filename, content) VALUES ("test'.$i.'" , "'.$i.'-fdafdfdfd")');
	echo $i."\t";
}
*/


echo "<pre>".var_export($db->fetchArray("SELECT * FROM files"), true)."</pre>\n<br>";
echo "Toplam : ".$db->numRows("SELECT * FROM files").print_r($db->getTiming(), true);
