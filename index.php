<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
require_once 'libs/DB_Functions.php';
require_once 'libs/config.php';
require_once 'libs/strings.php';
require_once 'libs/User_Functions.php';
$db = new DB_Functions();
//mod actions
$UserID = -1;
$users = new User_Functions();
if (isset($_COOKIE["session"])){
    $id = $users->checkJWTToken($_COOKIE["session"]);
    if ($id!=false){
        if (isset($_POST["api-key"], $_POST["action"])){
            if ($_POST["api-key"]==API_KEY){
                $UserID = intval($id);
                switch ($_POST["action"]) {
                    case 'deleteProject':
                        if (isset($_POST["ProjectID"])){
                            die($db->deleteProject($_POST["ProjectID"]));
                        }
                        break;
                    case 'unreportProject':
                        if (isset($_POST["ProjectID"])){
                            die($db->unreportProject($_POST["ProjectID"]));
                        }
                        break;
                    case 'generateInvite':
                        die($users->generateInvite());
                        break;
                }
            } else {
                die($db->unsuccessfulResult("ERROR_ACCESS_DENIED3"));
            }
        } else {
            die($db->unsuccessfulResult("ERROR_ACCESS_DENIED2"));
        }
    }
}
if (isset($_POST["api-key"], $_POST["action"])){
    if ($_POST["api-key"]==API_KEY){
        if ($UserID==-1){
            if (isset($_COOKIE["UserID"])){
                $UserID = intval($_COOKIE["UserID"]);
            } else {
                $UserID = $db->generateID();
            }
        }
        //TODO: Are we checking if this is non-null? At the moment if $_COOKIE["UserID"] is not int, $UserID will be null
        switch ($_POST["action"]) {
            case 'addProject':
                if (isset($_POST["ProjectJSON"])){
                    die($db->addProject($_POST["ProjectJSON"], $UserID));
                }
                break;
            case 'downloadProjectList':
                if (isset($_POST["ProjectTags"],$_POST["ProjectSort"],$_POST["Start"],$_POST["End"])){
                    die($db->downloadProjectList($UserID,$_POST["ProjectTags"],$_POST["ProjectSort"],$_POST["Start"],$_POST["End"]));
                }
                break;
            case 'getProjectDetails':
                if (isset($_POST["ProjectID"])){
                    die($db->getProjectDetails($_POST["ProjectID"]));
                }
                break;
            case 'downloadProject':
                if (isset($_POST["ProjectID"])){
                    die($db->downloadProject($_POST["ProjectID"]));
                }
                break;
            case 'getTagManifest':
                die($db->getTagManifest());
                break;
            case 'searchProjects':
                if (isset($_POST["Search"],$_POST["ProjectSort"],$_POST["Start"],$_POST["End"])){
                    die($db->searchProjects($_POST["Search"],$_POST["ProjectSort"],$_POST["Start"],$_POST["End"]));
                }
                break;
            case 'checkIfPublished':
                if (isset($_POST["ProjectIDs"])){
                    die($db->checkIfPublished($_POST["ProjectIDs"]));
                }
                break;
            case 'likeProject':
                if (isset($_POST["ProjectID"],$_POST["Like"])){
                    die($db->likeProject($_POST["ProjectID"],$UserID,$_POST["Like"]));
                }
                break;
            case 'reportProject':
                if (isset($_POST["ProjectID"],$_POST["Description"])){
                    die($db->reportProject($_POST["ProjectID"],$UserID,$_POST["Description"]));
                }
                break;
            case 'convertData':
                if (isset($_POST["From"],$_POST["To"],$_POST["Data"])){
                    die($db->convertData($_POST["From"],$_POST["To"],$_POST["Data"]));
                }
                break;
            default:
                die($db->unsuccessfulResult(ERROR_INVALID_ACTION));
                break;
        }
        die($db->unsuccessfulResult(ERROR_INVALID_PARAMETERS));
    } else {
        die($db->unsuccessfulResult("ERROR_ACCESS_DENIED3"));
    }
} else {
    die($db->unsuccessfulResult("ERROR_ACCESS_DENIED2"));
}
die($db->unsuccessfulResult("ERROR_ACCESS_DENIED1"));
?>