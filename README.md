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


## API Documentation:
HTTP POST requests to index.php (with cookie "UserID" set to user ID generated client-side)
Include `api-key` parameter for all requests.

### Add a New Project:

#### Query Parameters
`action`: `addProject`,
`ProjectJSON`: JSON data for project being uploaded (TB data in b64) e.g.
```JSON
{
	"ProjectID": 1245234,
	"ProjectName": "My Project Name",
	"ProjectDescription": "Lorem ipsum dolor sit amet",
	"ProjectSearchKeywords": "tone vibrato timbre",
	"ProjectData": "W3NvbWVfdGJfZGF0YV9oZXJlXQ==",
	"ProjectImage": "aW1hZ2UgaGVyZQ==",
	"ProjectIsMusicBlocks": 1,
	"ProjectCreatorName": "anonymous",
	"ProjectTags": [124,435,234,253,435]
}
```

#### Response
`{"success": true}` if project upload successful,
`{"success": false}` if project upload unsuccessful (i.e. validation error)

### Download Project List:

#### Query Parameters
`action`: `downloadProjectList`,
`ProjectTags`: array of tag IDs to search by e.g. `[1,2,5]`
`ProjectSort`: field to sort by - `recent`, `liked`, `downloaded`, `alphabetical`
`Start`: index of first project to be returned (inclusive) e.g. `0`
`End`: index of last project to be returned + 1 (exclusive) e.g. `25`

#### Response (array of IDs of projects matching parameters)
```JSON
{
	"success":true,
	"data":[1245234,3245234325]
}
```
if projects successfully found,
`{"success": false}` if no projects found or on validation errors

### Get Project Details:

#### Query Parameters
`action`: `getProjectDetails`,
`ProjectID`: ID of project to get details for e.g. `1245234`

#### Response (more information about the selected project)
```JSON
{
	"success":true,
	"data":{
		"UserID":1234567890,
		"ProjectName":"My Project Name",
		"ProjectDescription":"Lorem ipsum dolor sit amet",
		"ProjectImage":"aW1hZ2UgaGVyZQ==",
		"ProjectIsMusicBlocks":1,
		"ProjectCreatorName":"anonymous",
		"ProjectDownloads":2,
		"ProjectLikes":0,
		"ProjectTags":[1,4,2,6]
	}
}
```
if project successfully found,
`{"success": false}` otherwise

### Download Project:

#### Query Parameters
`action`: `downloadProject`,
`ProjectID`: ID of project to download e.g. `1245234`

#### Response (TB data in b64)
```JSON
{
	"success":true,
	"data":"W3NvbWVfdGJfZGF0YV9oZXJlXQ=="
}
```
if project data successfully found,
`{"success": false}` otherwise

### Get Tag Manifest:

#### Query Parameters
`action`: `getTagManifest`

#### Response (all tags with data, indexed by tag ID)
```JSON
{
	"success":true,
	"data":{
		"1":{
			"TagName":"Examples",
			"IsTagUserAddable":"0",
			"IsDisplayTag":"1"
		},
		"2":{
			"TagName":"Music",
			"IsTagUserAddable":"1",
			"IsDisplayTag":"1"
		},
		"3":{
			"TagName":"Art",
			"IsTagUserAddable":"1",
			"IsDisplayTag":"1"
		}
	}
}
```
if tags successfully found,
`{"success": false}` otherwise

### Search Projects:

#### Query Parameters
`action`: `searchProjects`
`Search`: search string e.g. `music timbre`
`Start`: index of first project to be returned (inclusive) e.g. `0`
`End`: index of last project to be returned + 1 (exclusive) e.g. `25`

#### Response (array of IDs of projects matching any of the search terms, most recent first)
```JSON
{
	"success":true,
	"data":[124523,3245234325]
}
```
if projects successfully found matching search keywords,
`{"success": false}` otherwise

### Check If Published:

#### Query Parameters
`action`: `checkIfPublished`
`ProjectIDs`: array of project IDs to check whether or not they are published e.g. `[124523, 5324646]`

#### Response (JSON object with keys of published project IDs; empty object if no project is published)
```JSON
{
	"success":true,
	"data":{
		"124523":true
	}
}
```
if query successfully completed,
`{"success": false}` otherwise

### Like Project:

#### Query Parameters
`action`: `likeProject`
`ProjectID`: ID of project to be (un)liked e.g. `124523`
`like`: `true` if user is liking project, `false` if user is unliking project

#### Response
`{"success": true}` if like/unlike successful
`{"success": false}` if like/unlike unsuccessful
(n.b. each user can only like a project once - once a user has liked a project, all further like requests will return false until they have unliked it)