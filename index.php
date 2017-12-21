<?php
require_once 'DB_Functions.php';
$db = new DB_Functions();
echo $db->searchProjects("timbre project", 0, 25);
?>