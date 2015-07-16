<?php
header('Content-Type: text/html; charset=utf-8');
include_once "dbMysql.class.php";
//$db = new dbMysql('localhost', 'sqluser', 'sql11259375', 'istesite_sinema');
$db = new dbMysql('localhost', 'sqluser', 'sql11259375', 'dailychanges');
echo "Toplam : ".$db->numRowsCount("SELECT * FROM domain ")."\n<br>";
//echo "Toplam : ".$db->numRows("SELECT * FROM domain");
//echo "<pre>".var_export($db->fetchArray("SELECT * FROM domain limit 1000"), true)."</pre>";
echo "exec time:".print_r($db->getTiming(), true);