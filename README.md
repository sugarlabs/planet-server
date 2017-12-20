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

Project JSON:
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