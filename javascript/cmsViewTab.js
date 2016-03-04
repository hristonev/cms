/**
 * Create tab view and handle tab switching
 * @param tabBar {Object} container for tab buttons
 * @param content {Object} container for tab contents
 * @param newTab {Object} created tab
 * @return {Object} content root tag
 * @param code {String}
 * @param close {Bool} false
 */
cmsView.prototype.tab = function(code, close){
	if(typeof(close) == "undefined"){
		close = false;
	}

	if(typeof(window.__cmsView['tabBar']) != "undefined"){
		this.tabBar = window.__cmsView['tabBar'];
		this.content = window.__cmsView['content'];
	}

	if(typeof(this.tabBar) == "undefined"){
		console.log('create new tab bar');
		this.tabBar = new domElement('div');
		this.tabBar.parent = this.container;
		this.tabBar.setCssClass('tabBar');
		this.tabBar.render();
		window.__cmsView['tabBar'] = this.tabBar;

		this.content = new domElement('div');
		this.content.parent = this.container;
		this.content.setCssClass('tabContentContainer');
		this.content.render();
		window.__cmsView['content'] = this.content;

		window.__cmsView['tab'] = new Array();
		window.__cmsView['tabContent'] = new Array();
		window.__cmsView['tabExchange'] = new Array();
	}
	var tab, ex;
	if(!close){
		for(var tabKey in window.__cmsView['tab']){
			window.__cmsView['tab'][tabKey].setCssClass('tab');
			window.__cmsView['tabContent'][tabKey].elm.style.display = "none";
		}
		if(typeof(window.__cmsView['tab'][code]) == "undefined"){
			console.log('new tab ' + code);

			tab = new domElement('div');
			tab.parent = this.tabBar.elm;
			tab.setAttribute('eventCode', 'tabSwitch');
			tab.caller = this;
			tab.setCssClass('tabActive');
			if(!this.recordView){
				tab.setNewText(this.name);
			}else{
				tab.insert_after = window.__cmsView['tab'][this.parent.code].elm;
				tab.setStyle("padding", "2px 4px 2px 2px");
			}
			tab.render();
			window.__cmsView['tab'][code] = tab;
			tab.setEvent('onclick', 'tabSwitch');
			if(!this.recordView){
				ex = new domElement('i');
				ex.parent = tab.elm;
				ex.setAttribute('eventCode', 'tabClose');
				ex.caller = this;
				ex.setCssClass('fa fa-times');
				ex.render();
				ex.setEvent('onclick', 'tabClose');
			}else{
				ex = new domElement('span');
				ex.parent = tab.elm;
				ex.setAttribute('eventCode', 'tabSwitch');
				ex.setNewText(this.recordId);
//				ex.setCssClass('fa fa-plus-square-o');
				ex.caller = this;
				ex.setAttribute('eventCode', 'tabSwitch');
				ex.render();
				ex.setEvent('onclick', 'tabSwitch');
			}

			tabContent = new domElement('div');
			tabContent.parent = this.content.elm;
			tabContent.setCssClass('tabContent');
			tabContent.render();
			window.__cmsView['tabContent'][code] = tabContent;
			this.loadData = true;
			if(!this.recordView){
				ex = new domElement('i');
				ex.parent = this.navigation.elm;
				ex.setCssClass('fa fa-exchange');
				ex.render();
			}

			window.__cmsView['tabExchange'][code] = ex;
		}else{
			window.__cmsView['tab'][code].setCssClass('tabActive');
			window.__cmsView['tabContent'][code].elm.style.display = "";
		}
	}else{
		if(window.__cmsView['tab'][code].elm.className == "tabActive"){
			var codeArr = Object.keys(window.__cmsView['tab']);
			var lastTabCode = codeArr[codeArr.length - 1];
			if(lastTabCode == code && codeArr.length >= 2){
				lastTabCode = codeArr[codeArr.length - 2];
			}
			window.__cmsView['tab'][lastTabCode].setCssClass('tabActive');
			window.__cmsView['tabContent'][lastTabCode].elm.style.display = "";
		}

		window.__cmsView['tab'][code].destructChildren();
		window.__cmsView['tab'][code].destruct();
		window.__cmsView['tabContent'][code].destructChildren();
		window.__cmsView['tabContent'][code].destruct();
		window.__cmsNavigation.destructExchange(code);
		delete window.__cmsView['tab'][code];
		delete window.__cmsView['tabContent'][code];
		delete window.__cmsView['tabExchange'][code];

	}

	return tabContent;
};