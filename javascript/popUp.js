function popUp(code){

	this.containerM = new domElement("div");
	this.containerM.parent = document.getElementsByTagName("body")[0];
	this.containerM.setCssClass("popUpFixedMaster");
	this.containerM.render();

	this.container = new domElement("div");
	this.container.parent = document.getElementsByTagName("body")[0];
	this.container.setCssClass("popUpFixedContainer");
	this.container.render();

	var div = new domElement('div');
	div.setCssClass("popUpClose");
	div.parent = this.container.elm;
	div.render();

	var elm = new domElement('i');
	elm.parent = div.elm;
	elm.setCssClass('fa fa-times');
	elm.setAttribute('eventCode', 'close');
	elm.caller = this;
	elm.render();
	elm.setEvent('onclick', 'close');

	if(typeof(window.__popUp) == "undefined"){
		window.__popUp = new Array();
	}
	window.__popUp[code] = this;
}

popUp.prototype.destruct = function(){
	this.container.destruct();
	this.containerM.destruct();
};

popUp.prototype.handleOutsideEvent = function(obj, e){
	var call = obj.getAttribute('eventCode');
	console.log('call: ' + call);
	switch (call) {
		case 'close':
			this.destruct();
			break;
	}
};