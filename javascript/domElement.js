function domElement(tag_name){
	//instance
	if(typeof(window.__domElement) == "undefined"){
		window.__domElement = new Array();
	}
	//private
	this.elm = document.createElement(tag_name);
	this.event_code = new Array();
	this.txt_node = null;
	this.instance_key = window.__domElement.length;
	window.__domElement[this.instance_key] = this;

	//public
	this.tag = tag_name;
	this.name = null;
	this.value = null;
	this.parent = null;
	this.text = null;
	this.caller = null;
	this.insert_before = null;
	this.insert_after = null;

	this.elm.is_domElement = function(){
		return true;
	};
	this.elm.destruct = function(){
		if(this.childNodes.length > 0){
			var firstChild = this.firstChild;
			while(firstChild){
				if(firstChild.hasOwnProperty("is_domElement") && firstChild.is_domElement()){
					firstChild.destruct();
				}else{
					firstChild.parentNode.removeChild(firstChild);
				}
				firstChild = this.firstChild;
			}
		}
		var instance = window.__domElement[this.getAttribute('domElement_instance')];
		delete window.__domElement[this.getAttribute('domElement_instance')];

		instance.remove_all_events();
		this.parentNode.removeChild(this);
		delete this;
	};
};
domElement.prototype = {
	getElm: function(){
		return this.elm;
	},

	getValue: function(){
		return this.elm.value;
	},

	setValue: function(value){
		this.elm.value = value;
	},

	setStyle: function(name, value){
		this.elm.style[name] = value;
	},

	setTitle: function(value){
		this.elm.setAttribute('title', value);
	},

	setCssClass: function(value){
		this.elm.className = value;
	},
	setAttribute: function(name, value){
		this.elm.setAttribute('dom_attr_' + name, value);
	},

	getAttribute: function(name, value){
		return this.elm.getAttribute('dom_attr_' + name);
	},

	hasAttribute: function(name, value){
		return this.elm.hasAttribute('dom_attr_' + name);
	},

	setEvent: function(event, code){
		this.event_code[event] = code;
		events.registerEventHandler(this.elm, event, function(e){
			obj = events.getAffectedElement(e);
			window.__domElement[obj.getAttribute('domElement_instance')].handleEvent(e);
			});
	},
	removeAllEvents: function(){
		if(this.event_code.length > 0){
			for(i in this.event_code){
				events.removeEventHandler(this.elm, event, function(e){
					obj = events.getAffectedElement(e);
					window.__domElement[obj.getAttribute('domElement_instance')].handle_event(e);
					});
			}
		}
	},

	setNewText: function(text){
		this.text = text;
	},

	handleEvent: function(e){
		this.caller.handleOutsideEvent(this, e);
	},

	render: function(){
		if(this.text != null){
			this.txt_node = document.createTextNode(this.text);
			this.elm.appendChild(this.txt_node);
		}

		//this.elm = document.createElement(this.tag);
		this.elm.setAttribute('domElement_instance', this.instance_key);
		if(this.insert_before == null && this.insert_after == null){
			this.parent.appendChild(this.elm);
		}else if(this.insert_after == null){
			this.parent.insertBefore(this.elm, this.insert_before);
		}else if(this.insert_before == null){
			this.parent.insertBefore(this.elm, this.insert_after.nextSibling);
		}
		//window.__domElement[this.instance_key] = this;
	},
	destruct: function(){
		this.elm.parentNode.removeChild(this.elm);
	},
	destructChildren: function(){
		var firstChild = this.elm.firstChild;
		while(firstChild){
			this.elm.removeChild(firstChild);
			firstChild = this.elm.firstChild;
		}
	}
};
