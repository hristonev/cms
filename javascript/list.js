function list(){
	
	this.id = null;
	this.xmlObject = null;
	this.dbObject = null;
	this.parentElm = null;
	this.loadData = true;
	
	if(typeof(window.__list) == "undefined"){
		window.__list = new Array();
		window.__listBusy = false;
	}
	
	this.init = function(){
		var responce = false;
		msg("check for open grids and close them");
		for(var z in window.__list){
			if(typeof(window.__list[z].table) != "undefined"){
				if(z != this.id){
					window.__list[z].table.setStyle("display", "none");
				}else{
					msg("open preloaded grid no server loading");
					window.__list[z].table.setStyle("display", "");
					window.__list[z].resize();
					this.loadData = false;
					responce = true;
				}
			}
		}
		
		if(window.__listBusy == false && this.loadData){
			msg("make new grid load data from server");
			window.__list[this.id] = this;
			responce = true;
		}
		if(this.id == "" || this.dbObject == "" || this.id === null || this.dbObject === null){
			msg("no id or xmlObject!");
			responce = false;
		}
		return responce;
	};
	
	this.render = function(){
		if(window.__listBusy == false){
			window.__listInUse = this.id;
			if(this.loadData){
				window.__listBusy = true;
				loaderOn();
				var data = new ajax();
				data.async = true;
				data.call_back = "window.__list['" + this.id + "'].readXml";
				data.attributes = null;
				data.file_path = "grid.php";
				data.class_name = "grid";
				data.method_name = "x_getData";
				data.register_argument("object", this.dbObject);
				data.send();
			}
		}
	};
	
	this.readXml = function(xml, attr){
		this.xmlObject = xml;
		this.buildHeader();
		this.buildData();
		this.resize();
		window.__listBusy = false;
		loaderOff();
	};
	
	this.buildData = function(){
		this.tbody = new domElement("tbody");
		this.tbody.parent = this.table.elm;
		this.tbody.render();
		var group = new Array(), groupKey, addGroup, primary = 0;
		if(this.xmlObject.getElementsByTagName("data").length > 0){
			var data = this.xmlObject.getElementsByTagName("data")[0].childNodes;
			var tr, td, div, value, input;
			for(var z = 0; z < data.length;){
				tr = new domElement("tr");
				tr.parent = this.tbody.elm;
				tr.render();
				for(var x = 0; x < this.header.length; x++){
					if(data[z].firstChild === null && data[z].tagName == "cell"){
						value = "";
					}else{
						value = data[z].firstChild.nodeValue;
					}
					if(this.header[x].firstChild.nodeValue == this.dependancy){//group
						if(value == ""){
							groupKey = 0;
						}else{
							groupKey = value;
						}
						addGroup = true;
						for(var i = 0; i < group.length; i++){
							if(group[i] == groupKey){
								addGroup = false;
							}
						}
						if(addGroup){
							group[group.length] = groupKey;
						}
						if((group.length % 2) == 0){
							tr.elm.className = "listGroup";
						}
					}
					
					td = new domElement("td");
					td.parent = tr.elm;
					td.render();
					div = new domElement("div");
					div.parent = td.elm;
					if(this.header[x].hasAttribute("primary")){
						primary = value;
						div.setCssClass("listOpenDataForm");
					}
					switch(this.header[x].getAttribute("type")){
						case "tinyint(1) unsigned"://checkbox 0/1
							input = new domElement("input");
							input.parent = div.elm;
							input.elm.type = "checkbox";
							input.setAttribute("listInstanceId", this.id);
							input.setAttribute("listHeaderNum", x);
							input.setAttribute("listPrimaryKey", primary);
							input.render();
							div.elm.style.textAlign = "center";
							if(value == 1){
								input.elm.checked = "checked";
							}
							events.register_event_handler(input.elm, "onchange", function(e){
								var obj = events.get_affected_element(e);
								var instance = window.__list[obj.getAttribute("dom_attr_listinstanceid")];
								loaderOn();
								var data = new ajax();
								data.async = true;
								data.call_back = "window.__list['" + instance.id + "'].actionCheckbox";
								data.attributes = obj;
								data.file_path = "grid.php";
								data.class_name = "grid";
								data.method_name = "x_updateCheckbox";
								data.register_argument("object", instance.dbObject);
								data.register_argument("primaryKey", obj.getAttribute("dom_attr_listPrimaryKey"));
								data.register_argument("field", instance.header[obj.getAttribute("dom_attr_listHeaderNum")].getAttribute("field"));
								data.register_argument("value", +obj.checked);
								data.send();
								//window.__list[this.id];
								e.preventDefault();
								e.stopPropagation();
								return false;
							});
							break;
						case "int(12) unsigned"://weight
							div.elm.style.textAlign = "center";
							
							input = new domElement("div");
							input.parent = div.elm;
							input.setCssClass("listWeightDecrease");
							input.setAttribute("listInstanceId", this.id);
							input.setAttribute("listHeaderNum", x);
							input.setAttribute("listPrimaryKey", primary);
							input.render();
							events.register_event_handler(input.elm, "onclick", function(e){
								var obj = events.get_affected_element(e);
								var instance = window.__list[obj.getAttribute("dom_attr_listinstanceid")];
								loaderOn();
								var data = new ajax();
								data.async = true;
								data.call_back = "window.__list['" + instance.id + "'].actionWeight";
								data.attributes = obj;
								data.file_path = "grid.php";
								data.class_name = "grid";
								data.method_name = "x_updateWeight";
								data.register_argument("object", instance.dbObject);
								data.register_argument("primaryKey", obj.getAttribute("dom_attr_listPrimaryKey"));
								data.register_argument("field", instance.header[obj.getAttribute("dom_attr_listHeaderNum")].getAttribute("field"));
								data.register_argument("value", 1);
								data.send();
								//window.__list[this.id];
								e.preventDefault();
								e.stopPropagation();
								return false;
							});
							
							input = new domElement("input");
							input.parent = div.elm;
							input.elm.type = "button";
							input.elm.value = value;
							input.elm.setAttribute("id", "weight" + primary);
							input.render();
							events.register_event_handler(input.elm, "onclick", function(e){
								alert(1);
								var obj = events.get_affected_element(e);
								var usrFld = domElement("div");
								usrFld.parent = document.getElementsByTagName("body")[0];
								usrFld.elm.style.position = "absolute";
								usrFld.elm.style.top = obj.offsetTop + "px";
								usrFld.elm.style.left = obj.offsetLeft + "px";
								usrFld.elm.style.width = "100px";
								usrFld.elm.style.height = "100px";
								usrFld.elm.style.background = "#f00";
								usrFld.render();
								alert(1);
								e.preventDefault();
								e.stopPropagation();
								return false;
							});
							
							input = new domElement("div");
							input.parent = div.elm;
							input.setCssClass("listWeightIncrease");
							input.setAttribute("listInstanceId", this.id);
							input.setAttribute("listHeaderNum", x);
							input.setAttribute("listPrimaryKey", primary);
							input.render();
							events.register_event_handler(input.elm, "onclick", function(e){
								var obj = events.get_affected_element(e);
								var instance = window.__list[obj.getAttribute("dom_attr_listinstanceid")];
								loaderOn();
								var data = new ajax();
								data.async = true;
								data.call_back = "window.__list['" + instance.id + "'].actionWeight";
								data.attributes = obj;
								data.file_path = "grid.php";
								data.class_name = "grid";
								data.method_name = "x_updateWeight";
								data.register_argument("object", instance.dbObject);
								data.register_argument("primaryKey", obj.getAttribute("dom_attr_listPrimaryKey"));
								data.register_argument("field", instance.header[obj.getAttribute("dom_attr_listHeaderNum")].getAttribute("field"));
								data.register_argument("value", -1);
								data.send();
								//window.__list[this.id];
								e.preventDefault();
								e.stopPropagation();
								return false;
							});
							break;
						default:
							div.text = value;
					}
					div.render();
					z++;
				}
			}
		}
	};
	
	this.buildHeader = function(){
		this.header = new Array();
		this.table = new domElement("table");
		this.table.setCssClass("list");
		this.table.parent = this.parentElm;
		this.table.render();
		this.thead = new domElement("thead");
		this.thead.parent = this.table.elm;
		this.dependancy = null;
		
		this.thead.render();
		var tr = new domElement("tr");
		tr.parent = this.thead.elm;
		tr.render();
		var div;
		var header = this.xmlObject.getElementsByTagName("header")[0].childNodes;
		this.th = new Array();
		for(var z = 0; z < header.length; z++){
			this.header[z] = header[z];
			this.th[z] = new domElement("th");
			if(header[z].hasAttribute("primary")){
				this.th[z].setCssClass("listPrimary");
			}
			if(header[z].hasAttribute("dependencyFrom")){
				this.dependancy = header[z].getAttribute("dependencyFrom");
			}
			this.th[z].parent = tr.elm;
			div = new domElement("div");
			div.parent = this.th[z].elm;
			this.th[z].render();
			div.text = this.header[z].firstChild.nodeValue;
			div.render();
		}
	};
	
	this.resize = function(){
		if(typeof(this.thead) != "undefined" && typeof(this.tbody) != "undefined" && typeof(this.th) != "undefined" && this.th.length > 0){
			this.thead.setStyle("width", this.parentElm.offsetWidth + "px");
			this.tbody.setStyle("width", this.table.elm.offsetWidth + "px");
			this.tbody.setStyle("height", this.parentElm.offsetHeight - this.thead.elm.offsetHeight + "px");
			var width = this.parentElm.offsetWidth / (this.th.length + 1);
//			var firstRow = this.tbody.elm.firstChild.childNodes;
			var rows = this.tbody.elm.childNodes;
			var currentRow;
			for(var i = 0; i < this.th.length; i++){
				this.th[i].elm.firstChild.style.width = width + "px";
			}
			for(var x = 0; x < rows.length; x++){
				currentRow = rows[x].childNodes;
				i = 0;
				for(var z = 0; z < currentRow.length; z++){
					currentRow[z].firstChild.style.width = width + "px";
					i++;
				}
			}
		}
	};
	
	this.actionCheckbox = function(xml, attr){
		loaderOff();
		var result = xml.getElementsByTagName("resultRecord");
		if(result.length > 0){
			if(attr.checked != result[0].firstChild.nodeValue){
				if(result[0].firstChild.nodeValue == 1){
					attr.checked = "checked";
				}else{
					attr.checked = "";
				}
				msg("error update has not been saved!");
			}else{
				msg("saved to DB");
			}
		}else{
			if(+attr.checked == 1){
				attr.checked = "";
			}else{
				attr.checked = "checked";
			}
			msg("error unexpected result in xml");
		}
	};
	
	this.actionWeight = function(xml, attr){
		loaderOff();
		var aRow = xml.getElementsByTagName("aRow");
		var bRow = xml.getElementsByTagName("bRow");
		var aPrimary = xml.getElementsByTagName("aPrimary");
		var bPrimary = xml.getElementsByTagName("bPrimary");
		
		if(aRow.length > 0 && aRow.length > 0){
			var value = aRow[0].firstChild.nodeValue - bRow[0].firstChild.nodeValue;
			document.getElementById("weight" + aPrimary[0].firstChild.nodeValue).value = aRow[0].firstChild.nodeValue;
			document.getElementById("weight" + bPrimary[0].firstChild.nodeValue).value = bRow[0].firstChild.nodeValue;
			var tr = attr.parentNode;
			while(tr.tagName.toLowerCase() != "tr"){
				tr = tr.parentNode;
			}
			var tbody = tr.parentNode;
			var aElm = tbody.rows[tr.rowIndex - 1];
			var bElm = tbody.rows[tr.rowIndex + value - 1];
			if(value > 0){
				tbody.insertBefore (bElm, aElm);
			}else{
				tbody.insertBefore (aElm, bElm);
			}
		}
	};
}
window.onresize = function(){
	for(var z in window.__list){
		window.__list[z].resize();
	}
};