var events = {};

events.registerEventHandler = function(obj, name, event_code, bubling){

	if(typeof(window.__events) == "undefined"){
		window.__events = new Array();
	}
	var key = window.__events.length;
	obj.setAttribute("events_key", obj.getAttribute("events_key") + "," + key);
	window.__events[key] = new Array();
	window.__events[key]["name"] = name;
	window.__events[key]["event_code"] = event_code;
	window.__events[key]["bubling"] = bubling;

	/* *** COMMENT
	// ----------------------------------------------
	COMMENT *** */

	if(typeof(bubling) == "undefined"){
		bubling = false;
	}
	if(document.all){
		obj.attachEvent(name, event_code);//element.detachevent('onclick',spyOnUser)
	}else{
		name = name.substr(2, (name.length - 2));
//		/alert(obj.tagName);
		obj.addEventListener(name, event_code, bubling);
	}

};

events.removeEvents = function(obj){
	if(obj.tagName){
		var reg_string = obj.getAttribute("events_key");
		if(reg_string){
			var registered_events = reg_string.split(",");
			for(var key in registered_events){
				if(registered_events[key] != "null"){
					events.removeEventHandler(obj, window.__events[registered_events[key]]["name"], window.__events[registered_events[key]]["event_code"], window.__events[registered_events[key]]["bubling"]);
				}
			}
		}
		obj.removeAttribute("events_key");
	}
};

events.removeEventHandler = function(obj, name, event_code, bubling){
	if(typeof(bubling) == "undefined"){
		bubling = false;
	}
	if(document.all){
		obj.detachEvent(name, event_code);
	}else{
		name = name.substr(2, (name.length - 2));
		obj.removeEventListener(name, event_code, bubling);
	}
};

events.getAffectedElement = function(e){
	if(typeof(e.srcElement) == "undefined"){
		obj = e.target;
	}else{
		obj = e.srcElement;
	}
	return obj;
};

events.stop = function(e){
	if (e.preventDefault){
		e.preventDefault();
		e.stopPropagation();
	}else{
		e.returnValue = false;
		e.cancelBubble = true;
	}
};
