//This file should be removed upon integration with MB.

var p;

function _(text){
	return text;
};

function deleteCookie(name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/';
}

$(document).ready(function(){
	p = new Planet(true);
	p.init();
	document.getElementById("logout").addEventListener('click', function (evt) {
		deleteCookie("session");
		window.location.href = "https://musicblocks.sugarlabs.org/planet-server/moderation/";
	});
});