<?php
require_once '../libs/User_Functions.php';
$user = new User_Functions();
if (!isset($_GET["token"])){
    die("Access Denied");
} else {
    $token = $_GET["token"];
    if (!$user->checkInvite($token)){
        die("Access Denied");
    } ?>
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
                            <p class="center no-top subtitle-text">Register</p>
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
                            <i class="material-icons prefix">email</i>
                            <input id="login-email" type="email" class="validate" required>
                            <label for="login-email">Email</label>
                        </div>
                    </div>
                    <div class="row no-bottom">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">lock</i>
                            <input id="login-password" type="password" class="validate" required>
                            <label for="login-password">Password</label>
                        </div>
                    </div>
                    <div class="row">
                        <button class="btn waves-effect waves-light centre-x loginbtn" type="submit" name="action" id="login-submit">Sign up<i class="material-icons right">send</i>
                        </button>
                    </div>
                    <!--TODO: Implement clientside validation-->
                </form>
            </div>
        </div>

        <!--Import jQuery before materialize.js-->
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="libs/materialize.min.js"></script>
        <script type="text/javascript" src="libs/jwt-decode.js"></script>
        <script type="text/javascript">const REGISTER_TOKEN = "<?php echo $token; ?>";</script>
        <script type="text/javascript" src="js/register.js"></script>
    </body>
</html>
<?php }?>