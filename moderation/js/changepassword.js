function throwError(message) {
    var err = document.getElementById("cp-error");
    err.textContent = message;
    err.style.visibility = "visible";
    document.getElementById("cp-success").style.visibility = "hidden";
}

function showSuccess(message) {
    var success = document.getElementById("cp-success");
    success.textContent = message;
    success.style.visibility = "visible";
    document.getElementById("cp-error").style.visibility = "hidden";
}

function processFinish(data) {
    data = JSON.parse(data);
    document.getElementById("cp-progress").style.visibility = "hidden";
    document.getElementById("cp-submit").disabled = false;
    if (data.success) {
        showSuccess("Password changed successfully.");
        document.getElementById("changepassword-form").style.display = "none";
        document.getElementById("cp-success").style.visibility = "visible";
        document.getElementById("cp-back-btn").style.display = "inline-block";
    } else {
        throwError(data.error);
    }
}

function handleForm() {
    var currentPassword = document.getElementById("current-password").value;
    var newPassword = document.getElementById("new-password").value;
    var confirmPassword = document.getElementById("confirm-password").value;

    if (newPassword !== confirmPassword) {
        throwError("New and password and confirmation password do not match.");
        return false;
    }

    if (newPassword.length === 0 || currentPassword.length === 0) {
        throwError("All fields are required.");
        return false;
    }

    document.getElementById("cp-error").style.visibility = "hidden";
    document.getElementById("cp-success").style.visibility = "hidden";
    document.getElementById("cp-progress").style.visibility = "visible";
    document.getElementById("cp-submit").disabled = true;

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState === XMLHttpRequest.DONE) {
            if (xmlhttp.status === 200) {
                processFinish(xmlhttp.responseText);
            } else {
                document.getElementById("cp-progress").style.visibility = "hidden";
                document.getElementById("cp-submit").disabled = false;
                throwError("Connection Error - Try Again");
            }
        }
    };
    xmlhttp.open("POST", "../users.php", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(
        "action=changePassword" +
        "&currentPassword=" + encodeURIComponent(currentPassword) +
        "&newPassword=" + encodeURIComponent(newPassword)
    );
    return false;
}