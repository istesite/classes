<?php
header('Content-Type: text/html; charset=utf-8');
include_once "dbMysql.class.php";
//$db = new dbMysql('localhost', 'sqluser', 'sql11259375', 'istesite_sinema');
$db = new dbMysql('127.0.0.1', 'root', '', 'istesite_taemca');
//echo "Toplam : ".$db->numRowsCount("SELECT * FROM domain ");
//echo "Toplam : ".$db->numRows("SELECT * FROM domain");
echo "<pre>".var_export($db->fetchObject("SELECT p.id, p.page_name, pt.page_title, pt.page_content, pt.page_keyword, p.indate, pt.lang FROM pages p INNER JOIN pages_trans pt ON pt.parent_id = p.id WHERE pt.lang='en'"), true)."</pre>";
//echo "exec time:".print_r($db->getTiming(), true);