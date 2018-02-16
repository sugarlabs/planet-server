function throwError(error){
    document.getElementById("login-error").style.visibility = "visible";
    document.getElementById("login-error").textContent = error;
}

function setCookie(name,token,exptime){
    var date = new Date();
    date.setTime(exptime*1000);
    document.cookie = name+"="+token+"; expires="+date.toGMTString()+"; path=/";
}

function afterLogin(jwt){
    var decoded = jwt_decode(jwt);
    setCookie("session",jwt,parseInt(decoded.exp));
    window.location.href = "./view";
}

function processFinish(data){
    data = JSON.parse(data);
    if (data.success){
        afterLogin(data.data);
    } else {
        throwError(data.error);
    }
}

function handleForm(){
    var username = encodeURIComponent(document.getElementById("login-username").value);
    var password = encodeURIComponent(document.getElementById("login-password").value);
    var ssi = document.getElementById("login-ssi").checked;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState === XMLHttpRequest.DONE) {
            if (xmlhttp.status === 200) {
                processFinish(xmlhttp.responseText);
            } else {
                throwError("Connection Error - Try Again");
            }
            document.getElementById("login-progress").style.visibility = 'hidden';
        }
    };
    xmlhttp.open("POST", "users.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("action=login&username="+username+"&password="+password+"&ssi="+ssi.toString());
    document.getElementById("login-progress").style.visibility = 'visible';
    return false;
}