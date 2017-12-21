<?php
require_once 'DB_Functions.php';
$db = new DB_Functions();
echo $db->checkIfPublished("[1245234,208350923403285,12452345]");
?>