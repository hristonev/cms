function xml(){
	this.doc = document.implementation.createDocument("", "", null);

	this.root = doc.createElement("root");
	doc.appendChild(this.root);

	return doc;
}

xml.prototype = {
		addNode: function(name, value, appendToRoot, appendTo){
			if(typeof(name) == "undefined"){
				name = "node";
			}
			if(typeof(value) == "undefined"){
				value = "";
			}
			if(typeof(appendToRoot) == "undefined"){
				appendToRoot = true;
			}
			if(typeof(appendTo) == "undefined"){
				appendTo = null;
			}

			var node = this.doc.createElement(name);

			return node;
		},

		getRoot: function(){
			return this.root;
		}
};