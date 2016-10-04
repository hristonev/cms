function radio(obj){
	/**
	 *
	 * Modify radio input
	 * @param elm {Object} Element for conversion DOM Node
	 * @param data {Array} Posible statements {0,1}, 1-on state, 0-off state
	 * @param nodes {Array}
	 */
	this.elm = obj;
	this.data = null;
	this.nodes = new Array();

	this.init = function(){
		var node;
		this.elm.style.display = "none";
		this.elm.setAttribute("checked", true);
		this.nodes[this.nodes.length] = this.elm;
		if(this.data != null){
			for(var i in this.data){
				if(this.elm.value != this.data[i]){
					node = this.elm.cloneNode(true);
					node.value = this.data[i];
					node.setAttribute("checked", false);
					this.elm.parentNode.appendChild(node);
					this.nodes[this.nodes.length] = node;
				}else{
					var value = this.elm.value;
				}
			}
		}
		if(this.data != null && this.data.length == 2){
			this.icons = new Array();
			this.icons[0] = "fa fa-toggle-off radioOff";
			this.icons[1] = "fa fa-toggle-on radioOn";
			var icon = new domElement("i");
			icon.parent = this.elm.parentNode;
			icon.setCssClass(this.icons[value]);
			icon.render();
			var self = this;
			icon.elm.onclick = function(){
				if(this.className == self.icons[0]){
					this.className = self.icons[1];
				}else{
					this.className = self.icons[0];
				}
				for(var i in self.nodes){
					if(JSON.parse(self.nodes[i].getAttribute("checked")) === true){
						self.nodes[i].setAttribute("checked", false);
					}else{
						self.nodes[i].setAttribute("checked", true);
					}
				}
			};
		}else{
			console.log("no data present for radio");
		}
	};
};