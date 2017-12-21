<?php

class DB_Functions {
 
    private $db;
    private $link;
 
    function __construct() {
        require_once 'DB_Connect.php';
        $this->db = new DB_Connect();
        $this->link = $this->db->connect();
    }

    function __destruct() {
         
    }

    //'Front-End' functions
    public function addProject($ProjectJSON, $UserID){
        $ProjectObj = json_decode($ProjectJSON,true);
        if ($ProjectObj==NULL){
            return false;
        }
        $ProjectID = $ProjectObj["ProjectID"];
        if (!is_int($ProjectID)){
            return false;
        }
        $ProjectName = $ProjectObj["ProjectName"];
        if (!$this->validateStringRange($ProjectName,1,50)){
            return false;
        }
        $ProjectDescription = $ProjectObj["ProjectDescription"];
        if (!$this->validateStringRange($ProjectDescription,1,1000)){
            return false;
        }
        $ProjectSearchKeywords = $ProjectObj["ProjectSearchKeywords"];
        //TODO: Should we validate on *long* fields like this, ProjectData and ProjectImage?
        if (!is_string($ProjectSearchKeywords)){
            return false;
        }
        $ProjectData = $ProjectObj["ProjectData"];
        if (!$this->validateStringNonNull($ProjectData)){
            return false;
        }
        $ProjectImage = $ProjectObj["ProjectImage"];
        if (!$this->validateStringNonNull($ProjectImage)){
            return false;
        }
        $ProjectIsMusicBlocks = $ProjectObj["ProjectIsMusicBlocks"];
        if (!($ProjectIsMusicBlocks==0||$ProjectIsMusicBlocks==1)){
            return false;
        }
        $ProjectCreatorName = $ProjectObj["ProjectCreatorName"];
        if (!$this->validateStringRange($ProjectCreatorName,1,50)){
            return false;
        }
        $ProjectTags = $ProjectObj["ProjectTags"];
        if (!$this->validateArray($ProjectTags,5)){
            return false;
        }
        $this->addProjectToDB($ProjectID, $UserID, $ProjectName, $ProjectDescription, $ProjectSearchKeywords, $ProjectData, $ProjectImage, $ProjectIsMusicBlocks, $ProjectCreatorName);
        $this->addTagsToProject($ProjectID, $ProjectTags);
        return true;
    }

