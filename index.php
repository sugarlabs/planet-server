<?php
header('Content-Type: application/json');
require_once 'DB_Functions.php';
require_once 'config.php';
$db = new DB_Functions();

if (isset($_POST["api-key"], $_COOKIE["UserID"], $_POST["action"])){
    if ($_POST["api-key"]==API_KEY){
        $UserID = intval($_COOKIE["UserID"]);
        //TODO: Are we checking if this is non-null? At the moment if $_COOKIE["UserID"] is not int, $UserID will be null
        switch ($_POST["action"]) {
            case 'addProject':
                if (isset($_POST["ProjectJSON"])){
                    die($db->addProject($_POST["ProjectJSON"], $UserID));
                }
                break;
            case 'downloadProjectList':
                if (isset($_POST["ProjectTags"],$_POST["ProjectSort"],$_POST["Start"],$_POST["End"])){
                    die($db->downloadProjectList($_POST["ProjectTags"],$_POST["ProjectSort"],$_POST["Start"],$_POST["End"]));
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
                if (isset($_POST["Search"],$_POST["Start"],$_POST["End"])){
                    die($db->searchProjects($_POST["Search"],$_POST["Start"],$_POST["End"]));
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
            default:
                die($db->falseValue);
                break;
        }
    } else {
        die($db->falseValue);
    }
} else {
    die($db->falseValue);
}
die($db->falseValue);
?>