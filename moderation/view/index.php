<?php
require_once '../../libs/DB_Functions.php';
require_once '../../libs/User_Functions.php';
$db = new DB_Functions();
$users = new User_Functions();
if (isset($_COOKIE["session"])){
    $id = $users->checkJWTToken($_COOKIE["session"]);
    if ($id==false){
        header("location: http://127.0.0.1/planet-server/moderation/");
        die();
    } else {
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
            const USERNAME = $users->getUsername($id);
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
        <nav class="nav-extended light-green lighten-1" role="navigation">
            <div class="nav-wrapper container">
                <a id="logo-container" href="#" class="brand-logo"><i class="material-icons" id="planeticon">public</i>Planet Moderation Portal</a>
            </div>
            <div class="nav-content" id="searchcontainer" style="display: none;">
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
                    <a class="modal-action waves-effect waves-green btn-flat" id="projectviewer-download-file">Download as File</a>
                    <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Open in Music Blocks</a>
                </div>
            </div>
        </div>
    </body>
</html>
<?php
    }
} else {
    header("location: http://127.0.0.1/planet-server/moderation/");
    die();
}