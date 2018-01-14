<?php

class DB_Functions {
 
    private $db;
    private $link;
 
    function __construct() {
        require_once 'DB_Connect.php';
        require_once 'strings.php';
        $this->db = new DB_Connect();
        $this->link = $this->db->connect();
    }

    function __destruct() {
         
    }
    public $falseValue = '{"success": false}';
    public $trueValue = '{"success": true}';
    public $png_b64 = 'data:image/png;base64,';

    //'Front-End' functions
    public function addProject($ProjectJSON, $UserID){
        error_log("a");
        $ProjectObj = json_decode($ProjectJSON,true);
        if ($ProjectObj==NULL){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $ProjectID = $ProjectObj["ProjectID"];
        $ProjectID = intval($ProjectID);
        if (!is_int($ProjectID)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $ProjectName = $ProjectObj["ProjectName"];
        if (!$this->validateStringRange($ProjectName,1,50)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $ProjectDescription = $ProjectObj["ProjectDescription"];
        if (!$this->validateStringRange($ProjectDescription,1,1000)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $ProjectSearchKeywords = $ProjectObj["ProjectSearchKeywords"];
        //TODO: Should we validate on *long* fields like this, ProjectData and ProjectImage?
        if (!is_string($ProjectSearchKeywords)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        error_log("b");
        $ProjectData = $ProjectObj["ProjectData"];
        if (!$this->validateStringNonNull($ProjectData)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        if(empty(htmlspecialchars(base64_decode($ProjectData, true)))) {
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $ProjectImage = $ProjectObj["ProjectImage"];
        if ($ProjectImage!=""){
            if (!$this->validateStringNonNull($ProjectImage)){
                return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
            }
            $ProjectImageParts = explode(",", $ProjectImage);
            $ProjectImage = $ProjectImageParts[count($ProjectImageParts)-1];
            error_log($ProjectImage);
            if(base64_decode($ProjectImage, true)==false) {
                return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
            }
        }
        error_log("c");
        $ProjectIsMusicBlocks = $ProjectObj["ProjectIsMusicBlocks"];
        if (!($ProjectIsMusicBlocks==0||$ProjectIsMusicBlocks==1)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $ProjectCreatorName = $ProjectObj["ProjectCreatorName"];
        if (!$this->validateStringRange($ProjectCreatorName,1,50)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $ProjectTags = $ProjectObj["ProjectTags"];
        if (!$this->validateArray($ProjectTags,5)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        if ($this->checkProjectExists($ProjectID)){
            $this->updateProject($ProjectID, $UserID, $ProjectName, $ProjectDescription, $ProjectSearchKeywords, $ProjectData, $ProjectImage, $ProjectIsMusicBlocks, $ProjectCreatorName);
            $this->removeTagsFromProject($ProjectID);
            $this->addTagsToProject($ProjectID, $ProjectTags);
        } else {
            $this->addProjectToDB($ProjectID, $UserID, $ProjectName, $ProjectDescription, $ProjectSearchKeywords, $ProjectData, $ProjectImage, $ProjectIsMusicBlocks, $ProjectCreatorName);
            $this->addTagsToProject($ProjectID, $ProjectTags);
        }
        return $this->trueValue;
        //TODO: Check if upload actually successful
    }

    public function downloadProjectList($UserID,$ProjectTags,$ProjectSort,$Start,$End){
        //$Start inclusive, $End exclusive
        $Start = intval($Start);
        $End = intval($End);
        if (!is_int($Start)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        if (!is_int($End)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $Offset = $Start;
        $Limit = $End-$Start;

        $sorttype = "";
        switch ($ProjectSort) {
            case 'RECENT':
                $sorttype = "ProjectCreatedDate DESC";
                break;
            case 'LIKED':
                $sorttype = "ProjectLikes DESC";
                break;
            case 'DOWNLOADED':
                $sorttype = "ProjectDownloads DESC";
                break;
            case 'ALPHABETICAL':
                $sorttype = "ProjectName ASC";
                break;
            default:
                return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }

        switch ($ProjectTags) {
            case 'ALL_PROJECTS':
                //select all projects
                $query = "SELECT * FROM `Projects` ORDER BY ".$sorttype." LIMIT ".strval($Limit)." OFFSET ".strval($Offset).";";
                break;
            case 'USER_PROJECTS':
                //select projects by UserID
                //NOTE: UserID has been run through intval, so there shouldn't be any SQL injection risk
                $query = "SELECT * FROM `Projects` WHERE `UserID` = '".strval($UserID)."' ORDER BY ".$sorttype." LIMIT ".strval($Limit)." OFFSET ".strval($Offset).";";
                break;
            default:
                //select projects by tags
                $TagsArr = json_decode($ProjectTags,true);
                if (!is_array($TagsArr)){
                    return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
                }
                $tagslist = "(";
                foreach ($TagsArr as $tag) {
                    $tag = intval($tag);
                    if (!is_int($tag)){
                        return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
                    } else {
                        $tagslist = $tagslist.strval($tag).", ";
                    }
                }
                $tagslist = substr($tagslist, 0, -2).")";
                $query = "SELECT Projects.* FROM TagsToProjects INNER JOIN Projects ON Projects.ProjectID = TagsToProjects.ProjectID WHERE TagsToProjects.TagID IN ".$tagslist." GROUP BY TagsToProjects.ProjectID HAVING COUNT(TagsToProjects.TagID) = ".strval(count($TagsArr))." ORDER BY ".$sorttype." LIMIT ".strval($Limit)." OFFSET ".strval($Offset).";";
                break;
        }

        $result = mysqli_query($this->link, $query);
        if ($result){
            if (mysqli_num_rows($result)==0){
                $arr = array();
                return $this->successfulResult($arr);
            } else {
                $arr = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    array_push($arr,[$row["ProjectID"],$row["ProjectLastUpdated"]]);
                }
                return $this->successfulResult($arr);
            }
        } else {
            return $this->unsuccessfulResult(ERROR_INTERNAL_DATABASE);
        }
    }

    public function getProjectDetails($ProjectID){
        $ProjectID = intval($ProjectID);
        if (!is_int($ProjectID)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $stmt = mysqli_prepare($this->link, "SELECT * FROM `Projects` WHERE `ProjectID` = ?;");
        mysqli_stmt_bind_param($stmt, 'i', $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result){
            if (mysqli_num_rows($result)==0){
                return $this->unsuccessfulResult(ERROR_PROJECT_NOT_FOUND);
            } else {
                $row = mysqli_fetch_assoc($result);
                $ProjectObj = array();
                $ProjectObj["UserID"]=$row["UserID"];
                $ProjectObj["ProjectName"]=$row["ProjectName"];
                $ProjectObj["ProjectDescription"]=$row["ProjectDescription"];
                if ($row["ProjectImage"]!=""){
                    $ProjectObj["ProjectImage"]=$this->png_b64.$row["ProjectImage"];
                } else {
                    $ProjectObj["ProjectImage"]="";
                }
                $ProjectObj["ProjectIsMusicBlocks"]=intval($row["ProjectIsMusicBlocks"]);
                $ProjectObj["ProjectCreatorName"]=$row["ProjectCreatorName"];
                $ProjectObj["ProjectDownloads"]=intval($row["ProjectDownloads"]);
                $ProjectObj["ProjectLikes"]=intval($row["ProjectLikes"]);
                $ProjectObj["ProjectCreatorName"]=$row["ProjectCreatorName"];
                $ProjectObj["ProjectTags"]=$this->getProjectTags($ProjectID);
                $ProjectObj["ProjectLastUpdated"]=$row["ProjectLastUpdated"];
                $ProjectObj["ProjectCreatedDate"]=$row["ProjectCreatedDate"];
                return $this->successfulResult($ProjectObj, true);
            }
        }
    }

    public function downloadProject($ProjectID){
        $ProjectID = intval($ProjectID);
        if (!is_int($ProjectID)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $stmt = mysqli_prepare($this->link, "SELECT * FROM `Projects` WHERE `ProjectID` = ?;");
        mysqli_stmt_bind_param($stmt, 'i', $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result){
            if (mysqli_num_rows($result)==0){
                return $this->unsuccessfulResult(ERROR_PROJECT_NOT_FOUND);
            } else {
                $row = mysqli_fetch_assoc($result);
                $this->incrementDownloads($row["ProjectID"]);
                return $this->successfulResult($row["ProjectData"]);
            }
        }
    }

    public function getTagManifest(){
        $query = "SELECT * FROM `Tags`;";
        $result = mysqli_query($this->link, $query);
        if ($result){
            $arr = array();
            if (mysqli_num_rows($result)>0){
                while ($row = mysqli_fetch_assoc($result)) {
                    $obj = array();
                    $obj["TagName"] = $row["TagName"];
                    $obj["IsTagUserAddable"] = $row["IsTagUserAddable"];
                    $obj["IsDisplayTag"] = $row["IsDisplayTag"];
                    $arr[$row["TagID"]] = $obj;
                }
            }
            return $this->successfulResult($arr, true);
        } else {
            return $this->unsuccessfulResult(ERROR_INTERNAL_DATABASE);
        } 
    }

    public function searchProjects($search, $ProjectSort, $Start, $End){
        $Start = intval($Start);
        $End = intval($End);
        if ($Start>=$End){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }

        $sorttype = "";
        switch ($ProjectSort) {
            case 'RECENT':
                $sorttype = "ProjectLastUpdated DESC";
                break;
            case 'LIKED':
                $sorttype = "ProjectLikes DESC";
                break;
            case 'DOWNLOADED':
                $sorttype = "ProjectDownloads DESC";
                break;
            case 'ALPHABETICAL':
                $sorttype = "ProjectName ASC";
                break;
            default:
                return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }

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
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        if (!is_int($End)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $Offset = $Start;
        $Limit = $End-$Start;
        $query = $str." ORDER BY ".$sorttype." LIMIT ".strval($Limit)." OFFSET ".strval($Offset).";";
        $result = mysqli_query($this->link, $query);
        if ($result){
            if (mysqli_num_rows($result)==0){
                $arr = array();
                return $this->successfulResult($arr);
            } else {
                $arr = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    array_push($arr,[$row["ProjectID"],$row["ProjectLastUpdated"]]);
                }
                return $this->successfulResult($arr);
            }
        } else {
            return $this->unsuccessfulResult(ERROR_INTERNAL_DATABASE);
        }
    }

    public function checkIfPublished($ProjectIDs){
        $IDsArr = json_decode($ProjectIDs,true);
        if (!is_array($IDsArr)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        $idslist = "(";
        foreach ($IDsArr as $id) {
            $id = intval($id);
            if (!is_int($id)){
                return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
            } else {
                $idslist = $idslist.strval($id).", ";
            }
        }
        $idslist = substr($idslist, 0, -2).")";
        $query = "SELECT * FROM `Projects` WHERE `ProjectID` IN ".$idslist.";";
        $result = mysqli_query($this->link, $query);
        if ($result){
            if (mysqli_num_rows($result)==0){
                $arr = new stdClass();
                return $this->successfulResult($arr);
            } else {
                $arr = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $arr[$row["ProjectID"]]=true;
                }
                return $this->successfulResult($arr);
            }
        } else {
            return $this->unsuccessfulResult(ERROR_INTERNAL_DATABASE);
        }
    }

    public function likeProject($ProjectID, $UserID, $like){
        $ProjectID = intval($ProjectID);
        if (!is_int($ProjectID)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        if (!is_int($UserID)){
            return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        if ($like=="true"){
            $like=true;
        } else {
            $like=false;
        }
        $stmt = mysqli_prepare($this->link, "SELECT * FROM `LikesToProjects` WHERE `ProjectID` = ? AND `UserID` = ?;");
        mysqli_stmt_bind_param($stmt, 'ii', $ProjectID, $UserID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result){
            if (mysqli_num_rows($result)==0){
                if ($like){
                    $stmt = mysqli_prepare($this->link, "INSERT INTO `LikesToProjects` (`ProjectID`, `UserID`) VALUES (?, ?);");
                    mysqli_stmt_bind_param($stmt, 'ii', $ProjectID, $UserID);
                    // execute prepared statement
                    mysqli_stmt_execute($stmt);
                    $this->incrementLikes($ProjectID,true);
                    return $this->trueValue;
                } else {
                    return $this->unsuccessfulResult(ERROR_ACTION_NOT_PERMITTED);
                }
            } else {
                if (!$like){
                    $stmt = mysqli_prepare($this->link, "DELETE FROM `LikesToProjects` WHERE `ProjectID` = ? AND `UserID` = ?;");
                    mysqli_stmt_bind_param($stmt, 'ii', $ProjectID, $UserID);
                    // execute prepared statement
                    mysqli_stmt_execute($stmt);
                    $this->incrementLikes($ProjectID,false);
                    return $this->trueValue;
                } else {
                    return $this->unsuccessfulResult(ERROR_ACTION_NOT_PERMITTED);
                }
            }
        }
        return $this->unsuccessfulResult(ERROR_INTERNAL_DATABASE);
    }

    public function convertData($From, $To, $Data){
        $result = null;
        $contenttype = "";
        switch ($From) {
            case 'ly':
                switch ($To) {
                    case 'pdf':
                        $result = $this->convertLyPDF($Data);
                        $contenttype = "application/pdf";
                        break;
                    default:
                        return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
                }
                break;
            default:
                return $this->unsuccessfulResult(ERROR_INVALID_PARAMETERS);
        }
        if ($result==null){
            return $this->unsuccessfulResult(ERROR_CONVERSION_FAILURE);
        }
        $obj = array();
        $obj["contenttype"]=$contenttype;
        $obj["blob"]=$result;
        return $this->successfulResult($obj);
    }

    //Conversion functions
    public function convertLyPDF($Data){
        $Data = base64_decode($Data, true);
        $ly = "sudo -u lilypond /Applications/LilyPond.app/Contents/Resources/bin/lilypond";
        $time = strval(time());
        $filename = "./convert/ly2pdf/lilypond-".$time.".ly";
        $output = "./convert/ly2pdf/lilypond-".$time;
        $pdfoutput = "./convert/ly2pdf/lilypond-".$time.".pdf";
        file_put_contents($filename,$Data);
        $out = null;
        $ret = null;
        exec($ly." -o ".$output." ".$filename, $out, $ret);
        if (!$ret){
            $pdfdata = file_get_contents($pdfoutput);
            $b64data = base64_encode($pdfdata);
            unlink($filename);
            unlink($pdfoutput);
            return $b64data;
        } else {
            unlink($filename);
            unlink($pdfoutput);
            return null;
        }
    }

    //Result functions
    public function successfulResult($data, $htmlsafe=false){
        $a = array();
        $a["success"]=true;
        $a["data"]=$data;
        if ($htmlsafe){
            return json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        } else {
            return json_encode($a);
        }
    }

    public function unsuccessfulResult($error){
        $a = array();
        $a["success"]=false;
        $a["error"]=$error;
        return json_encode($a);
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
                while ($row = mysqli_fetch_assoc($result)) {
                    array_push($arr,intval($row["TagID"]));
                }
            }
        }
        return $arr;
    }

    public function checkProjectExists($ProjectID){
        $stmt = mysqli_prepare($this->link, "SELECT * FROM `Projects` WHERE `ProjectID` = ?");
        mysqli_stmt_bind_param($stmt, 'i', $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $arr = array();
        if ($result){
            if (mysqli_num_rows($result)>0){
                return true;
            }
        }
        return $false;
    }

    //Database-adding functions
    public function addProjectToDB($ProjectID, $UserID, $ProjectName, $ProjectDescription, $ProjectSearchKeywords, $ProjectData, $ProjectImage, $ProjectIsMusicBlocks, $ProjectCreatorName){
        $stmt = mysqli_prepare($this->link, "INSERT INTO `Projects` (`ProjectID`, `UserID`, `ProjectName`, `ProjectDescription`, `ProjectSearchKeywords`, `ProjectData`, `ProjectImage`, `ProjectIsMusicBlocks`, `ProjectCreatorName`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);");
        mysqli_stmt_bind_param($stmt, 'iisssssis', $ProjectID, $UserID, $ProjectName, $ProjectDescription, $ProjectSearchKeywords, $ProjectData, $ProjectImage, $ProjectIsMusicBlocks, $ProjectCreatorName);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
    }

    public function updateProject($ProjectID, $UserID, $ProjectName, $ProjectDescription, $ProjectSearchKeywords, $ProjectData, $ProjectImage, $ProjectIsMusicBlocks, $ProjectCreatorName){
        $stmt = mysqli_prepare($this->link, "UPDATE `Projects` SET `UserID`=?, `ProjectName`=?, `ProjectDescription`=?, `ProjectSearchKeywords`=?, `ProjectData`=?, `ProjectImage`=?, `ProjectIsMusicBlocks`=?, `ProjectCreatorName`=? WHERE `ProjectID` = ?;");
        mysqli_stmt_bind_param($stmt, 'isssssisi', $UserID, $ProjectName, $ProjectDescription, $ProjectSearchKeywords, $ProjectData, $ProjectImage, $ProjectIsMusicBlocks, $ProjectCreatorName, $ProjectID);
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

    public function removeTagsFromProject($ProjectID){
        $stmt = mysqli_prepare($this->link, "DELETE FROM `TagsToProjects` WHERE `ProjectID`=?;");
        mysqli_stmt_bind_param($stmt, 'i', $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
    }

    //Like/Download Increment/Decrement functions
    public function incrementDownloads($ProjectID){
        $stmt = mysqli_prepare($this->link, "UPDATE `Projects` SET `ProjectDownloads` = `ProjectDownloads` + 1 WHERE `ProjectID` = ?;");
        mysqli_stmt_bind_param($stmt, 'i', $ProjectID);
        // execute prepared statement
        mysqli_stmt_execute($stmt);
    }

    public function incrementLikes($ProjectID,$increment){
        if ($increment){
            $stmt = mysqli_prepare($this->link, "UPDATE `Projects` SET `ProjectLikes` = `ProjectLikes` + 1 WHERE `ProjectID` = ?;");
        } else {
            $stmt = mysqli_prepare($this->link, "UPDATE `Projects` SET `ProjectLikes` = `ProjectLikes` - 1 WHERE `ProjectID` = ?;");
        }
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
