//This file should be removed upon integration with MB.

var p;

function newProject(){
	p.ProjectStorage.initialiseNewProject();
};

function openProject(data,image){
	p.saveLocally(data,image);
	p.open();
};

function _(text){
	return text;
};

$(document).ready(function(){
	p = new Planet(true);
	p.init(function(){p.open();});
});