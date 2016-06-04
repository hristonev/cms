function log(value){
	var container = document.getElementById("logContainer");
	if(container === null){
		container = document.createElement("div");
		container.id = "logContainer";
		container.className = "logContainer";
		document.getElementById("cmsFooter").appendChild(container);
		events.registerEventHandler(container, "onclick", function(e){
			obj = events.getAffectedElement(e);
			if(obj.className == "logContainer"){
				obj.className = "logContainerExpand";
			}else{
				obj.className = "logContainer";
			}
		});
	}
	var txtNode = document.createTextNode("[" + new Date().toLocaleDateString() + " " + new Date().toLocaleTimeString() + "] " + value);
	container.appendChild(txtNode);
	var brNode = document.createElement("br");
	container.appendChild(brNode);
	container.scrollTop = container.scrollHeight;
};