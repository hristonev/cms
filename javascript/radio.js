function radio(obj){
	this.elm = obj;
	this.data = null;
	this.nodes = new Array();

	this.init = function(){
		var node;
		this.elm.style.display = "none";
		this.elm.checked = true;
		this.nodes[this.nodes.length] = this.elm;
		if(this.data != null){
			for(var i in this.data){
				if(this.elm.value != this.data[i]){
					node = this.elm.cloneNode(true);
					node.value = this.data[i];
					node.checked = false;
					this.elm.parentNode.appendChild(node);
					this.nodes[this.nodes.length] = node;
				}
			}
		}
		if(this.data.length == 2){
			this.icons = new Array();
			this.icons[0] = "fa fa-toggle-off radioOff";
			this.icons[1] = "fa fa-toggle-on radioOn";
			var icon = new domElement("i");
			icon.parent = this.elm.parentNode;
			icon.setCssClass(this.icons[0]);
			icon.render();
			var self = this;
			icon.elm.onclick = function(){
				if(this.className == self.icons[0]){
					this.className = self.icons[1];
					self.nodes[1].checked = true;
				}else{
					this.className = self.icons[0];
					self.nodes[0].checked = true;
				}
			};
		}
	};
};