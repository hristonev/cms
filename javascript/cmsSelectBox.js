/**
 * Create object custom ajax loaded select
 * @param arg {Object} with properties table, fieldValue, fieldId, (recurseBy if is tree) {String}
 * @param elm {DomNode} input element to attach cmsSelectBox
 * @external ajax loaded from include/object.php
 */
function cmsSelectBox(arg, elm){
	this.element = elm;
	this.executeOnSelect = null;
	this.arg = arg;

	if(typeof(window.__cmsSelectBox) == "undefined"){
		window.__cmsSelectBox = new Array();
		window.__cmsSelectBox['instance'] = new Array();
	}
	var loadedKey = null;
	for(var i in window.__cmsSelectBox['instance']){
		if(JSON.stringify(window.__cmsSelectBox['instance'][i].arg) == JSON.stringify(arg)){
			loadedKey = i;
		}
	}

	this.instanceKey = window.__cmsSelectBox['instance'].length;
	window.__cmsSelectBox['instance'][this.instanceKey] = this;

	if(loadedKey === null){
		var data = new ajax();
		data.async = true;
		data.call_back = "window.__cmsSelectBox['instance'][" + this.instanceKey + "].collectData";
		data.attributes = null;
		data.group = "include";
		data.className = "object";
		data.methodName = "xGetSelectData";
		data.register_argument("arg", JSON.stringify(arg));
		data.send();
	}else{
		this.data = window.__cmsSelectBox["instance"][loadedKey].data;
		this.build();
	}
};
cmsSelectBox.prototype.collectData = function(dataStr){
	var obj = JSON.parse(dataStr)
	this.data = obj.data;
	this.build();
};

cmsSelectBox.prototype.build = function(){
	this.element.elm.parentNode.style.position = "relative";
	this.icon = new domElement("i");
	this.icon.parent = this.element.elm.parentNode;
	this.icon.insert_before = this.element.elm.nextSibling;
	this.icon.setCssClass("fa fa-angle-double-down cmsSelectBoxIcon");
	this.icon.render();

	this.element.setCssClass("cmsSelectBoxCaller");
	this.element.setAttribute('eventCode', 'showHide');
	this.element.caller = this;
	this.element.setEvent('onclick', 'showHide');
};

cmsSelectBox.prototype.render = function(){
	this.icon.setCssClass("fa fa-angle-double-up cmsSelectBoxIcon");
	this.container = new domElement("div");
	this.container.parent = this.element.elm.parentNode;
	this.container.setStyle("width", this.element.elm.offsetWidth + "px");
	this.container.setStyle("height", "0px");
	this.container.setCssClass("cmsSelectBox");
	this.container.render();
	var ul = new Array(), li, treeLevel, parentNode;
	for(var i in this.data){
		treeLevel = this.data[i].parent;
		if(typeof(ul[this.data[i].current]) == "undefined"){
			ul[this.data[i].current] = new domElement("ul");
			if(treeLevel > 1){
				parentNode = ul[treeLevel].elm;
			}else{
				parentNode = this.container.elm;
			}
			ul[this.data[i].current].parent = parentNode;
			ul[this.data[i].current].render();
		}
		if(typeof(this.data[i].cell[0]) != "undefined" && typeof(this.data[i].cell[1]) != "undefined"){
			li = new domElement("li");
			li.parent = ul[this.data[i].current].elm;
			li.setNewText(this.data[i].cell[0].name);
			li.setAttribute("id", this.data[i].cell[1].name);
			li.setAttribute('eventCode', 'select');
			li.caller = this;
			li.render();
			li.setEvent('onclick', 'select');
		}
	}
	animate(this.container, "expand", "y", 0, (this.element.elm.offsetHeight * 10), 20);
};

cmsSelectBox.prototype.selectAndClose = function(obj){
	this.element.elm.value = obj.elm.firstChild.nodeValue;
	this.element.elm.setAttribute("saveData", obj.getAttribute("id"));
	events.triggerEvent(this.element.elm, "change");
	this.remove();
};

cmsSelectBox.prototype.remove = function(){
	this.icon.setCssClass("fa fa-angle-double-down cmsSelectBoxIcon");
	animate(this.container, "expand", "y", (this.element.elm.offsetHeight * 10), 0, 20, true);
	this.container = null;
};

cmsSelectBox.prototype.handleOutsideEvent = function(obj, e){
	var call = obj.getAttribute('eventCode');
	if(e.type == "change"){
		call = "change";
	}
	switch (call) {
		case 'showHide':
			if(typeof(this.container) == "undefined" || this.container === null){
				this.render();
			}else{
				this.remove();
			}
			break;
		case 'select':
			this.selectAndClose(obj);
			break;
		case 'change':
			if(this.executeOnSelect !== null){
				eval(this.executeOnSelect);
			}
			break;
	}
};