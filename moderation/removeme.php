<?php
require_once '../libs/User_Functions.php';
$user = new User_Functions();
echo $user->generateInvite();
?>