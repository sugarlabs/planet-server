<?php
require_once 'DB_Functions.php';
$db = new DB_Functions();
$UserID = 1234567890;
$JSON = '{"ProjectID": 1245234,"ProjectName": "My Project Name","ProjectDescription": "Lorem ipsum dolor sit amet","ProjectSearchKeywords": "tone vibrato timbre","ProjectData": "[some_tb_data_here]","ProjectImage": "data:image/png;base64,blah","ProjectIsMusicBlocks": 1,"ProjectCreatorName": "anonymous","ProjectTags": [124,435,234,253,435]}';
$db->addProject($JSON,$UserID);
?>