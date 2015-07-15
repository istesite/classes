<?php
header('Content-Type: text/html; charset=utf-8');
include_once "dbMysql.class.php";
$db = new dbMysql('localhost', 'sqluser', 'sql11259375', 'istesite_sinema');
echo "Toplam : ".$db->numRows("SELECT id, film_name, film_url FROM filmler Limit 125"); exit;
echo "<pre>".var_export($db->fetchArray("SELECT id, film_name, film_url FROM filmler Limit 125"), true)."</pre>";