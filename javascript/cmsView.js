function cmsView(objName, recordView, recordId){
	/**
	 * Create object view. Has {tab} prototype
	 * @param window.__cmsView {Array}
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

	this.tableName = null;
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
	this.saveTime = 100;
	this.cellCollection = new Array();
	this.langId = 1;//HARDCODE REMOVE ASAP
	this.workingField = null;
	this.waitSaveId = false;
	this.primaryFld = null;

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
		if(typeof(this.data.customTemplate) == "undefined"){
			div = new domElement('div');
			div.parent = this.root.elm;
			div.setCssClass('gridControl');
			div.setNewText(data.totalRecords.name + ' ' + data.totalRecords.value);
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
		}else{
			eval("var template = new " + this.data.customTemplate + "();");
			template.data = this.data;
			template.caller = this;
			template.render();
		}
	};

	this.renderRecordFields = function(container, data, langId){
		var container, table, tr, td, fld = null, name, langFldId, editor, datetime;

		table = new domElement("table");
		table.setCssClass("recordView");
		table.parent = container.elm;
		table.render();

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
					fld.setCssClass("recordViewDisable");
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
					fld = new domElement("input");
					fld.elm.readOnly = true;
					fld.name = name;
					fld.parent = td.elm;
					fld.setAttribute('eventCode', 'saveRecord');
					fld.caller = this;
					fld.render();
					if(typeof(data.cell[cellKey].collectData) != "undefined"){
						fld.setEvent('onchange', 'saveRecord');
						var selectBox = new cmsSelectBox(data.cell[cellKey].collectData, fld);
						selectBox.executeOnSelect = "window.__cmsView['instance'][" + this.instanceKey + "].workingField = domElement(" + fld.getInstanceKys() + ");";
						selectBox.executeOnSelect += "window.__cmsView['instance'][" + this.instanceKey + "].saveData();";
					}
					break;
				case "TEXT":
					fld = new domElement("textarea");
					fld.name = name;
					langFldId = name + "_" + data.cell[cellKey].langId + "_" + this.recordId;
					fld.elm.id = langFldId;
					fld.parent = td.elm;
					fld.render();
					fld.editor = CKEDITOR.replace(langFldId);
					fld.editor.caller = this;
					fld.editor.field = data.cell[cellKey].field;
					fld.editor.langId = data.cell[cellKey].langId;
					fld.editor.on("change", function(){
						this.caller.workingField = this;
						this.caller.saveData();
					});
					break;
				case "DATETIME":
					fld = new domElement("input");
					fld.name = name;
					fld.parent = td.elm;
					fld.caller = this;
					fld.render();
					datetime = new calendar(fld, this, 'saveRecord', true);
					break;
			}
			fld.field = data.cell[cellKey].field;
			fld.langId = langId;
			if(typeof(data.cell[cellKey].data) != "undefined"){
				fld.setValue(decodeURIComponent(data.cell[cellKey].data));
			}
			if(name == "langId"){
				fld.setValue(langId);
			}
			if(data.cell[cellKey].field == "id"){
				this.primaryFld = fld;
			}
		}
	};

	this.renderRecord = function(dataStr){
		data = JSON.parse(dataStr);
		this.recordData = data;

		container = new domElement("div");
		container.setCssClass("recordViewContainer");
		container.parent = this.root.elm;
		container.render();

		var hTag, div;
		for(var headerKey in data.recordView.header){
			hTag = new domElement('h3');
			hTag.parent = container.elm;
			hTag.setNewText(data.recordView.header[headerKey].name);
			hTag.render();
			div = new domElement('div');
			div.setCssClass("recordViewBorder");
			div.parent = container.elm;
			div.render();
			this.renderRecordFields(div, data.recordView.header[headerKey], data.recordView.header[headerKey].langId);
		}

		this.primaryFld.setStyle("width", "50%");
		var recordDelete = new domElement("div");
		recordDelete.parent = this.primaryFld.elm.parentNode;
		recordDelete.caller = this;
		recordDelete.setCssClass("recordViewDelete");
		recordDelete.setNewText(data.deleteKwd);
		recordDelete.setAttribute('eventCode', 'deleteConfirm');
		recordDelete.render();
		recordDelete.setEvent('onclick', 'deleteConfirm');
	};

	this.deleteConfirm = function(){
		this.dialog = new popUp("recordDeleteConfirm");

		var alert = new domElement("h2");
		alert.setCssClass("recordViewDeleteAlert");
		alert.parent = this.dialog.container.elm;
		alert.setNewText(this.recordData.alertDelete + this.primaryFld.elm.value);
		alert.render();

		var positive = new domElement("div");
		positive.parent = this.dialog.container.elm;
		positive.caller = this;
		positive.setCssClass("recordViewDeleteConfirmPositive");
		positive.setNewText(data.positive);
		positive.setAttribute('eventCode', 'deleteConfirmPositive');
		positive.render();
		positive.setEvent('onclick', 'deleteConfirmPositive');

		var negative = new domElement("div");
		negative.parent = this.dialog.container.elm;
		negative.caller = this;
		negative.setCssClass("recordViewDeleteConfirmNegative");
		negative.setNewText(data.negative);
		negative.setAttribute('eventCode', 'deleteConfirmNegative');
		negative.render();
		negative.setEvent('onclick', 'deleteConfirmNegative');
	}

	this.saveRecord = function(fld){
		var dataSend = {};
		var htmlData;
		dataSend.recordId = this.recordId;
		dataSend.field = this.workingField.field;
		dataSend.langId = this.workingField.langId;
		dataSend.tableName = this.tableName;
		if(typeof(this.workingField.mode) != "undefined" && this.workingField.mode == "wysiwyg"){//field is converted as wysiwyg editor
			htmlData = this.workingField.getData();
		}else if(this.workingField.elm.hasAttribute("saveData")){
			htmlData = this.workingField.elm.getAttribute("saveData");
		}else{
			htmlData = this.workingField.getValue();
		}
		if(htmlData.length > 0){
			htmlData = htmlData.replace(/"/g, '\\"');
			htmlData = htmlData.replace(/\r|\n|\r\n|\n\r|\t/g, "");

		}
		dataSend.value = encodeURIComponent(htmlData);

		this.saveTimeout = null;
		if(!this.waitSaveId){
			var data = new ajax();
			data.async = true;
			data.call_back = "window.__cmsView['instance'][" + this.instanceKey + "].saveRecordBack";
			data.attributes = null;
			data.group = "template";
			data.className = "cmsView";
			data.methodName = "xSaveRecord";
			data.register_argument("data", JSON.stringify(dataSend));
			data.send();
		}else{
			for(var key = 0; key < this.fieldColl.length; key++){
				console.log(this.fieldColl[key]);
			}
			console.log("wait for record id");
		}

		if(this.recordId == 0 && !this.waitSaveId){
			this.waitSaveId = true;
		}
	};

	this.saveRecordBack = function(dataStr){
		var data = JSON.parse(dataStr);
		if(parseInt(data.recordId) > 0){
			this.recordId = data.recordId;
			this.primaryFld.setValue(this.recordId);
			this.waitSaveId = false;
			this.tabChangeName(this.recordId);
		}
	}

	this.renderGrid = function(){
		this.table = new domElement('table');
		this.table.parent = this.root.elm;
		this.table.setCssClass('dataGrid');
		this.table.render();
		if(this.data.dataGrid.hasWeight){
			this.dnd = new tableDnD(this.table.elm);
			if(this.data.dataGrid.maxTreeLevel > 0){
				this.dnd.tree = true;
			}
			this.dnd.caller = this;
		}

		this.thead = new domElement('thead');
		this.thead.parent = this.table.elm;
		this.thead.render();

		this.tbody = new domElement('tbody');
		this.tbody.parent = this.table.elm;
		this.tbody.caller = this;
		this.tbody.setAttribute('eventCode', 'scroll');
		this.tbody.render();
		this.tbody.setEvent('onscroll', 'scroll');
		if(this.data.dataGrid.hasWeight){
			this.dnd.init();
		}

		this.tfoot = new domElement('tfoot');
		this.tfoot.parent = this.table.elm;
		this.tfoot.render();

		var timer = window.performance.now();
		var rowParent, row, cellColl, cell, cellTag, offset, isLastHeader, offsetFromHeader;
		var cellText;
		this.rowNumber = 1;
		var cellWidth = new Array();
		var primaryKey = null;
		var cssClass, recordId;
		var cellKeys = new Array();
		var treeLevel;
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
			if(this.data.dataGrid.hasWeight){
				if(this.dnd.tree){
					this.dnd.addRow(
						row.elm
						, this.data.dataGrid.row[rowKey].current
						, this.data.dataGrid.row[rowKey].parent
					);
				}else{
					this.dnd.addRow(row.elm, this.data.dataGrid.row[rowKey].current);
				}
			}

			if(cellTag == 'th'){
				this.cellPerRow = this.data.dataGrid.row[rowKey].cell.length;
			}
			offset = 0;
			isLastHeader = false;
			offsetFromHeader = 0;
			treeLevel = this.data.dataGrid.row[rowKey].treeLevel;
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
				cell.setTitle(cellText);
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
					if(cellKey === primaryKey && this.data.dataGrid.row[rowKey].cell[cellKey].primary && this.data.dataGrid.maxTreeLevel > 0){
						cell.setStyle("width", parseInt(cellWidth[cellKey]) + ((this.data.dataGrid.maxTreeLevel - 1) * 20) + "px");
						cell.setStyle("padding", "0");
						cell.elm.style.textAlign = "center";
					}
				}else if(isLastHeader){
					cell.setStyle("margin-left", offsetFromHeader + "px");
					isLastHeader = false;
					offsetFromHeader = 0;
				}if(cellKey === primaryKey){
					cssClass += " openRecord";
					recordId = parseInt(this.data.dataGrid.row[rowKey].cell[cellKey].name);
					this.cellCollection[recordId] = new Array();
					if(isNaN(recordId)){
						recordId = 0;
					}
					cell.setAttribute("recordId", recordId);
					cell.caller = this;
					cell.setAttribute('eventCode', 'Record');
				}
				cell.setCssClass(cssClass);
				cell.render();
				if(cellKey === primaryKey && recordId > 0){
					cell.setEvent('onclick', 'Record');
					cell.writeProtect = true;
					if(this.data.dataGrid.maxTreeLevel > 0){
						cell.elm.style.paddingLeft = ((treeLevel - 1) * 20) + "px";
						cell.elm.style.paddingRight = ((this.data.dataGrid.maxTreeLevel - treeLevel) * 20) + "px";
					}
				}
				if(rowKey == 0){//collect first row as array keys for cellCollection
					cellKeys[cellKey] = this.data.dataGrid.row[rowKey].cell[cellKey].code;
				}else if(cellTag != "th"){
					this.cellCollection[recordId][cellKeys[cellKey]] = cell.getInstanceKys();
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
		log("Datagrid rendered for " + (window.performance.now() - timer).toFixed(3) + "ms");

	};

	this.scrollEvent = function(){
//		events.stop(e);
		for(var cellKey = 0; cellKey < this.headCell.length; cellKey++){
			this.headCell[cellKey].elm.style.left = (this.tbody.elm.scrollLeft + parseInt(this.headCell[cellKey].getAttribute("offset"))) + "px";
		}
		this.thead.elm.style.marginLeft = -this.tbody.elm.scrollLeft + "px";
	};

	this.gridResize = function(){
		if(this.recordView || typeof(this.data.customTemplate) != "undefined"){

		}else if(typeof(this.table) != "undefined"){
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

	this.saveData = function(){
		clearTimeout(this.saveTimeout);
		//save delay
		this.saveTimeout = setTimeout("window.__cmsView['instance'][" + this.instanceKey + "].saveRecord();", this.saveTime);
	};

	this.deleteRecord = function(){
		var data = new ajax();
		data.async = true;
		data.call_back = "window.__cmsView['instance'][" + this.instanceKey + "].deleteRecordBack";
		data.attributes = null;
		data.group = "template";
		data.className = "cmsView";
		data.methodName = "xDeleteRecord";
		data.register_argument("object", this.code);
		data.register_argument("id", this.recordId);
		data.send();
	};

	this.deleteFromGrid = function(rowId){
		var cell, parentNode;
		for(var i in this.cellCollection[rowId]){
			cell = new domElement(this.cellCollection[rowId][i]);
			parentNode = cell.parent;
			cell.destruct();
		}
		parentNode.parentNode.removeChild(parentNode);
		this.gridResize();
	};

	this.deleteRecordBack = function(dataStr){
		var data = JSON.parse(dataStr);

		this.tab(this.code, true);
		this.dialog.destruct();
		if(typeof(data.recordId) != "undefined" && parseInt(data.recordId) > 0){
			this.parent.deleteFromGrid(parseInt(data.recordId));
		}
	};

	this.changeWeight = function(obj){
		var data = new ajax();
		data.async = true;
		data.call_back = "";
		data.attributes = null;
		data.group = "template";
		data.className = "cmsView";
		data.methodName = "xchangeWeight";
		data.register_argument("table", this.code);
		data.register_argument("source", obj.source);
		data.register_argument("target", obj.target);
		data.send();
	};

	this.handleOutsideEvent = function(obj, e, call){
		if(typeof(call) == "undefined"){
			var call = obj.getAttribute("eventCode");
		}
		if(call != "scroll"){
			console.log('event ' + call + " " + this.code);
		}
		switch (call) {
			case 'tabSwitch':
				this.tab(this.code);
				this.gridResize();
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
				Record.tableName = this.code;
				Record.render();
				break;
			case 'saveRecord':
				this.workingField = obj;
				this.saveData(obj);
				break;
			case 'deleteConfirm':
				this.deleteConfirm();
				break;
			case 'deleteConfirmNegative':
				this.dialog.destruct();
				break;
			case 'deleteConfirmPositive':
				this.deleteRecord();
				break;
			case 'DnD':
				this.changeWeight(obj);
				break;
		}
	};
};