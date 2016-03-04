function navigation(){

	this.resize = null;
	this.instance = null;
	this.instanceKey = null;
	this.body = document.getElementsByTagName("html")[0];
	this.navigation = document.getElementById("navigation");
	this.menuContainer = document.getElementById("menu");
	this.menuPrefix = null;
	this.menu = new Array();
	this.mouseDown = false;

	this.init = function(){
		//setup instance in window for event handler and future use of same instance
		window.__navigation = this;
		this.menuPrefix = this.menuContainer.id + "_";
		this.collectMenu(this.menuContainer);
		this.makeActive();
	};

	this.makeActive = function(){
		for(var z = 0; z < this.menu.length; z++){
			msg("add event " + this.menu[z].id);
			events.register_event_handler(this.menu[z], "onclick", function(e){
				if(typeof(window.__navigation.lastActive) != "undefined"){
					window.__navigation.lastActive.className = "";
				}
				obj = events.get_affected_element(e);
				var id = obj.getAttribute("id");
				msg("click " + id);
				var gridId = id;
				var gridObject = id;

				var grid = new list();
				grid.id = gridId;
				grid.dbObject = gridObject;
				grid.parentElm = document.getElementById("listContent");
				window.__navigation.lastActive = obj;
				msg("init grid " + gridId);
				if(grid.init()){
					obj.className = "navigationActive";
					msg("render grid");
					grid.render();
				}else{

				}
			});
		}
	};

	this.collectMenu = function(obj){
		var nodes = obj.childNodes;
		for(var z = 0; z < nodes.length; z++){
			if(nodes[z].tagName && nodes[z].hasAttribute("id")){
				this.menu[this.menu.length] = nodes[z];
				msg("colect menu item " + nodes[z].id);
			}
			if(nodes[z].childNodes.length > 0){
				this.collectMenu(nodes[z]);
			}
		}
	};

	this.startResize = function(e){
		if(!this.mouseDown){
			events.register_event_handler(this.body, "onmousemove", function(e){
				//e = event || window.event; // IE-ism
				obj = events.get_affected_element(e);
				window.__navigation.resize(e);
				});
			events.register_event_handler(this.body, "onmouseup", function(e){
				//e = event || window.event; // IE-ism
				obj = events.get_affected_element(e);
				window.__navigation.stopResize(e);
				});
			this.mouseDown = true;
		}
	};

	this.stopResize = function(e){
		events.remove_events(this.body);
		this.mouseDown = false;
	};

	this.resize = function(e){
		if(this.mouseDown){
			this.navigation.style.width = e.clientX + "px";
		}
	};

}