    public function downloadProjectList($ProjectTags,$ProjectSort,$Start,$End){
        //$Start inclusive, $End exclusive
        $TagsArr = json_decode($ProjectTags,true);
        if (!is_array($TagsArr)){
            return false;
        }
        $tagslist = "(";
        foreach ($TagsArr as $tag) {
            if (!is_int($tag)){
                return false;
            } else {
                $tagslist = $tagslist.strval($tag).", ";
            }
        }
        $tagslist = substr($tagslist, 0, -2).")";
        $sorttype = "";
        switch ($ProjectSort) {
            case 'recent':
                $sorttype = "ProjectCreatedDate DESC";
                break;
            case 'liked':
                $sorttype = "ProjectLikes DESC";
                break;
            case 'downloaded':
                $sorttype = "ProjectDownloads DESC";
                break;
            case 'alphabetical':
                $sorttype = "ProjectName ASC";
                break;
            default:
                return false;
        }
        if (!is_int($Start)){
            return false;
        }
        if (!is_int($End)){
            return false;
        }
        $Offset = $Start;
        $Limit = $End-$Start;
        $query = "SELECT DISTINCT Projects.* FROM Projects INNER JOIN TagsToProjects ON TagsToProjects.TagID IN ".$tagslist." AND Projects.ProjectID=TagsToProjects.ProjectID ORDER BY ".$sorttype." LIMIT ".strval($Limit)." OFFSET ".strval($Offset).";";
        $result = mysqli_query($this->link, $query);
        if ($result){
            if (mysqli_num_rows($result)==0){
                return false;
            } else {
                $arr = array();
                while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    array_push($arr,intval($row["ProjectID"]));
                }
                return json_encode($arr);
            }
        } else {
            return false;
        } 
    }

    public function getProjectDetails($ProjectID){
        if (!is_int($ProjectID)){
            return false;
        }
        $stmt = mysqli_prepare($this->link, "SELECT * FROM `Projects` WHERE `ProjectID` = ?;");
        mysqli_stmt_bind_param($stmt, 'i', $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result){
            if (mysqli_num_rows($result)==0){
                return false;
            } else {
                $row = mysqli_fetch_array($result, MYSQL_ASSOC);
                $ProjectObj = array();
                $ProjectObj["UserID"]=intval($row["UserID"]);
                $ProjectObj["ProjectName"]=$row["ProjectName"];
                $ProjectObj["ProjectDescription"]=$row["ProjectDescription"];
                $ProjectObj["ProjectImage"]=$row["ProjectImage"];
                $ProjectObj["ProjectIsMusicBlocks"]=intval($row["ProjectIsMusicBlocks"]);
                $ProjectObj["ProjectCreatorName"]=$row["ProjectCreatorName"];
                $ProjectObj["ProjectDownloads"]=intval($row["ProjectDownloads"]);
                $ProjectObj["ProjectLikes"]=intval($row["ProjectLikes"]);
                $ProjectObj["ProjectCreatorName"]=$row["ProjectCreatorName"];
                $ProjectObj["ProjectTags"]=$this->getProjectTags($ProjectID);
                return json_encode($ProjectObj, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            }
        }
    }

    public function downloadProject($ProjectID){
        if (!is_int($ProjectID)){
            return false;
        }
        $stmt = mysqli_prepare($this->link, "SELECT * FROM `Projects` WHERE `ProjectID` = ?;");
        mysqli_stmt_bind_param($stmt, 'i', $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result){
            if (mysqli_num_rows($result)==0){
                return false;
            } else {
                $row = mysqli_fetch_array($result, MYSQL_ASSOC);
                $this->incrementDownloads($row["ProjectID"]);
                return $row["ProjectData"];
            }
        }
    }

    public function getTagManifest(){
        $query = "SELECT * FROM `Tags`;";
        $result = mysqli_query($this->link, $query);
        if ($result){
            $arr = array();
            if (mysqli_num_rows($result)>0){
                while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = array();
                    $obj["TagName"] = $row["TagName"];
                    $obj["IsTagUserAddable"] = $row["IsTagUserAddable"];
                    $obj["IsDisplayTag"] = $row["IsDisplayTag"];
                    $arr[$row["TagID"]] = $obj;
                }
            }
            return json_encode($arr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        } else {
            return false;
        } 
    }

    public function searchProjects($search, $Start, $End){
        $searcharr = explode(" ", $search);
        $prefix = "(CONCAT(`ProjectName`,' ',`ProjectDescription`,' ',`ProjectSearchKeywords`) LIKE ('%";
        $suffix = "%'))";
        $connective = " OR ";
        $start = "SELECT * FROM `Projects` WHERE ";
        $str = $start;
        foreach ($searcharr as $key) {
            $k = mysqli_real_escape_string($this->link, $key);
            $str = $str.$prefix.$k.$suffix.$connective;
        }
        $str = substr($str, 0, -1*strlen($connective));
        if (!is_int($Start)){
            return false;
        }
        if (!is_int($End)){
            return false;
        }
        $Offset = $Start;
        $Limit = $End-$Start;
        $query = $str." LIMIT ".strval($Limit)." OFFSET ".strval($Offset).";";
        $result = mysqli_query($this->link, $query);
        if ($result){
            if (mysqli_num_rows($result)==0){
                return false;
            } else {
                $arr = array();
                while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    array_push($arr,intval($row["ProjectID"]));
                }
                return json_encode($arr);
            }
        } else {
            return false;
        }
    }

    //Database-searching functions
    public function getProjectTags($ProjectID){
        $stmt = mysqli_prepare($this->link, "SELECT DISTINCT Tags.TagID FROM Tags INNER JOIN TagsToProjects ON TagsToProjects.ProjectID = ? AND Tags.TagID=TagsToProjects.TagID;");
        mysqli_stmt_bind_param($stmt, 'i', $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $arr = array();
        if ($result){
            if (mysqli_num_rows($result)>0){
                $arr = array();
                while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    array_push($arr,intval($row["TagID"]));
                }
            }
        }
        return $arr;
    }

    //Database-adding functions
    public function addProjectToDB($ProjectID, $UserID, $ProjectName, $ProjectDescription, $ProjectSearchKeywords, $ProjectData, $ProjectImage, $ProjectIsMusicBlocks, $ProjectCreatorName){
        $stmt = mysqli_prepare($this->link, "INSERT INTO `Projects` (`ProjectID`, `UserID`, `ProjectName`, `ProjectDescription`, `ProjectSearchKeywords`, `ProjectData`, `ProjectImage`, `ProjectIsMusicBlocks`, `ProjectCreatorName`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");
        mysqli_stmt_bind_param($stmt, 'iisssssis', $ProjectID, $UserID, $ProjectName, $ProjectDescription, $ProjectSearchKeywords, $ProjectData, $ProjectImage, $ProjectIsMusicBlocks, $ProjectCreatorName);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
    }

    public function addTagProjectPair($TagID, $ProjectID){
        $stmt = mysqli_prepare($this->link, "INSERT INTO `TagsToProjects` (`TagID`, `ProjectID`) VALUES (?, ?);");
        mysqli_stmt_bind_param($stmt, 'ii', $TagID, $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
    }

    public function addTagsToProject($ProjectID, $ProjectTags){
        foreach ($ProjectTags as $tag) {
            if ($this->canUserAddTag($tag)){
                $this->addTagProjectPair($tag, $ProjectID);
            }
        }
    }

    //Like/Download Increment/Decrement functions
    public function incrementDownloads($ProjectID){
        $stmt = mysqli_prepare($this->link, "UPDATE `Projects` SET `ProjectDownloads` = `ProjectDownloads` + 1 WHERE `ProjectID` = ?;");
        mysqli_stmt_bind_param($stmt, 'i', $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
    }

    //Validation/checking functions
    public function canUserAddTag($tag){
        $stmt = mysqli_prepare($this->link, "SELECT * FROM `Tags` WHERE `TagID` = ? AND `isTagUserAddable` = 0;");
        mysqli_stmt_bind_param($stmt, 'i', $tag);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result){
            if (mysqli_num_rows($result)==0){
                return true;
            }
        }
        return false;
    }

    public function validateStringRange($string,$start,$end){
        //inclusive
        if (is_string($string)){
            if (strlen($string)>=$start&&strlen($string)<=$end){
                return true;
            }
        }
        return false;
    }

    public function validateStringNonNull($string){
        //inclusive
        if (is_string($string)){
            if (strlen($string)>=0){
                return true;
            }
        }
        return false;
    }

    public function validateArray($array,$length){
        if (is_array($array)){
            if (count($array)<=$length){
                return true;
            }
        }
        return false;
    }
}
?>
