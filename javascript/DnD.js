if(typeof(window.__dnd) == "undefined"){
	window.__dnd = {};
	window.__dnd.domNode = null;
	window.__dnd.current = null;
}

function tableDnD(table){
	/**
	 * Create table rows drag and drop handler
	 * @param table {DomNode} table node
	 * @param tree {Bool} table is tree
	 * @param tbody {DomNode} table body if not set corresponds to tbody node
	 * @param collectDefault {Bool} collect table rows by default. False if tree type table
	 * @param row {Array}
	 * @param rowTree {Array} two dimensional array
	 *
	 * @param dragItem item in drag condition
	 *
	 * @method init
	 *
	 * @param instanceKey {Number}
	 * @param tableName {String}
	 * @param code {String}
	 * @param name {String}
	 * @param container {DomNode} root node
	 * @param root {Object} root element for [view] returned by [tab]
	 * @param recordView {Bool}
	 * @param recordId {int}
	 * @param saveTimeout {timeout ID}
	 * @param saveTime {int} 1s default less server processor
	 * @param fieldColl {Array} collection with all data fields
	 * @param data {JSON Object}
	 * @param workingField {Object} dom object element
	 * @fires {Method} gridResize
	 */
	this.table = table;
	this.tree = false;
	this.tbody = null;
	this.collectDefault = false;
	this.row = new Array();
	this.rowTree = new Array();
	this.rowId = new Array();
	this.idRow = new Array();

	this.dragItemKey = null;
	this.dragItemParentKey = null;
	this.replaceItem = null;

	this.init = function(collectDefault){
		if(typeof(collectDefault) != "undefined"){
			this.collectDefault = collectDefault;
		}
		if(this.tbody == null){
			this.tbody = this.table.getElementsByTagName("tbody")[0];
		}
		if(this.collectDefault){
			console.log("collect default table row");
		}
	};

	this.addRow = function(row, current, parent){
		var rowKey = this.row.length;
		this.row[rowKey] = row;
		this.rowId[rowKey] = current;

		this.makeDraggable(this.row[rowKey], rowKey, parent);

		if(this.tree){
			if(parent > 0){
				if(typeof(this.rowTree[parent]) == "undefined"){
					console.log("no parent to this node");
				}else{
					this.rowTree[parent][current] = rowKey;
					this.rowTree[current] = new Array();
				}
			}else{
				this.rowTree[current] = new Array();
			}
		}
	};

	this.dropEffect = function(itemKey, position){
		switch(position){
			case "top":
				this.row[itemKey].style.opacity = "0.5";
				break;
			case "bottom":
				this.row[itemKey].style.opacity = "0.5";
				break;
			default:
				this.row[itemKey].style.opacity = "0.8";
		}
	};

	this.makeDraggable = function(item, rowKey, parent){
		var self = this;
		item.onmousedown = function(e){
			self.dragItemKey = rowKey;
			self.dragItemParentKey = parent;
			window.__dnd.domNode = this;
			window.__dnd.current = self;
			self.styleDragOn(e);
			console.log("start drag item " + rowKey);

			return events.stop(e);
		}
		item.onmouseover = function(e){
			if(window.__dnd.domNode != null && window.__dnd.current != null){
				self.dragOver = rowKey;
			}
		}
	};

	this.styleDragOn = function(e){
		this.row[this.dragItemKey].style.opacity = "0.3";
		if(this.tree){
			for(var i in this.rowTree[this.dragItemParentKey]){
				if(this.dragItemKey != this.rowTree[this.dragItemParentKey][i]){
					this.row[this.rowTree[this.dragItemParentKey][i]].style.opacity = "0.8";
				}
			}
		}
		this.startCoords = events.mouseCoords(e);
	};

	this.styleDragOff = function(){
		this.row[this.dragItemKey].style.opacity = "1";
		if(this.tree){
			for(var i in this.rowTree[this.dragItemParentKey]){
				if(this.dragItemKey != this.rowTree[this.dragItemParentKey][i]){
					this.row[this.rowTree[this.dragItemParentKey][i]].style.opacity = "1";
				}
			}
		}else{
			for(var i in this.row){
				this.row[i].style.opacity = "1";
			}
		}
	};

	this.getPosition = function(e){
    	var coords = events.mouseCoords(e);
    	this.replaceItem = null;
    	if(this.tree){
			for(var i in this.rowTree[this.dragItemParentKey]){
				if(this.dragOver == this.rowTree[this.dragItemParentKey][i] && this.dragItemKey != this.rowTree[this.dragItemParentKey][i]){
					this.replaceItem = this.dragOver;
					if(coords.y > this.startCoords.y){
						this.dropEffect(this.dragOver, "bottom");
						this.replaceItemBefore = false;
					}else{
						this.dropEffect(this.dragOver, "top");
						this.replaceItemBefore = true;
					}
				}else if(this.dragItemKey != this.rowTree[this.dragItemParentKey][i]){
					this.dropEffect(this.rowTree[this.dragItemParentKey][i]);
				}
			}
    	}else{
    		for(var i in this.row){
    			if(this.dragOver == i && this.dragItemKey != i){
    				this.replaceItem = this.dragOver;
					if(coords.y > this.startCoords.y){
						this.dropEffect(this.dragOver, "bottom");
						this.replaceItemBefore = false;
					}else{
						this.dropEffect(this.dragOver, "top");
						this.replaceItemBefore = true;
					}
    			}else if(this.dragItemKey != i){
					this.dropEffect(i);
    			}
    		}
    	}
	};

	this.dropItem = function(){
		var collection = {};
		collection.source = this.rowId[this.dragItemKey];
		collection.target = this.rowId[this.replaceItem];
		if(this.replaceItem != null){
			if(this.replaceItemBefore){
				this.tbody.insertBefore(this.row[this.dragItemKey], this.row[this.replaceItem]);
			}else{
				this.tbody.insertBefore(this.row[this.dragItemKey], this.row[this.replaceItem].nextSibling);
			}
			if(this.tree){
				if(!this.replaceItemBefore){

					this.moveChildNodes(this.rowId[this.replaceItem], this.row[this.replaceItem].nextSibling);
				}
				this.moveChildNodes(this.rowId[this.dragItemKey], this.row[this.dragItemKey].nextSibling);
			}
		}
		console.log(collection);
	};

	this.moveChildNodes = function(id, parent){
		if(typeof(this.rowTree[id]) != "undefined"){
			for(var i in this.rowTree[id]){
				this.tbody.insertBefore(this.row[this.rowTree[id][i]], parent);
				this.moveChildNodes(this.rowId[this.rowTree[id][i]], this.row[this.rowTree[id][i].nextSibling]);
			}
		}
	}
}

document.onmousemove = function(e){
    if (window.__dnd.domNode != null && window.__dnd.current != null) {
    	window.__dnd.current.getPosition(e);
        return events.stop(e);
    }
}

// Similarly for the mouseup
document.onmouseup   = function(e){
    if (window.__dnd.domNode != null && window.__dnd.current != null) {
    	console.log("stop drag item " + window.__dnd.current.dragItemKey);
    	window.__dnd.current.styleDragOff();
    	window.__dnd.current.dropItem();

		window.__dnd.domNode = null;
		window.__dnd.current = null;
    }
}