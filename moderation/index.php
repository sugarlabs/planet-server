<?php
require_once '../libs/DB_Functions.php';
require_once '../libs/User_Functions.php';
$db = new DB_Functions();
$users = new User_Functions();
if (isset($_COOKIE["session"])){
    $id = $users->checkJWTToken($_COOKIE["session"]);
    if ($id!=false){
        header("location: http://127.0.0.1/planet-server/moderation/view/");
        die();
    }
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
        <title>MB Planet Moderation Portal</title>
    </head>

    <!--Inspired by http://demo.geekslabs.com/materialize-v1.0/user-login.html-->
    <body class="light-green">
        <div class="row">
            <div class="col s12 card-panel z-depth-4">
                <form id="login-form" onSubmit="return handleForm()">
                    <div class="row">
                        <div class="input-field col s12 center">
                            <i class="light-green-text material-icons planet-icon">public</i>
                            <p class="center login-form-text title-text">PLANET MODERATION PORTAL</p>
                            <div class="progress" id="login-progress" style="visibility: hidden;">
                                <div class="indeterminate"></div>
                            </div>
                            <div class="error-message" id="login-error" style="visibility: hidden;"></div>
                        </div>
                    </div>
                    <div class="row no-bottom">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">account_circle</i>
                            <input id="login-username" type="text" class="validate" required>
                            <label for="login-username">Username</label>
                        </div>
                    </div>
                    <div class="row no-bottom">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">lock</i>
                            <input id="login-password" type="password" class="validate" required>
                            <label for="login-password">Password</label>
                        </div>
                    </div>
                    <div class="row" id="checkboxrow">
                        <div class="input-field col s12 no-top">
                            <input type="checkbox" id="login-ssi" />
                            <label for="login-ssi">Remember me</label>
                        </div>
                    </div>
                    <div class="row">
                        <button class="btn waves-effect waves-light centre-x loginbtn" type="submit" name="action" id="login-submit">Login<i class="material-icons right">send</i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!--Import jQuery before materialize.js-->
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="libs/materialize.min.js"></script>
        <script type="text/javascript" src="libs/jwt-decode.js"></script>
        <script type="text/javascript" src="js/login.js"></script>
    </body>
</html>