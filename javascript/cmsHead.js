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
		}).done(function(dataStr) {
			var data = JSON.parse(dataStr);
			$(".cmsTitle").text(data.value);
		});

		icon = new domElement('i');
		icon.setCssClass('fa fa-sitemap');
		icon.caller = this;
		icon.setAttribute('eventCode', 'siteMap');
		icon.parent = row.elm;
		icon.render();
		icon.setEvent('onclick', 'siteMap');

		icon = new domElement('i');
		icon.setCssClass('fa fa-sign-out logout');
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
		}).done(function(dataStr) {
			var data = JSON.parse(dataStr);
			$(".logout").prop('title', data.value);
		});

		icon.parent = row.elm;
		icon.elm.setAttribute('instance', this.caller.instance_key);
		icon.caller = this.caller;
		icon.setAttribute('eventCode', 'logout');
		icon.render();
		icon.setEvent('onclick', 'logout');

		icon = new domElement('i');
		icon.setCssClass('fa fa-spinner ajaxLoading');
		icon.elm.setAttribute('id', 'ajaxLoading');
		icon.parent = row.elm;
		icon.render();

	};

	this.handleOutsideEvent = function(obj, e){
		var call = obj.getAttribute('eventCode');
		console.log('call: ' + call);
		switch (call) {
			case 'siteMap':
				var obj = new cmsSiteMap();
				obj.render();
				break;
		};
	};
}