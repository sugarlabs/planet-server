<?php
require_once 'DB_Functions.php';
$db = new DB_Functions();
$UserID = 1234567890;
$JSON = '[1,2,3]';
echo $db->downloadProjectList($JSON,"liked",0,1);
?>