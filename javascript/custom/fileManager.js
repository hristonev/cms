function fileManager(){

	if(typeof(window.__fileManager) == "undefined"){
		window.__fileManager = new Array();
		window.__fileManager['instance'] = new Array();
	}

	this.instanceKey = window.__fileManager['instance'].length;
	window.__fileManager[this.instanceKey] = this;

	this.data = null;
	this.caller = null;
	this.rootElm = null;
	this.fileContainer = null;
	this.uploadContainer = null;
	this.files = new Array();

	this.lang = null;
	this.property = null;
	this.mime = null;

	this.generatedProperty = new Array();
	this.generatedAddIcon = new Array();
	this.generatorElm = new Array();

	this.render = function(){
		this.rootElm = this.caller.root.elm;

		var table = new domElement("table");
		table.setStyle("width", "100%");
		table.parent = this.rootElm;
		table.render();

		var tr = new domElement("tr");
		tr.parent = table.elm;
		tr.render();

		this.manager = new domElement("td");
		this.manager.setCssClass("fileManager");
		this.manager.parent = tr.elm;
		this.manager.render();

		this.uploader = new domElement("td");
		this.uploader.setCssClass("fileUploader");
		this.uploader.parent = tr.elm;
		this.uploader.render();

		this.managerSection();
		this.uploadSection();
	};

	this.managerSection = function(){
		var groupButton;
		this.groupBtnContainer = new domElement("div");
		this.groupBtnContainer.parent = this.manager.elm;
		this.groupBtnContainer.render();
		if(typeof(this.data.fm) != "undefined" && typeof(this.data.fm.group) != "undefined"){
			for(key in this.data.fm.group){
				groupButton = new domElement("div");
				groupButton.parent = this.groupBtnContainer.elm;
				groupButton.caller = this;
				groupButton.setCssClass("fileManagerGroup");
				groupButton.setNewText(this.data.fm.group[key] + " (" + this.data.fm.groupCount[key] + ")");
				groupButton.setAttribute('eventCode', 'groupSelect');
				groupButton.setAttribute('group', key);
				groupButton.render();
				groupButton.setEvent('onclick', 'groupSelect');
			}
		}
	};

	this.groupSelect = function(group){
		var data = new ajax();
		data.async = true;
		data.call_back = "window.__fileManager[" + this.instanceKey + "].groupRender";
		data.attributes = null;
		data.group = "template";
		data.className = "fileManager";
		data.methodName = "xGetGroup";
		data.register_argument("group", group);
		data.send();
	};

	this.resize = function(){
		if(this.fileContainer != null){
			this.fileContainer.elm.style.height = (this.rootElm.offsetHeight - this.groupBtnContainer.elm.offsetHeight - 4) + "px";
		}
		if(this.uploadContainer != null){
			this.uploadContainer.elm.style.height = (this.rootElm.offsetHeight - this.groupBtnContainer.elm.offsetHeight - 4) + "px";
		}
	};

	this.itemPropertyRender = function(data, container, insertBefore){
		if(typeof(data.id) != "undefined"){
			var property = new domElement("div");
			property.parent = container;
			if(typeof(insertBefore) != "undefined"){
				property.insert_before = insertBefore;
			}
			property.setCssClass("fileManagerItemPropertyList");
			property.render();

			var text = new domElement("span");
			text.parent = property.elm;
			text.setNewText(data.lang);
			text.render();

			var text = new domElement("span");
			text.parent = property.elm;
			text.setNewText(data.property);
			text.render();

			var text = new domElement("h5");
			text.parent = property.elm;
			text.setNewText(data.value);
			text.render();

			var del = new domElement('i');
			del.parent = property.elm;
			del.setAttribute('eventCode', 'propertyDelete');
			del.setAttribute('id', data.id);
			del.caller = this;
			del.setCssClass('fa fa-times');
			del.render();
			del.setEvent('onclick', 'propertyDelete');
		}else{
			console.log("incorrect property data");
		}
	};

	this.propertyDelete = function(obj){
		var data = new ajax();
		data.async = true;
		data.call_back = "window.__fileManager[" + this.instanceKey + "].propertyDeleteBack";
		data.attributes = obj;
		data.group = "template";
		data.className = "fileManager";
		data.methodName = "xRemoveProperty";
		data.register_argument("id", obj.getAttribute("id"));
		data.send();
	};

	this.propertyDeleteBack = function(dataStr, obj){
		data = JSON.parse(dataStr);
		if(data.success == 1){
			var parent = new domElement(obj.parent.getAttribute("domelement_instance"));
			obj.destruct();
			parent.destruct();
		}
	}

	this.groupRender = function(dataStr){
		data = JSON.parse(dataStr);

		var i,x,fileContainer,file,name,icon;

		if(typeof(data.lang) != "undefined" && typeof(data.property) != "undefined" && typeof(data.mime) != "undefined"){
			if(this.lang == null){
				this.lang = data.lang;
			}
			if(this.property == null){
				this.property = data.property;
			}
			if(this.mime == null){
				this.mime = data.mime;
			}
		}else{
			console.log("error in received data lang,property,mime");
		}
		if(typeof(data.file) != "undefined"){
			if(this.fileContainer != null){
				this.fileContainer.destruct();
			}
			this.fileContainer = new domElement("div");
			this.fileContainer.parent = this.manager.elm;
			this.fileContainer.setCssClass("fileManagerItemContainer");
			this.fileContainer.render();

			this.resize();

			builder.registerResize("window.__fileManager[" + this.instanceKey + "]", "resize");

			for(i in data.file){

				name = new domElement("div");
				name.parent = this.fileContainer.elm;
				name.setCssClass("fileManagerItemName");
				name.setNewText(data.file[i].originalName + " (" + this.bytesFormat(data.file[i].size) + ") " + data.file[i].hash);
				name.render();

				//header name and info
				file = new domElement("div");
				file.parent = this.fileContainer.elm;
				file.setCssClass("fileManagerItem");
				file.render();

				//icon
				if(data.file[i].mimeCheck.match(/image/g)){
					icon = new domElement("IMG");
					icon.parent = file.elm;
					icon.setCssClass("fileManagerItemIcon");
					icon.setSrc("?type=image&id=" + data.file[i].id + "&size=300");
					icon.render();
				}else{
					icon = new domElement("div");
					icon.parent = file.elm;
					icon.setCssClass("fileManagerItemIcon iconFont");
					if(typeof(data.file[i].icon) != "undefined" && data.file[i].icon != ""){
						icon.setNewText(String.fromCharCode("0x" + data.file[i].icon));
					}else{
						icon.setNewText("\uf15b");
					}
					icon.render();
				}

				//properties
				//add new property
				var opt;
				var property = new domElement("div");
				property.setAttribute("file", i);
				property.parent = file.elm;
				property.setCssClass("fileManagerItemProperty");
				property.render();

				var langNP = new domElement("select");
				langNP.setName('lang');
				langNP.parent = property.elm;
				langNP.caller = this;
				langNP.render();
				for(x in data.lang){
					opt = new domElement("option");
					opt.parent = langNP.elm;
					opt.setNewText(data.lang[x].value);
					opt.setValue(data.lang[x].id);
					opt.render();
				}

				var selectNP = new domElement("select");
				selectNP.setName('property');
				selectNP.parent = property.elm;
				selectNP.caller = this;
				selectNP.setAttribute('eventCode', 'propertySelect');
				selectNP.render();
				selectNP.setEvent('onchange', 'propertySelect');
				for(x in data.property){
					opt = new domElement("option");
					opt.parent = selectNP.elm;
					opt.setNewText(data.property[x].value);
					opt.setValue(data.property[x].id);
					opt.render();
				}
				//render properties
				if(typeof(data.file[i].property) != "undefined" && data.file[i].property.length > 0){
					for(x in data.file[i].property){
						this.itemPropertyRender(data.file[i].property[x], file.elm);
					}
				}
				var clear = new domElement("div");
				clear.setAttribute("file", i);
				clear.parent = file.elm;
				clear.setStyle("clear", "both");
				clear.render();
			}
		}else{
			console.log("error in received data file");
		}
		this.data = data;
	};

	this.onPropertySelect = function(obj){
		var prop, opt, x;
		var file = obj.parent.getAttribute("dom_attr_file");
		for(var i in this.data.property){
			if(this.data.property[i].id == obj.getValue()){
				if(typeof(this.generatedProperty[file]) != "undefined" && this.generatedProperty[file] != null){
					this.generatedProperty[file].destruct();
					this.generatedAddIcon[file].destruct();
					this.generatedProperty[file] = null;
				}
				if(parseInt(obj.getValue()) > 0){
					if(typeof(this.data.property[i].object) == "undefined"){
						prop = new domElement("input");
						prop.setName('value');
						prop.parent = obj.parent;
						prop.render();
						prop.elm.focus();
					}else if(typeof(this.data.property[i].row) != "undefined"){
						prop = new domElement("select");
						prop.setName('value');
						prop.parent = obj.parent;
						prop.render();

						for(x in this.data.property[i].row){
							opt = new domElement("option");
							opt.parent = prop.elm;
							opt.setNewText(this.data.property[i].row[x].value);
							opt.setValue(this.data.property[i].row[x].id);
							opt.render();
						}
					}
					var addBtn = new domElement("i");
					addBtn.parent = obj.parent;
					addBtn.caller = this;
					addBtn.setCssClass("fa fa-plus");
					addBtn.setAttribute('eventCode', 'addProperty');
					addBtn.render();
					addBtn.setEvent('onclick', 'addProperty');

					this.generatedAddIcon[file] = addBtn;
					this.generatedProperty[file] = prop;
					this.generatorElm[file] = obj;
				}
			}
		}
	};

	this.addProperty = function(obj){
		var container = obj.parent;
		var dataObj = new Array();
		var file = container.getAttribute("dom_attr_file");
		var ajaxAttr = {};
		ajaxAttr.data = {};
		ajaxAttr.obj = container.parentNode;
		ajaxAttr.file = file;
		var index;

		key = dataObj.length;
		dataObj[key] = {};
		dataObj[key].name = "file";
		dataObj[key].value = this.data.file[file].id;

		for(var i = 0; i < container.childNodes.length; i++){
			if(typeof(container.childNodes[i].value) != "undefined"){
				key = dataObj.length;
				dataObj[key] = {};
				dataObj[key].name = container.childNodes[i].getAttribute("name");
				dataObj[key].value = container.childNodes[i].value;
				switch(container.childNodes[i].getAttribute("name")){
					case "lang":
						if(container.childNodes[i].tagName == "SELECT"){
							index = container.childNodes[i].selectedIndex;
							ajaxAttr.data.lang = container.childNodes[i].item(index).text;
						}else{
							ajaxAttr.data.lang = container.childNodes[i].value;
						}
						break;
					case "property":
						if(container.childNodes[i].tagName == "SELECT"){
							index = container.childNodes[i].selectedIndex;
							ajaxAttr.data.property = container.childNodes[i].item(index).text;
						}else{
							ajaxAttr.data.property = container.childNodes[i].value;
						}
						break;
					case "value":
						if(container.childNodes[i].tagName == "SELECT"){
							index = container.childNodes[i].selectedIndex;
							ajaxAttr.data.value = container.childNodes[i].item(index).text;
						}else{
							ajaxAttr.data.value = container.childNodes[i].value;
						}
						break;
				}
			}
		}

		var data = new ajax();
		data.async = true;
		data.call_back = "window.__fileManager[" + this.instanceKey + "].addPropertyBack";
		data.attributes = ajaxAttr;
		data.group = "template";
		data.className = "fileManager";
		data.methodName = "xAddProperty";
		data.register_argument("data", JSON.stringify(dataObj));
		data.send();

	};

	this.addPropertyBack = function(dataStr, attr){
		data = JSON.parse(dataStr);
		attr.data.id = data.id;

		this.itemPropertyRender(attr.data, attr.obj, attr.obj.childNodes[attr.obj.childNodes.length - 1]);

		this.generatedProperty[attr.file].destruct();
		this.generatedAddIcon[attr.file].destruct();
		this.generatorElm[attr.file].elm.options[0].selected = true;
		this.generatedProperty[attr.file] = null;
	};

	this.uploadSection = function(){
		this.uploadContainer = new domElement('div');
		this.uploadContainer.parent = this.uploader.elm;
		this.uploadContainer.setStyle("overflowY", "scroll");
		this.uploadContainer.render();

		this.uploadBtn = new domElement("input");
		this.uploadBtn.parent = this.uploadContainer.elm;
		this.uploadBtn.elm.setAttribute("type", "file");
		this.uploadBtn.elm.setAttribute("multiple", true);
		this.uploadBtn.setAttribute('eventCode', 'fileSelect');
		this.uploadBtn.caller = this;
		this.uploadBtn.render();
		this.uploadBtn.setEvent('onchange', 'fileSelect');
		this.resize();
	};

	this.handleFileSelect = function(){
		var key, progress, bar;
		for(var i = 0; i < this.uploadBtn.elm.files.length; i++){
			key = this.files.length;
			this.files[key] = new FormData();
			this.files[key].append("file", this.uploadBtn.elm.files[i]);
			progress = new domElement("div");
			progress.setStyle("position", "relative");
			progress.setNewText(this.uploadBtn.elm.files[i].name + " " + this.bytesFormat(this.uploadBtn.elm.files[i].size));
			progress.parent = this.uploadContainer.elm;
			progress.insert_after = this.uploadBtn.elm;
			progress.render();
			bar = new domElement("div");
			bar.parent = progress.elm;
			bar.elm.setAttribute("id", "bar" + key);
			bar.setStyle("height", "2px");
			bar.setStyle("width", "0px");
			bar.setStyle("background", "#00ff00");
			bar.setStyle("position", "absolute");
			bar.setStyle("bottom", "20px");
			bar.render();
		}
		this.resize();
		//this.upload();
	};

	this.bytesFormat = function (size) {
	    var prefix = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
	    var i = parseInt(Math.floor(Math.log(size) / Math.log(1024)));
	    if (i == 0) return size + prefix[i];
	    return (size / Math.pow(1024, i)).toFixed(1) + prefix[i];
	};

	this.upload = function(){
		for(var i = 0; i < this.files.length; i++){
			var data = new ajax();
			data.baseURL = "upload.php";
			data.progressCall = "document.getElementById('bar" + i + "').style.width = progress + '%';";
			data.setHeaders = false;
			data.async = true;
			//data.call_back = "window.__fileManager['instance'][" + this.instanceKey + "].build";
			data.attributes = i;
			data.group = "template";
			data.className = "fileManager";
			data.methodName = "xUpload";
			data.send(this.files[i]);
		}
	};

	this.handleOutsideEvent = function(obj, e){
		var call = obj.getAttribute("eventCode");
		switch (call) {
			case 'fileSelect':
				this.handleFileSelect();
				break;
			case 'groupSelect':
				this.groupSelect(obj.getAttribute("group"));
				break;
			case 'propertySelect':
				this.onPropertySelect(obj);
				break;
			case 'addProperty':
				this.addProperty(obj);
				break;
			case 'propertyDelete':
				this.propertyDelete(obj);
				break;
		}
	};
}