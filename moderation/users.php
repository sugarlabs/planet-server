<?php
require_once '../libs/User_Functions.php';
require_once '../libs/strings.php';
$users = new User_Functions();
if (!isset($_POST["action"])){
	die($db->unsuccessfulResult(ERROR_ACCESS_DENIED));
} else {
	switch ($_POST["action"]) {
        case 'add':
            if (isset($_POST["token"])&&isset($_POST["username"])&&isset($_POST["email"])&&isset($_POST["password"])){
                die($users->addNewUser($_POST["token"], $_POST["username"], $_POST["email"], $_POST["password"], true));
            }
            break;
        case 'login':
            if (isset($_POST["username"])&&isset($_POST["password"])&&isset($_POST["ssi"])){
                die($users->checkUser($_POST["username"], $_POST["password"], ($_POST["ssi"]=="true" ? true : false)));
            }
            break;
        default:
            die($db->unsuccessfulResult(ERROR_INVALID_ACTION));
            break;
    }
    die($db->unsuccessfulResult(ERROR_INVALID_PARAMETERS));
}
?>