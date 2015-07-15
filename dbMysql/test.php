<?php
include_once "dbMysql.class.php";
$db = new dbMysql('localhost', 'root', '', 'dailychanges');
echo "<pre>".var_export($db->fetchArray("SELECT * FROM domain LIMIT 100", 1), true)."</pre>";