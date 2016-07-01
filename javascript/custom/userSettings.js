function userSettings(){

	if(typeof(window.__userSettings) == "undefined"){
		window.__userSettings = new Array();
		window.__userSettings['instance'] = new Array();
	}

	this.instanceKey = window.__userSettings['instance'].length;
	window.__userSettings[this.instanceKey] = this;

	this.data = null;
	this.caller = null;
	this.rootElm = null;
	this.accountSettings = new Array();
	this.forms = new Array();

	this.render = function(){
		this.rootElm = this.caller.root.elm;

		this.container = new domElement("div");
		this.container.setCssClass("userSettings");
		this.container.parent = this.rootElm;
		this.container.render();
		if(this.data.user.valid){
			var username = new domElement("h2");
			username.parent = this.container.elm;
			username.setNewText(this.data.user.name);
			username.render();

			this.renderSettings();
		}
	};

	this.renderSettings = function(){
		var self = this;

		//account settings
		this.settings = new domElement("a");
		this.settings.parent = this.container.elm;
		this.settings.setNewText(this.data.user.accountSettings);
		this.settings.render();
		this.settings.elm.onclick = function(){
			if(self.sc.elm.style.display == "none"){
				self.sc.elm.style.display = "";
			}else{
				self.sc.elm.style.display = "none";
			}
		};
		var formKey = this.forms.length;
		this.forms[formKey] = new Array(
			"currentPassword"
			, "newPassword"
			, "newPasswordRepeat"
			, "changeUsername"
		);
		this.sc = new domElement("div");
		this.sc.parent = this.container.elm;
		this.sc.setStyle("display", "none");
		this.sc.setCssClass("userSettingsContainer");
		this.sc.render();
		this.renderSettingsRow(this.forms[formKey][0], "password", this.sc.elm);
		this.renderSettingsRow(this.forms[formKey][1], "password", this.sc.elm);
		this.renderSettingsRow(this.forms[formKey][2], "password", this.sc.elm);
		this.renderSettingsRow(this.forms[formKey][3], "text", this.sc.elm);
		this.scInfo = new domElement('span');
		this.scInfo.parent = this.sc.elm;
		this.scInfo.render();
		this.scSave = new domElement('input');
		this.scSave.elm.type = "button";
		this.scSave.parent = this.sc.elm;
		this.scSave.setCssClass("userSettingsSaveButton");
		this.scSave.setValue(this.data.user.save);
		this.scSave.render();
		this.scSave.elm.onclick = function(){
			var formData = {};
			var formElements = self.sc.elm.getElementsByTagName("input");
			for(var x = 0; x < formElements.length; x++){
				for(var i = 0; i < self.forms[formKey].length; i++){
					if(formElements[x].name == self.forms[formKey][i]){
						formData[formElements[x].name] = formElements[x].value;
					}
				}
			}
			var data = new ajax();
			data.async = true;
			data.call_back = "window.__userSettings[" + self.instanceKey + "].accountSaveBack";
			data.attributes = null;
			data.group = "template";
			data.className = "userSettings";
			data.methodName = "xChangeAccount";
			data.register_argument("formData", JSON.stringify(formData));
			data.send();
		};

		//add user
		this.addUser = new domElement("a");
		this.addUser.parent = this.container.elm;
		this.addUser.setNewText(this.data.user.addUser);
		this.addUser.render();
		this.addUser.elm.onclick = function(){
			if(self.auc.elm.style.display == "none"){
				self.auc.elm.style.display = "";
			}else{
				self.auc.elm.style.display = "none";
			}
		};
		var formKey = this.forms.length;
		this.forms[formKey] = new Array(
			"username"
			, "password"
			, "cmsAccess"
		);
		this.auc = new domElement("div");
		this.auc.parent = this.container.elm;
		this.auc.setStyle("display", "none");
		this.auc.setCssClass("userSettingsContainer");
		this.auc.render();
		this.renderSettingsRow(this.forms[formKey][0], "text", this.auc.elm);
		this.renderSettingsRow(this.forms[formKey][1], "text", this.auc.elm);
		this.renderSettingsRow(this.forms[formKey][2], "radio", this.auc.elm);
		this.aucInfo = new domElement('span');
		this.aucInfo.parent = this.auc.elm;
		this.aucInfo.render();
		this.aucSave = new domElement('input');
		this.aucSave.elm.type = "button";
		this.aucSave.parent = this.auc.elm;
		this.aucSave.setCssClass("userSettingsSaveButton");
		this.aucSave.setValue(this.data.user.save);
		this.aucSave.render();
		this.aucSave.elm.onclick = function(){
			var formData = {};
			var formElements = self.auc.elm.getElementsByTagName("input");
			for(var x = 0; x < formElements.length; x++){
				for(var i = 0; i < self.forms[formKey].length; i++){
					if(formElements[x].name == self.forms[formKey][i]){
						if(formElements[x].type != "radio" || formElements[x].checked == true){
							formData[formElements[x].name] = formElements[x].value;
						}
					}
				}
			}
			var data = new ajax();
			data.async = true;
			data.call_back = "window.__userSettings[" + self.instanceKey + "].accountAddBack";
			data.attributes = null;
			data.group = "template";
			data.className = "userSettings";
			data.methodName = "xAddAccount";
			data.register_argument("formData", JSON.stringify(formData));
			data.send();
		};
	};

	this.accountSaveBack = function(dataStr){
		var data = JSON.parse(dataStr);
		var elm;
		this.scInfo.destructChildren();
		for(var i = 0; i < data.error.length; i++){
			elm = new domElement("div");
			elm.parent = this.scInfo.elm;
			elm.setCssClass("userSettingsError");
			elm.setNewText(data.error[i]);
			elm.render();
		}
		for(var i = 0; i < data.info.length; i++){
			elm = new domElement("div");
			elm.parent = this.scInfo.elm;
			elm.setCssClass("userSettingsInfo");
			elm.setNewText(data.info[i]);
			elm.render();
		}
	};

	this.accountAddBack = function(dataStr){
		var data = JSON.parse(dataStr);
		var elm;
		this.aucInfo.destructChildren();
		for(var i = 0; i < data.error.length; i++){
			elm = new domElement("div");
			elm.parent = this.aucInfo.elm;
			elm.setCssClass("userSettingsError");
			elm.setNewText(data.error[i]);
			elm.render();
		}
		for(var i = 0; i < data.info.length; i++){
			elm = new domElement("div");
			elm.parent = this.aucInfo.elm;
			elm.setCssClass("userSettingsInfo");
			elm.setNewText(data.info[i]);
			elm.render();
		}
	};

	this.renderSettingsRow = function(code, type, container){
		this.accountSettings[code] = {};

		this.accountSettings[code].row = new domElement("div");
		this.accountSettings[code].row.setCssClass("userSettingsRow");
		this.accountSettings[code].row.parent = container;
		this.accountSettings[code].row.render();

		this.accountSettings[code].text = new domElement('span');
		this.accountSettings[code].text.parent = this.accountSettings[code].row.elm;
		this.accountSettings[code].text.setNewText(this.data.user[code]);
		this.accountSettings[code].text.render();

		if(type == "radio"){
			this.accountSettings[code].field = new domElement('input');
			this.accountSettings[code].field.parent = this.accountSettings[code].row.elm;
			this.accountSettings[code].field.elm.type = "radio";
			this.accountSettings[code].field.elm.name = code;
			this.accountSettings[code].field.elm.value = 0;
			this.accountSettings[code].field.render();
			var radioBtn = new radio(this.accountSettings[code].field.elm);
			radioBtn.data = new Array(0,1);
			radioBtn.init();

		}else{
			this.accountSettings[code].field = new domElement('input');
			this.accountSettings[code].field.parent = this.accountSettings[code].row.elm;
			this.accountSettings[code].field.elm.type = type;
			this.accountSettings[code].field.elm.name = code;
			this.accountSettings[code].field.render();
		}
	}

}