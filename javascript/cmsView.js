function cmsView(objName, recordView, recordId){
	/**
	 * Create object view. Has {tab} prototype
	 * @param window.__cmsView {Array}
	 * @param instanceKey {Number}
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
	 * @fires {Method} gridResize
	 */
	if(typeof(window.__cmsView) == "undefined"){
		window.__cmsView = new Array();
		window.__cmsView['instance'] = new Array();
	}
	
	if(typeof(recordView) == "undefined"){
		this.recordView = false;
	}else{
		this.recordView = recordView;
	}
	
	if(typeof(recordId) == "undefined"){
		this.recordId = 0;
	}else{
		this.recordId = recordId;
	}
	
	this.instanceKey = window.__cmsView['instance'].length;
	window.__cmsView['instance'][this.instanceKey] = this;
	
	this.code = objName;
	this.ml = false;
	this.name = null;
	this.container= null;
	this.root = null;
	this.loadData = false;
	this.rowNumber = 0;
	this.cellPerRow = 0;
	this.bodyStyle = window.getComputedStyle(document.getElementsByTagName('body')[0]);
	this.fake = null;
	this.headCell = new Array();
	this.lastCellInRow = new Array();
	this.parent = null;
	this.recordFields = new Array();
	this.saveTimeout = null;
	this.saveTime = 1000;
	this.fieldColl = new Array();
	
	builder.registerResize("window.__cmsView['instance'][" + this.instanceKey + "]", "gridResize");
	
	this.render = function(){
		this.root = this.tab(this.code);
		if(this.loadData && !this.recordView){
			var data = new ajax();
			data.async = true;
			data.call_back = "window.__cmsView['instance'][" + this.instanceKey + "].build";
			data.attributes = null;
			data.group = "template";
			data.className = "cmsView";
			data.methodName = "xGetGrid";
			data.register_argument("object", this.code);
			data.register_argument("ml", this.ml);
			data.send();
		}else if(this.recordView){
			var data = new ajax();
			data.async = true;
			data.call_back = "window.__cmsView['instance'][" + this.instanceKey + "].renderRecord";
			data.attributes = null;
			data.group = "template";
			data.className = "cmsView";
			data.methodName = "xGetRecordView";
			data.register_argument("object", this.parent.code);
			data.register_argument("ml", this.parent.ml);
			data.register_argument("recordId", this.recordId);
			data.send();
		}
	};
	
	this.build = function(dataStr){
		data = JSON.parse(dataStr);
		var div, a;
		
		this.data = data;
		
		div = new domElement('div');
		div.parent = this.root.elm;
		div.setCssClass('gridControl');
		div.setNewText(data.totalRecords.name + ' ' + data.totalRecords.value);
//		div.setNewText($(xml).find("totalRecords").attr('name') + ' ' + $(xml).find("totalRecords").attr('value'));
		div.render();

		a = new domElement('a');
		a.parent = div.elm;
		a.setCssClass('gridControl');
		a.setNewText(data.newRecord.value);
		a.setAttribute('eventCode', 'Record');
		a.caller = this;
		a.render();
		a.setEvent('onclick', 'Record');
		
		/*
		 * render data grid
		 */
		this.renderGrid();
		
		this.gridResize();
	};
	
	this.renderRecordFields = function(container, data){
		var table, tr, td, fld = null, name;
		
		table = new domElement("table");
		table.setCssClass("recordView");
		table.parent = container.elm;
		table.render();
		
//		var cellColl = data.getElementsByTagName("cell");
//		var dataColl = data.getElementsByTagName("data");
		
		for(var cellKey in data.cell){
			tr = new domElement("tr");
			tr.parent = table.elm;
			tr.render();
			
			td = new domElement("td");
			td.parent = tr.elm;
			name = data.cell[cellKey].name;
			td.setNewText(name);
			td.render();
			
			td = new domElement("td");
			td.parent = tr.elm;
			td.render();
			
			switch(data.cell[cellKey].type){
				case "DISABLE":
					fld = new domElement("input");
					fld.name = name;
					fld.elm.disabled = true;
					fld.elm.readOnly = true;
					fld.parent = td.elm;
					fld.render();
					break;
				case "INPUT":
					fld = new domElement("input");
					fld.name = name;
					fld.parent = td.elm;
					fld.setAttribute('eventCode', 'saveRecord');
					fld.caller = this;
					fld.render();
					fld.setEvent('onkeyup', 'saveRecord');
					break;
				case "BOOL":
					fld = new domElement("input");
					fld.name = name;
					fld.elm.type = "checkbox";
					fld.parent = td.elm;
					fld.render();
					break;
				case "SELECT":
					fld = new domElement("select");
					fld.name = name;
					fld.parent = td.elm;
					fld.render();
					break;
			}
			this.fieldColl[this.fieldColl.length] = fld;
			fld.setValue(data.cell[cellKey].data);
		}
	};
	
	this.renderRecord = function(dataStr){
		data = JSON.parse(dataStr);
		console.log("render record view");
		
		var hTag, div;
//		var data = xml.getElementsByTagName("recordView")[0];
//		var headerColl = data.getElementsByTagName("header");
		for(var headerKey in data.recordView.header){
			hTag = new domElement('h3');
			hTag.parent = this.root.elm;
			hTag.setNewText(data.recordView.header[headerKey].name);
			hTag.render();
			div = new domElement('div');
			div.parent = this.root.elm;
			div.render();
			this.renderRecordFields(div, data.recordView.header[headerKey]);
		}
	};
	
	this.saveRecord = function(){
		this.saveTimeout = null;
		var dataValue = new Array();
		var dataKey = new Array();
		for(var key = 0; key < this.fieldColl.length; key++){
			//console.log(JSON.stringify(value));
			dataKey[dataKey.length] = this.fieldColl[key].name
			dataValue[dataValue.length] = this.fieldColl[key].getValue();
		}
		//alert(data);
		var data = new ajax();
		data.async = true;
		data.attributes = null;
		data.group = "template";
		data.className = "cmsView";
		data.methodName = "xSaveRecord";
		data.register_argument("dataKey", JSON.stringify(dataKey));
		data.register_argument("dataValue", JSON.stringify(dataValue));
		data.send();
//		var fieldData = {};
//		for(var key in this.recordFields){
//			if(key != this.parent.code + "Id"){
//				fieldData[key] = this.recordFields[key].getValue();
//			}
//		}
//		console.log(JSON.stringify(fieldData));
//		data.register_argument("data", JSON.stringify(fieldData));
//		data.send();
		
	};
	
	this.renderGrid = function(){
		this.table = new domElement('table');
		this.table.parent = this.root.elm;
		this.table.setCssClass('dataGrid');
		this.table.render();
		
		this.thead = new domElement('thead');
		this.thead.parent = this.table.elm;
		this.thead.render();
		
		this.tbody = new domElement('tbody');
		this.tbody.parent = this.table.elm;
		this.tbody.caller = this;
		this.tbody.setAttribute('eventCode', 'scroll');
		this.tbody.render();
		this.tbody.setEvent('onscroll', 'scroll');
		
		this.tfoot = new domElement('tfoot');
		this.tfoot.parent = this.table.elm;
		this.tfoot.render();
		
		var timer = window.performance.now();
		var rowParent, row, cellColl, cell, cellTag, offset, isLastHeader, offsetFromHeader;
//		var grid = this.xml.getElementsByTagName('dataGrid')[0];
//		var rowColl = grid.getElementsByTagName('row');
		var cellText;
		this.rowNumber = 1;
//		this.rowNumber = rowColl.length;
		var cellWidth = new Array();
		var primaryKey = null;
		var cssClass;
		for(var rowKey in this.data.dataGrid.row){
			switch(this.data.dataGrid.row[rowKey].type){
				case 'header':
					rowParent = this.thead.elm;
					cellTag = 'th';
					break;
				case 'footer':
					rowParent = this.tfoot.elm;
					cellTag = 'td';
					break;
				default:
					rowParent = this.tbody.elm;
					cellTag = 'td';
			}
			row = new domElement('tr');
			row.parent = rowParent;
			row.render();
			
//			cellColl = rowColl[rowKey].getElementsByTagName('cell');
			if(cellTag == 'th'){
				this.cellPerRow = this.data.dataGrid.row[rowKey].cell.length;
			}
			offset = 0;
			isLastHeader = false;
			offsetFromHeader = 0;
//			var numberOfCells = cellColl.length;
			for(var cellKey in this.data.dataGrid.row[rowKey].cell){
				cssClass = "";
				if(this.data.dataGrid.row[rowKey].cell[cellKey].primary){
					primaryKey = cellKey;
				}
				if(this.data.dataGrid.row[rowKey].cell[cellKey].width && typeof(cellWidth[cellKey]) == "undefined"){
					cellWidth[cellKey] = this.root.elm.clientWidth * parseInt(this.data.dataGrid.row[rowKey].cell[cellKey].width) / 100;
					if(cellWidth[cellKey] < 30){
						cellWidth[cellKey] = 30;
					}
					cellWidth[cellKey] += "px";
				}
				cell = new domElement(cellTag);
				cell.parent = row.elm;
				cellText = this.data.dataGrid.row[rowKey].cell[cellKey].name;
				if(typeof(cellWidth[cellKey]) != "undefined"){
					cell.setStyle("width", cellWidth[cellKey]);
				}
				if(cellText == ""){
					cellText = "\u00A0";
				}
				cell.setNewText(cellText);
				if(this.data.dataGrid.row[rowKey].cell[cellKey].type == "header"){
					cssClass += " gridFixed";
					cell.setAttribute("offset", offset);
					cell.setStyle("left", offset + "px");
					this.headCell[this.headCell.length] = cell;
					isLastHeader = true;
				}else if(isLastHeader){
					cell.setStyle("margin-left", offsetFromHeader + "px");
					isLastHeader = false;
					offsetFromHeader = 0;
				}if(cellKey === primaryKey){
					cssClass += " openRecord";
					cell.setAttribute("recordId", parseInt(this.data.dataGrid.row[rowKey].cell[cellKey].name));
					cell.caller = this;
					cell.setAttribute('eventCode', 'Record');
				}
				cell.setCssClass(cssClass);
				cell.render();
				if(cellKey === primaryKey){
					cell.setEvent('onclick', 'Record');
				}
				if(this.data.dataGrid.row[rowKey].cell[cellKey].type == "header"){
					offsetFromHeader += cell.elm.offsetWidth;
				}
				offset = cell.elm.offsetLeft + cell.elm.offsetWidth;
			}
			this.lastCellInRow[this.lastCellInRow.length] = cell;
		}
		/*
		 * render fake/empty element for proper grid size
		 */
		row = new domElement('tr');
		row.setStyle("height", "0px");
		row.parent = this.tfoot.elm;
		row.setStyle("display", "block");
		row.render();
		
		cell = new domElement('td');
		cell.parent = row.elm;
		cell.setStyle("height", "0px");
		cell.setStyle("display", "block");
		cell.elm.setAttribute("colspan", this.cellPerRow);
		cell.render();
		
		this.fake = new domElement('div');
		this.fake.parent = cell.elm;
		this.fake.setStyle("height", "0px");
		this.fake.elm.style.position = "absolute";
		this.fake.elm.style.bottom = "0px";
		this.fake.elm.style.display = "none";
		this.fake.render();
		
		console.log((this.rowNumber * this.cellPerRow) + " elments render in grid for " + (window.performance.now() - timer) + "ms");
		
	};
	
	this.scrollEvent = function(){
//		events.stop(e);
		for(var cellKey = 0; cellKey < this.headCell.length; cellKey++){
			this.headCell[cellKey].elm.style.left = (this.tbody.elm.scrollLeft + parseInt(this.headCell[cellKey].getAttribute("offset"))) + "px";
		}
		this.thead.elm.style.marginLeft = -this.tbody.elm.scrollLeft + "px";
	};
	
	this.gridResize = function(){
		if(this.recordView){
			
		}else{
			var margin = this.root.elm.clientWidth;
			margin -= parseInt($(this.table.elm).css('margin-left'));
			margin -= parseInt($(this.table.elm).css('margin-right'));
			if(this.tbody.elm.scrollWidth < margin){
				var lastCellWidth = margin - this.tbody.elm.scrollWidth;
				lastCellWidth += parseInt(window.getComputedStyle(this.lastCellInRow[0].elm).getPropertyValue("width")) - 1;
				for(var cellKey = 0; cellKey < this.lastCellInRow.length; cellKey++){
					this.lastCellInRow[cellKey].setStyle("width", lastCellWidth + "px");
				}
			}
			this.table.elm.style.width = margin + 'px';
			this.fake.elm.style.width = this.root.elm.clientWidth + 'px';
			
			if(this.tbody.elm.scrollHeight > (this.root.elm.clientHeight - this.thead.elm.clientHeight - this.table.elm.previousSibling.clientHeight)){
				margin = this.root.elm.parentNode.clientHeight;
				margin -= parseInt($('.gridControl').css('margin-top'));
				margin -= this.table.elm.previousSibling.clientHeight;
				margin -= parseInt($(this.table.elm).css('margin-bottom'));
				this.tbody.elm.style.height = (margin - 25) + 'px';
			}else{
				this.tbody.elm.style.height = this.tbody.elm.scrollHeight + 'px';
			}
		}
	};
	
	this.handleOutsideEvent = function(obj, e){
		var call = obj.getAttribute('eventCode');
		if(call != "scroll"){
			console.log('event ' + call + " " + this.code);
		}
		switch (call) {
			case 'tabSwitch':
				this.tab(this.code);
				break;
			case 'tabClose':
				events.stop(e);
				this.tab(this.code, true);
				break;
			case 'scroll':
				this.scrollEvent();
				break;
			case 'Record':
				recordId = 0;
				if(obj.hasAttribute('recordId')){
					recordId = parseInt(obj.getAttribute('recordId'));
				}
				
				var Record = new cmsView(this.code + "_" + recordId, true, recordId);
				Record.parent = this;
				Record.render();
				break;
			case 'saveRecord':
				clearTimeout(this.saveTimeout);
				//some delay
				this.saveTimeout = setTimeout("window.__cmsView['instance'][" + this.instanceKey + "].saveRecord();", this.saveTime);
				break;
		}
	};
};