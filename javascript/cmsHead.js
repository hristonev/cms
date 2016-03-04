function cmsHead(root){
	this.root = root;
	this.caller = null;		//builder

	this.render = function(){
		var row = new domElement('div');
		row.parent = this.root.elm;
		row.setCssClass('row');
		row.render();

		var title = new domElement('h2');
		title.parent = row.elm;
		title.setCssClass('cmsTitle');
		title.render();
		$.ajax({
			method: "POST",
			url: "index.php",
			data: {
				"group": "include",
				"className": "base",
				"methodName": "xKwd",
				"argument": {
					"code" : "cmsTitle"
				}
			}
		}).done(function(xml) {
			$(".cmsTitle").text($(xml).find("kwd").text());
		});

		var logout = new domElement('i');
		logout.setCssClass('fa fa-sign-out logout');
		$.ajax({
			method: "POST",
			url: "index.php",
			data: {
				"group": "include",
				"className": "base",
				"methodName": "xKwd",
				"argument": {
					"code" : "logout"
				}
			}
		}).done(function(xml) {
			$(".logout").prop('title', $(xml).find("kwd").text());
		});
		logout.parent = row.elm;
		logout.elm.setAttribute('instance', this.caller.instance_key);
		logout.caller = this.caller;
		logout.setAttribute('eventCode', 'logout');
		logout.render();
		logout.setEvent('onclick', 'logout');

		var logout = new domElement('i');
		logout.setCssClass('fa fa-spinner ajaxLoading');
		logout.elm.setAttribute('id', 'ajaxLoading');
		logout.parent = row.elm;
		logout.render();
	};
}
