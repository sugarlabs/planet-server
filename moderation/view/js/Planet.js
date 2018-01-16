// Copyright (c) 2017 Euan Ong
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the The GNU Affero General Public
// License as published by the Free Software Foundation; either
// version 3 of the License, or (at your option) any later version.
//
// You should have received a copy of the GNU Affero General Public
// License along with this library; if not, write to the Free Software
// Foundation, 51 Franklin Street, Suite 500 Boston, MA 02110-1335 USA

function Planet(isMusicBlocks){
	this.LocalPlanet = null;
	this.GlobalPlanet = null;
	this.ProjectStorage = null;
	this.ServerInterface = null;
	this.LocalStorage = window.localStorage;
	this.ConnectedToServer = null;
	this.TagsManifest = null;
	this.IsMusicBlocks = isMusicBlocks;
	this.UserIDCookie = "UserID";
	this.UserID = null;

	this.prepareUserID = function(){
		var id = getCookie(this.UserIDCookie);
		if (id==""){
			id = this.ProjectStorage.generateID();
			setCookie(this.UserIDCookie,id,3650);
		}
		this.UserID = id;
	};

	this.open = function(){
		this.LocalPlanet.updateProjects();
	};

	this.saveLocally = function(data, image){
		this.ProjectStorage.saveLocally(data, image);
	};

	this.init = function(callback){
		this.ProjectStorage = new ProjectStorage(this);
		this.ProjectStorage.init();
		this.prepareUserID();
		this.ServerInterface = new ServerInterface(this);
		this.ServerInterface.init();
		this.ServerInterface.getTagManifest(function(data){this.initPlanets(data,callback)}.bind(this));
	};

	this.initPlanets = function(tags, callback){
		if (!tags.success){
			this.ConnectedToServer = false;
		} else {
			this.ConnectedToServer = true;
			this.TagsManifest = tags.data;
		}
		this.LocalPlanet = new LocalPlanet(this);
		this.LocalPlanet.init();
		this.GlobalPlanet = new GlobalPlanet(this);
		this.GlobalPlanet.init();
		if (callback!=undefined){
			callback();
		}
	};
};