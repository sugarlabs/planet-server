<?php
require_once 'DB_Functions.php';
$db = new DB_Functions();
$ProjectID = 1245234;
echo $db->downloadProject($ProjectID);
?>