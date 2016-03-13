function cmsSiteMap(){
	this.containerM = null;
	this.container = null;
	this.treeContainer = null;
	window.__cmsSiteMap = this;
	this.nodes = new Array();
};

cmsSiteMap.prototype.render = function(){
	this.containerM = new domElement("div");
	this.containerM.parent = document.getElementsByTagName("body")[0];
	this.containerM.setCssClass("popUpFixedMaster");
	this.containerM.render();

	this.container = new domElement("div");
	this.container.parent = document.getElementsByTagName("body")[0];
	this.container.setCssClass("popUpFixedContainer");
	this.container.render();

	var div = new domElement('div');
	div.setCssClass("popUpClose");
	div.parent = this.container.elm;
	div.render();

	var elm = new domElement('i');
	elm.parent = div.elm;
	elm.setCssClass('fa fa-times');
	elm.setAttribute('eventCode', 'close');
	elm.caller = this;
	elm.render();
	elm.setEvent('onclick', 'close');

	var data = new ajax();
	data.async = true;
	data.call_back = "window.__cmsSiteMap.build";
	data.attributes = null;
	data.group = "include";
	data.className = "siteMap";
	data.methodName = "xGetData";
	data.send();

};
cmsSiteMap.prototype.build = function(dataStr){
	data = JSON.parse(dataStr);

	var div = new domElement('div');
	div.setCssClass("attention");
	div.parent = this.container.elm;
	div.setNewText(data.workDir);
	div.render();

	var a = new domElement('a');
	a.caller = this;
	a.setCssClass("siteMapButton");
	a.parent = this.container.elm;
	a.setNewText(data.makeDirectories);
	a.setAttribute('eventCode', 'makeDir');
	a.render();
	a.setEvent('onclick', 'makeDir');

	//BUILD TREE
	this.treeContainer = this.container;
	this.buildTree(data.siteMap);
};

cmsSiteMap.prototype.buildTree = function(node){
	var li, key;
	var ul = new domElement('ul');
	ul.setCssClass("treeBranch");
	ul.parent = this.treeContainer.elm;
	for(var i in node){
		if(node[i].hasOwnProperty("name") && node[i].name.length > 0){
			li = new domElement('li');
			li.parent = ul.elm;
			li.setNewText(node[i].name);
			li.setAttribute("sqlId", node[i].sqlId);
			li.render();
			this.treeContainer = ul;
			ul.render();
			this.nodes[this.nodes.length] = li;
		}
		if(node[i].hasOwnProperty("children")){
			this.buildTree(node[i].children);
		}
	}
};

cmsSiteMap.prototype.makeDirectoryStructure = function(){
	if(this.tempNodeCollection.length > 0){
		var elm = this.tempNodeCollection.shift();
		var data = new ajax();
		data.async = true;
		data.call_back = "window.__cmsSiteMap.makeDirectoryStructure";
		data.attributes = null;
		data.group = "include";
		data.className = "siteMap";
		data.methodName = "xCreateNode";
		data.register_argument("id", elm.getAttribute("sqlId"));
		data.send();
	}
}

cmsSiteMap.prototype.destruct = function(){
	this.container.destruct();
	this.containerM.destruct();
};

cmsSiteMap.prototype.handleOutsideEvent = function(obj, e){
	var call = obj.getAttribute('eventCode');
	console.log('call: ' + call);
	switch (call) {
		case 'close':
			this.destruct();
			break;
		case 'makeDir':
			if(typeof(this.tempNodeCollection) == "undefined"){
				this.tempNodeCollection = this.nodes;
				this.makeDirectoryStructure();
			}
			break;
	}
};