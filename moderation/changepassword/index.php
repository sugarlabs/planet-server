<?php
require_once '../../libs/User_Functions.php';
$users = new User_Functions();
if (!isset($_COOKIE["session"])){
    header("location: ../");
    die();
}
$id = $users->checkJWTToken($_COOKIE["session"]);
if ($id==false){
    header("location: ../");
    die();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <!--Import Google Icon Font-->
        <link type="text/css" href="../fonts/material-icons.css" rel="stylesheet">
        <!--Import materialize.css-->
        <link type="text/css" rel="stylesheet" href="../libs/materialize.min.css" media="screen,projection"/>
        <!--Import style.css-->
        <link type="text/css" rel="stylesheet" href="../css/style.css"/>

        <!--Let browser know website is optimized for mobile-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>MB Planet Moderator- Change Password</title>
    </head>

    <body class="light-green">
        <div class="row">
            <div class="col s12 card-panel z-depth-4">
                <form id="changepassword-form" onSubmit="return handleForm()">
                    <div class="row">
                        <div class="input-field col s12 center">
                            <i class="light-green-text material-icons planet-icon">public</i>
                            <p class="center login-form-text title-text">CHANGE PASSWORD</p>
                            <div class="progress" id="cp-progress" style="visibility: hidden;">
                                <div class="indeterminate"></div>
                            </div>
                            <div class="error-message" id="cp-error" style="visibility: hidden;"></div>
                        </div>
                    </div>
                    <div class="row no-bottom">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">lock_outline</i>
                            <input id="current-password" type="password" class="validate" required>
                            <label for="current-password">Current Password</label>
                        </div>
                    </div>
                    <div class="row no-bottom">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">lock</i>
                            <input id="new-password" type="password" class="validate" required>
                            <label for="new-password">New Password</label>
                        </div>
                    </div>
                    <div class="row no-bottom">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">lock</i>
                            <input id="confirm-password" type="password" class="validate" required>
                            <label for="confirm-password">Confirm New Password</label>
                        </div>
                    </div>
                    <div class="row" style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
                        <a class="btn waves-effect waves-light grey loginbtn" href="../view/">Cancel</a>
                        <button class="btn waves-effect waves-light loginbtn" type="submit" name="action" id="cp-submit">Save<i class="material-icons right">save</i>
                        </button>
                    </div>
                </form>
                <div class="success-message" id="cp-success" style=" font-weight: bold; visibility: hidden;"></div>
                <div class="row" style="display: none;" id="cp-back-btn">
                    <a class="btn waves-effect waves-light centre-x loginbtn" href="../view/">Go back to moderation portal<i class="material-icons right">arrow_forward</i></a>
                </div>
            </div>
        </div>

        <!--Import jQuery before materialize.js-->
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="../libs/materialize.min.js"></script>
        <script type="text/javascript" src="../js/changepassword.js"></script>
    </body>
</html>