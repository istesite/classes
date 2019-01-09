<?php
header('Content-Type: text/html; charset=utf-8');
include_once "dbMysql.class.php";

$db = new dbMysql('db_server', 'db_user', 'db_pass', 'db_name');

echo "<pre>".var_export($db->fetchObject("SELECT p.id, p.page_name, pt.page_title, pt.page_content, pt.page_keyword, p.indate, pt.lang FROM pages p INNER JOIN pages_trans pt ON pt.parent_id = p.id WHERE pt.lang='en'"), true)."</pre>";
