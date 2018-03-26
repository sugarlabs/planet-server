<?php
require_once '../../libs/DB_Functions.php';
require_once '../../libs/User_Functions.php';
$db = new DB_Functions();
$users = new User_Functions();
if (isset($_COOKIE["session"])){
    $id = $users->checkJWTToken($_COOKIE["session"]);
    if ($id==false){
        header("location: https://musicblocks.sugarlabs.org/planet-server/moderation/");
        die();
    } else {
        $project = "null";
        if (isset($_GET["id"])){
            $project = $_GET["id"];
        }
?>
<!DOCTYPE html>
<html>
    <head>
        <!--Import Google Icon Font-->
        <link type="text/css" href="fonts/material-icons.css" rel="stylesheet">
        <!--Import materialize.css-->
        <link type="text/css" rel="stylesheet" href="libs/materialize.min.css" media="screen,projection"/>
        <!--Import style.css-->
        <link type="text/css" rel="stylesheet" href="css/style.css"/>

        <!--Let browser know website is optimized for mobile-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <!--Import jQuery before materialize.js-->
        <script type="text/javascript" src="libs/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="libs/materialize.min.js"></script>

        <!--Constants-->
        <script type="text/javascript">
            const USERNAME = "<?php echo $users->getUsername($id); ?>";
            const PROJECT_TO_OPEN = <?php echo $project; ?>;
        </script>

        <script type="text/javascript" src="js/helper.js"></script>
        <script type="text/javascript" src="js/ProjectStorage.js"></script>
        <script type="text/javascript" src="js/ServerInterface.js"></script>

        <script type="text/javascript" src="js/LocalCard.js"></script>
        <script type="text/javascript" src="js/Publisher.js"></script>
        <script type="text/javascript" src="js/LocalPlanet.js"></script>

        <script type="text/javascript" src="js/GlobalTag.js"></script>
        <script type="text/javascript" src="js/GlobalCard.js"></script>
        <script type="text/javascript" src="js/ProjectViewer.js"></script>
        <script type="text/javascript" src="js/GlobalPlanet.js"></script>

        <script type="text/javascript" src="js/Planet.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
    </head>

    <body>
        <ul id="userdropdown" class="dropdown-content" style="transform: translateY(50%);">
            <li><a id="invitelink">Generate invite link</a></li>
            <li class="divider"></li>
            <li><a id="logout">Logout</a></li>
        </ul>
        <nav class="nav-extended light-green lighten-1" role="navigation">
            <div class="nav-wrapper container">
                <a id="logo-container" href="#" class="brand-logo"><i class="material-icons" id="planeticon">public</i>Planet Moderation Portal</a>
                <ul class="right">
                    <li><a class="dropdown-button" href="#!" data-activates="userdropdown"><?php echo $users->getUsername($id); ?><i class="material-icons right">arrow_drop_down</i></a></li>
                </ul>
            </div>
            <div class="nav-content" id="searchcontainer">
                <div class="container">
                    <div class="input-field search">
                        <i class="material-icons prefix" id="searchicon">search</i><input placeholder="Search for a project" id="global-search" type="text"><span><i class="material-icons" id="search-close">clear</i></span>
                    </div>
                </div>
            </div>
        </nav>
        <div id="global">
            <div class="section no-pad-bot">
                <div class="container">
                    <h3 class="header center light-green-text" id="globaltitle">Explore Projects</h3>
                </div>
            </div>
            <div id="globalcontents">
                <div class="section flexcontainer" id="tagscontainer">
                    <div class="container">
                        <div class="flexchips", id="primarychips">
                        </div>
                        <div class="flexchips", id="morechips">
                        </div>
                        <a class="waves-effect btn-flat centre-button" id="view-more-chips" onclick="toggleExpandable('morechips','flexchips');toggleText('view-more-chips','View More','View Less');">View More</a>
                    </div>
                </div>
                <div class="container">
                    <div class="divider"></div>
                    <div class="section">
                        <div class="row">
                            <div class="input-field col s3 offset-s9">
                                <select id="sort-select">
                                    <option value="RECENT" selected>Most recent</option>
                                    <option value="LIKED">Most liked</option>
                                    <option value="DOWNLOADED">Most downloaded</option>
                                    <option value="ALPHABETICAL">A-Z</option>
                                </select>
                                <label>Sort by</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col no-margin-left s12">
                                <div class="section projectscontainer" id="global-projects">
                                </div>
                                <a class="waves-effect btn-flat centre-button absolute" id="load-more-projects" style="display: none;">Load More Projects</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="preloader-wrapper small active centre-load" id="global-load" style="display: none;">
                                <div class="spinner-layer spinner-blue">
                                    <div class="circle-clipper left">
                                        <div class="circle"></div>
                                    </div><div class="gap-patch">
                                        <div class="circle"></div>
                                    </div><div class="circle-clipper right">
                                        <div class="circle"></div>
                                    </div>
                                </div>

                                <div class="spinner-layer spinner-red">
                                    <div class="circle-clipper left">
                                        <div class="circle"></div>
                                    </div><div class="gap-patch">
                                        <div class="circle"></div>
                                    </div><div class="circle-clipper right">
                                        <div class="circle"></div>
                                    </div>
                                </div>

                                <div class="spinner-layer spinner-yellow">
                                    <div class="circle-clipper left">
                                        <div class="circle"></div>
                                    </div><div class="gap-patch">
                                        <div class="circle"></div>
                                    </div><div class="circle-clipper right">
                                        <div class="circle"></div>
                                    </div>
                                </div>

                                <div class="spinner-layer spinner-green">
                                    <div class="circle-clipper left">
                                        <div class="circle"></div>
                                    </div><div class="gap-patch">
                                        <div class="circle"></div>
                                    </div><div class="circle-clipper right">
                                        <div class="circle"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="projectviewer" class="modal">
                <a class="modal-close close-button"><i class="material-icons">close</i></a>
                <div class="modal-content">
                    <h4 id="projectviewer-title"></h4>
                    <div class="col no-margin-left s12">
                        <div class="row no-margin-bottom">
                            <div class="col no-margin-left s6">
                                <div class="row no-margin-bottom">
                                    <p>
                                        <div class="subheading">Last Updated</div><div id="projectviewer-last-updated"></div>
                                    </p>
                                    <p>
                                        <div class="subheading">Creation Date</div><div id="projectviewer-date"></div>
                                    </p>
                                    <p>
                                        <div class="subheading">Number of Downloads:</div><div id="projectviewer-downloads"></div>
                                    </p>
                                    <p>
                                        <div class="subheading">Number of Likes:</div><div id="projectviewer-likes"></div>
                                    </p>
                                    <p>
                                        <div class="subheading">Tags:</div><div id="projectviewer-tags"></div>
                                    </p>
                                </div>
                            </div>
                            <div class="col no-margin-left s6">
                                <img class="col no-margin-left s12 project-image no-padding" id="projectviewer-image">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col no-margin-left s12">
                                <p>
                                    <div class="subheading">Description</div>
                                    <div id="projectviewer-description"></div>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a class="btn-flat left modal-action red-text waves-effect waves-red" id="projectviewer-delete">Delete Project</a>
                    <a class="btn-flat left modal-action green-text waves-effect waves-green" id="projectviewer-unreport" style="display: none;">Dismiss Reports</a>
                    <a class="modal-action waves-effect waves-green btn-flat" id="projectviewer-download-file">Download as File</a>
                    <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat" id="projectviewer-edit">Edit Project</a>
                </div>
            </div>
            <div id="publisher" class="modal">
                <div class="modal-content" id="publisher-content">
                    <a class="modal-close close-button"><i class="material-icons">close</i></a>
                    <h4 id="publisher-ptitle">Publish Project</h4>
                    <div class="progress" id="publisher-progress" style="visibility: hidden;">
                        <div class="indeterminate"></div>
                    </div>
                    <div class="error-message" id="publisher-error" style="display: none;"></div>
                    <form class="col no-margin-left s12" id="publisher-form">
                        <input type="hidden" id="publish-id" name="ProjectID">
                        <div class="row">
                            <div class="col no-margin-left s6">
                                <div class="row">
                                    <div class="input-field">
                                        <input id="publish-title" type="text" class="validate" data-length="50" required>
                                        <label for="publish-title" id="publish-title-label">Project title</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field">
                                        <div class="chips chips-autocomplete" id="tagsadd"></div>
                                        <label for="publish-tags">Tags (max 5)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col no-margin-left s6">
                                <img class="col no-margin-left s12 project-image" id="publish-image" src="images/mbgraphic.png">
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col no-margin-left s12">
                                <textarea id="publish-description" class="materialize-textarea validate" data-length="1000" required></textarea>
                                <label for="publish-description" id="publish-description-label">Description</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a class="modal-action waves-effect waves-green btn-flat" id="publisher-submit">Submit</a>
                    <a href="#!" class="modal-action modal-close waves-effect waves-red btn-flat">Cancel</a>
                </div>
            </div>
            <div id="deleter" class="modal">
                <div class="modal-content">
                    <a class="modal-close close-button"><i class="material-icons">close</i></a>
                    <h4>Delete "<span id="deleter-title"></span>"?</h4>
                    <p>Permanently delete project "<span id="deleter-name"></span>"?</p>
                </div>
                <div class="modal-footer">
                    <a href="#!" id="deleter-button" class="modal-action modal-close waves-effect waves-red btn-flat">Delete</a>
                    <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Cancel</a>
                </div>
            </div>
            <div id="unreporter" class="modal">
                <div class="modal-content">
                    <a class="modal-close close-button"><i class="material-icons">close</i></a>
                    <h4>Unflag "<span id="unreporter-title"></span>"?</h4>
                    <p>Dismiss reports for project "<span id="unreporter-name"></span>"?</p>
                </div>
                <div class="modal-footer">
                    <a href="#!" id="unreporter-button" class="modal-action modal-close waves-effect waves-red btn-flat">Unflag</a>
                    <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Cancel</a>
                </div>
            </div>
            <div id="invitelinkmodal" class="modal">
                <div class="modal-content">
                    <a class="modal-close close-button"><i class="material-icons">close</i></a>
                    <h4>Invite Link</h4>
                    <p>Copy the link below to invite other administrators. The link will expire after 24 hours.</p>
                    <input id="invitelinkbox" type="text">
                </div>
                <div class="modal-footer">
                    <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Close</a>
                </div>
            </div>
        </div>
    </body>
</html>
<?php
    }
} else {
    header("location: https://musicblocks.sugarlabs.org/planet-server/moderation/");
    die();
}