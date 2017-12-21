<?php
require_once 'DB_Functions.php';
$db = new DB_Functions();
$ProjectID = 1245234;
$UserID = 12334;
$like = false;
if ($db->likeProject($ProjectID,$UserID,$like)){
  echo "true";
} else {
  echo "false";
}
?>