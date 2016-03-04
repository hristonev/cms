var builder = function(){
	//instance
	if(typeof(window.__builder) == "undefined"){
		window.__builder = new Array();
	}
	if(typeof(window.__userData) == "undefined"){
		window.__userData = new Array();
	}

	this.instance_key = window.__builder.length;
	window.__builder[this.instance_key] = this;

	this.loadModule = new Array(
		"ajax",
		"event",
		"xml",
		"domElement",
		"list",
		"navigation",
		"popUp",
		"cmsHead",
		"cmsNavigation",
		"cmsView",
		"cmsViewTab",
		"ckeditor/ckeditor"
	);
	this.loaded = 0;
	this.head = null;
	this.navigation = null;
	this.navResize = null;
	this.content = null;
	this.body = null;
	this.footer = null;

	this.loadJS = function(){
		var module = this.loadModule[0];
		this.loadModule.shift();
		$.ajax({
			method: "POST",
			url: "index.php",
			dataType: "script",
			data: {
				"group": "js",
				"className": module
			}
		}).done(function(xml) {
			eval(xml);
			$('body').data("builder").loaded++;
			if($('body').data("builder").loadModule.length > 0){
				$('body').data("builder").loadJS();
			}else{
				console.log('load js complete');
				$('body').data("builder").init();
			}

		});
	};

	this.init = function(){
		//load js files
		$('body').data("builder", this);
		if(this.loaded == 0){
			console.log('load framework');
			this.loadJS();
		}else{
			console.log('create base containers');
			this.body = document.getElementsByTagName('body')[0];

			this.head = new domElement('div');
			this.head.parent = this.body;
			this.head.setCssClass('head');
			this.head.elm.setAttribute('id', 'cmsHead');
			this.head.render();

			this.navigation = new domElement('div');
			this.navigation.parent = this.body;
			this.navigation.setCssClass('navigation');
			this.navigation.elm.setAttribute('id', 'cmsNavigation');
			this.navigation.render();

			this.navResize = new domElement('div');
			this.navResize.parent = this.body;
			this.navResize.setCssClass('navResize');
			this.navResize.elm.setAttribute('id', 'cmsNavResize');
			this.navResize.caller = this;
			this.navResize.setAttribute('eventCode', 'resize');
			this.navResize.render();
			this.navResize.setEvent('onmousedown', 'navSatrtResize');

			this.content = new domElement('div');
			this.content.parent = this.body;
			this.content.setCssClass('content');
			this.content.elm.setAttribute('id', 'cmsContent');
			this.content.render();


			this.footer = new domElement('div');
			this.footer.parent = this.body;
			this.footer.setCssClass('footer');
			this.footer.elm.setAttribute('id', 'cmsFooter');
			this.footer.render();

			console.log('create head elements');
			var head = new cmsHead(this.head);
			head.caller = this;
			head.render();

			console.log('create navigation elements');
			var nav = new cmsNavigation(this.navigation);
			nav.caller = this;
			nav.render();
		}
	};

	this.destruct = function(){
		console.log('logout');
		$.ajax({
			method: "POST",
			url: "index.php",
			data: {
				"group": "include",
				"className": "user",
				"methodName": "xLogout"
				}
		}).done(function(dataStr) {
			data = JSON.parse(dataStr);
			var body = document.getElementsByTagName('body')[0];
			this.logout = new domElement('div');
			this.logout.parent = body;
			this.logout.setCssClass('logoutMsg');
			this.logout.setNewText(data.msg);
			this.logout.render();
		});
		var fadeSpead = 400;
		$('#cmsHead').fadeOut(fadeSpead);
		$('#cmsNavigation').fadeOut(fadeSpead);
		$('#cmsNavResize').fadeOut(fadeSpead);
		$('#cmsContent').fadeOut(fadeSpead);
		$('#cmsFooter').fadeOut(fadeSpead,function(){
			$('#cmsHead').remove();
			$('#cmsNavigation').remove();
			$('#cmsNavResize').remove();
			$('#cmsContent').remove();
			$('#cmsFooter').remove();
			console.log('bye');
		});
	};

	this.handleOutsideEvent = function(obj, e){
		switch(obj.getAttribute('eventCode')){
		case 'resize':
			if (!this.hasOwnProperty('dragSatartValue')) {
				var dragSatartValue = window.getComputedStyle(document.body);
				if(typeof(window.__navigationResize) == "undefined"){
					window.__navigationResize = new Array();
					window.__navigationResize['dragStartWidth'] = dragSatartValue.getPropertyValue('--navWidth');
					window.__navigationResize['dragStartMouse'] = e.pageX;
					window.__navigationResize['dragMinWidth'] = dragSatartValue.getPropertyValue('--navMinWidth');
				}
				events.registerEventHandler(this.body, "onmouseup", function(e){
					events.removeEvents(document.getElementsByTagName('body')[0]);
				});
				events.registerEventHandler(this.body, "onmousemove", function(e){
					var obj = document.getElementsByTagName('body')[0];
					var width = parseInt(window.__navigationResize['dragStartWidth']) + parseInt(e.pageX) - parseInt(window.__navigationResize['dragStartMouse']);
					if(parseInt(width) < parseInt(window.__navigationResize['dragMinWidth'])){
						width = parseInt(window.__navigationResize['dragMinWidth']);
					}
					if(parseInt(width) > (parseInt(window.innerWidth) * 2 / 3)){
						width = (parseInt(window.innerWidth) * 2 / 3);
					}
					obj.style.setProperty('--navWidth', width + 'px', null);
				});
			}
			break;
		case 'logout':
			this.destruct();
			break;
		}
	};

};

builder.registerResize = function(obj, method){
	if(typeof(window.__builder['resize']) == "undefined"){
		window.__builder['resize'] = new Array();
	}
	var key = window.__builder['resize'].length;
	window.__builder['resize'][key] = new Array();
	window.__builder['resize'][key]['obj'] = obj;
	window.__builder['resize'][key]['method'] = method;
};

$(window).resize(function(){
	if(typeof(window.__builder['resize']) != "undefined"){
		for(var i = 0; i < window.__builder['resize'].length; i++){
			eval(window.__builder['resize'][i]['obj'] + "." + window.__builder['resize'][i]['method'] + "();");
		}
	}
});
/**
 * local storage
 */
var storage = {};
storage.get = function(code){
	return localStorage.getItem(code);
};

storage.set = function(code, value){
	console.log('STORE ' + code);
	localStorage.setItem(code, value);
};

$(window).ready(function() {
	if(typeof(window.__builder) == "undefined"){
		var cms = new builder();
		cms.init();
	}
});
