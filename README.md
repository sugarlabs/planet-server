# planet-server
A server for the Turtle/Music Blocks Planet.

To use, create a config.php file with

```php
<?php
define("DB_HOST", "localhost");
define("DB_USER", "YOUR_MYSQL_USERNAME_HERE");
define("DB_PASSWORD", "YOUR_PASSWORD_HERE");
define("DB_DATABASE", "planet");
define("API_KEY", "YOUR_API_KEY_HERE");
?>
```

Upload Project JSON:
```JSON
{
	"ProjectID": 1245234,
	"ProjectName": "My Project Name",
	"ProjectDescription": "Lorem ipsum dolor sit amet",
	"ProjectSearchKeywords": "tone vibrato timbre",
	"ProjectData": "[some_tb_data_here]",
	"ProjectImage": "data:image/png;base64,<base64 image data here>",
	"ProjectIsMusicBlocks": 1,
	"ProjectCreatorName": "anonymous",
	"ProjectTags": [124,435,234,253,435]
}
```

Download Project Tag IDs JSON:
```JSON
[1,2,5,6,7]
```

Get Project Details JSON:
```JSON
{
	"UserID": 12345,
	"ProjectName": "My Project Name",
	"ProjectDescription": "Lorem ipsum dolor sit amet",
	"ProjectImage": "data:image/png;base64,<base64 image data here>",
	"ProjectIsMusicBlocks": 1,
	"ProjectCreatorName": "anonymous",
	"ProjectDownloads": 57,
	"ProjectLikes": 42,
	"ProjectCreatorName": "anonymous",
	"ProjectTags": [124,435,234,253,435]
}
```

Get Project Data:
```JSON
[some_tb_data_here]
```

Get Tag Manifest Data:
```JSON
{
	"1": {
		"TagName":"Examples",
		"IsUserAddable":0,
		"IsDisplayTag":1
	},
	"4": {
		"TagName":"Music",
		"IsUserAddable":1,
		"IsDisplayTag":1
	},
	"9": {
		"TagName":"Art",
		"IsUserAddable":1,
		"IsDisplayTag":0
	}
}
```
(indexed by tag ID)