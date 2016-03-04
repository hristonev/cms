var ajax = function(){
	this.method = 'POST';
	this.url = document.URL;
	this.async = true;
	this.call_back = '';
	this.group = '';
	this.className = '';
	this.methodName = '';
	this.argument = '';
	this.attributes = null;
	this.header = "content-type: text/xml";
	this.is_xml = true;
	this.baseURL = "index.php";
	if(typeof(window.__base["ajax"]) == "undefined"){
		window.__base["ajax"] = 0;
	}
	if(typeof(window.__base["ajax_request"]) == "undefined"){
		window.__base["ajax_request"] = new Array();
		window.__base["ajax_request_arg"] = new Array();
	}

	this.register_argument = function(name, value){
		this.argument += '&argument[' + name + ']=' + value;
	};

	this.get_url = function(){
		var url = "?ajax=1";
		url += "&group=" + this.group;
		url += "&className=" + this.className;
		url += "&methodName=" + this.methodName;
		url += this.argument;

		return url;
	};

	this.send = function(){
		var loading = document.getElementById("ajaxLoading");
		if(loading !== null){
			loading.className = "fa fa-spinner ajaxLoading spin";
		}
		window.__base["ajax"] += 1;

		var xml_doc = null;
		this.argument += '&argument[header]=' + this.header;

		var xml_http;
		var ie = false;
		try{ // Firefox, Opera 8.0+, Safari
			xml_http = new XMLHttpRequest();
			xml_http.overrideMimeType('text/xml');
		}catch (trymicrosoft){// Internet Explorer e
			try{
				xml_http = new ActiveXObject("Msxml2.XMLHTTP");
				ie = true;
			}catch (othermicrosoft){ // e
				try{
					xml_http = new ActiveXObject("Microsoft.XMLHTTP");
					ie = true;
				}
				catch (failed){
					alert("AJAX not possible!");
					return false;
				}
			}

		}

		var url = this.baseURL;
		this.argument += "&group=" + this.group + "&className=" + this.className + "&methodName=" + this.methodName;
		var call_back = this.call_back;
		var attributes = this.attributes;

		xml_http.onreadystatechange = function(){
			if (xml_http.readyState == 4 && xml_http.status == 200){
				window.__base["ajax"] -= 1;
				var loading = document.getElementById("ajaxLoading");
				if(loading !== null){
					loading.className = "fa fa-spinner ajaxLoading";
				}
				if(call_back != ""){
					if(this.group == "xml"){
						if(document.all){
							xml_doc = new ActiveXObject('Microsoft.XMLDOM');
							xml_doc.loadXML(xml_http.responseText);
						}else{
							xml_doc = xml_http.responseXML;
						}
					}else{
						xml_doc = xml_http.responseText;
					}
					eval(call_back + "(xml_doc, attributes);");
				}
			}
		};
		if(ie){

		}
		xml_http.open(this.method, url, this.async);
		xml_http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xml_http.setRequestHeader("Content-length", this.argument.length);
		xml_http.setRequestHeader("Connection", "close");
		xml_http.send(this.argument);

		if(!this.async){
			window.__base["ajax"] -= 1;
			var loading = document.getElementById("ajaxLoading");
			if(loading !== null){
				loading.className = "fa fa-spinner ajaxLoading";
			}
			if(this.is_xml){
				if(document.all){
					xml_doc = new ActiveXObject('Microsoft.XMLDOM');
					xml_doc.loadXML(xml_http.responseText);
				}else{
					xml_doc = xml_http.responseXML;
				}
			}else{
				xml_doc = xml_http.responseText;
			}
		}

		return xml_doc;
	};
};