function cmsNavigation(root){

	this.root = root;
	this.caller = null;		//builder
	this.xmlObject = null;
	this.group = Array();
	this.groupItem = new Array();
	this.groupContent = null;
	this.navHeight = 0;
	this.groupOveralHeight = 0;
	this.expandedGroupKey = null;
	this.animationSpead = 200;

	//instance
	window.__cmsNavigation = this;

	builder.registerResize("window.__cmsNavigation", "recalculateNavigation");

	this.render = function(){
		var data = new ajax();
		data.async = true;
		data.call_back = "window.__cmsNavigation.build";
		data.attributes = null;
		data.group = "template";
		data.className = "cmsNavigation";
		data.methodName = "xGetData";
		data.send();
	};

	this.build = function(dataStr){
		var data = JSON.parse(dataStr);
		for(var groupKey in data.navigation.group){
			this.group[groupKey] = new domElement('div');
			this.group[groupKey].parent = this.root.elm;
			this.group[groupKey].setCssClass('navigationGroup');
			this.group[groupKey].setNewText(data.navigation.group[groupKey].name);
			this.group[groupKey].setAttribute('key', groupKey);
			this.group[groupKey].setAttribute('eventCode', 'expandGroup');
			this.group[groupKey].caller = this;
			this.group[groupKey].render();
			this.group[groupKey].setEvent('onclick', 'expandGroup');
			this.groupOveralHeight += this.group[groupKey].elm.clientHeight;
			this.groupOveralHeight += parseInt($(this.group[groupKey].elm).css('margin-top'));
			this.groupOveralHeight += parseInt($(this.group[groupKey].elm).css('margin-bottom'));

			//render items
			this.groupItem[groupKey] = new Array();
			if(typeof(data.navigation.group[groupKey].item) != "undefind"){
				for(var itemKey in data.navigation.group[groupKey].item){
					this.groupItem[groupKey][itemKey] = data.navigation.group[groupKey].item[itemKey];
				}
			}
			this.navHeight = this.root.elm.clientHeight;
			groupKey++;
		}
		if(storage.get('navigationExpanded') != null){
			this.expandGroup(storage.get('navigationExpanded'));
		}
	};

	this.recalculateNavigation = function(){
		this.navHeight = this.root.elm.clientHeight;
		this.groupOveralHeight = 0;
		for(var groupKey = 0; groupKey < this.group.length; groupKey++){
			console.log(this.group[groupKey].elm.clientHeight);
			this.groupOveralHeight += this.group[groupKey].elm.clientHeight;
			this.groupOveralHeight += parseInt($(this.group[groupKey].elm).css('margin-top'));
			this.groupOveralHeight += parseInt($(this.group[groupKey].elm).css('margin-bottom'));
		}
		if(this.groupContent !== null){
			this.groupContent.elm.style.height = (this.navHeight - this.groupOveralHeight) + 'px';
		}
	};

	this.destroyGroupContent = function(){
		window.__cmsNavigationDestroy = this.groupContent;
		$(window.__cmsNavigationDestroy.elm).animate(
			{height:0},
			this.animationSpead,
			function(){
				window.__cmsNavigationDestroy.destruct();
			}
		);
	};

	this.expandGroup = function(groupKey){
		if(this.expandedGroupKey == groupKey){
			this.destroyGroupContent();
			this.expandedGroupKey = null;
		}else{
			if(this.expandedGroupKey !== null){
				this.destroyGroupContent(this.animationSpead);
			}
			this.expandedGroupKey = groupKey;
			this.groupContent = new domElement('div');
			this.groupContent.setCssClass('navigationGroupContent');
			this.groupContent.parent = this.root.elm;
			this.groupContent.insert_before = this.group[groupKey].elm.nextSibling;
			this.groupContent.elm.style.height = '0px';
			this.groupContent.render();
			var item, ex;

			for(var itemKey in this.groupItem[groupKey]){
				item = new domElement('div');
				item.parent = this.groupContent.elm;
				item.caller = this;
				item.setCssClass('navigationItem');
				item.setNewText(this.groupItem[groupKey][itemKey].name);
				item.setAttribute('code', itemKey);
				item.setAttribute('eventCode', 'showView');
				item.setAttribute('groupKey', groupKey);
				item.setAttribute('itemKey', itemKey);
				item.render();
				item.setEvent('onclick', 'showView');
				if(typeof(window.__cmsView) != "undefined" && typeof(window.__cmsView['tab'][itemKey]) != "undefined"){
					ex = new domElement('i');
					ex.parent = item.elm;
					ex.setCssClass('fa fa-exchange');
					ex.render();
				}

			}
			$(this.groupContent.elm).animate({height:(this.navHeight - this.groupOveralHeight)},this.animationSpead);
			storage.set('navigationExpanded', groupKey);
		}
	};

	this.destructExchange = function(code){
		$("[dom_attr_code='" + code + "']").find( "i" ).remove();
	};

	this.handleOutsideEvent = function(obj, e){
		var call = obj.getAttribute('eventCode');
		console.log('event ' + call);
		switch (call) {
			case 'expandGroup':
				this.expandGroup(obj.getAttribute('key'));
				break;
			case 'showView':
				var code = obj.getAttribute('itemKey');
				var view = new cmsView(code);
				view.navigation = obj;
				view.name = this.groupItem[obj.getAttribute('groupKey')][obj.getAttribute('itemKey')].name;
				view.ml = this.groupItem[obj.getAttribute('groupKey')][obj.getAttribute('itemKey')].ml;
				view.container = document.getElementById('cmsContent');
				view.render();
				break;
		};
	};
};