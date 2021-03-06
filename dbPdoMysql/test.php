<?php
header('Content-Type: text/html; charset=utf-8');
include_once "dbPdoMysql.class.php";

# CONNECTION & SETTINGS
$db = new dbPdoMysql('db_server', 'db_username', 'db_password', 'db_name');
$db->setDebug(true);

# SELECT fetchArray
echo "<h4>SELECT fetchArray TEST</h4>";
$sqlSelect = "SELECT * FROM pages";
$rowSelect = $db->fetchArray($sqlSelect);
echo "<pre>".var_export($rowSelect, true);

# SELECT fetchObject
echo "<h4>SELECT fetchObject TEST</h4>";
$sqlSelect = "SELECT * FROM pages";
$rowSelect = $db->fetchObject($sqlSelect);
echo "<pre>".var_export($rowSelect, true);

# NUM ROWS
echo "<h4>NUM ROWS TEST</h4>";
$sqlSelect = "SELECT * FROM pages";
$rowSelect = $db->numRows($sqlSelect);
echo "<pre>".var_export($rowSelect, true);


# NUM ROWS COUNT
echo "<h4>NUM ROWS COUNT TEST</h4>";
$sqlSelect = "SELECT * FROM pages";
$rowSelect = $db->numRowsCount($sqlSelect);
echo "<pre>".var_export($rowSelect, true);