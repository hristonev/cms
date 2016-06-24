Number.prototype.pad = function(size) {
	var s = String(this);
	while (s.length < (size || 2)) {s = "0" + s;}
	return s;
}

function calendar(elment, caller, saveMethod, hasTime){
	this.elm = elment;
	this.caller = caller;
	this.save = saveMethod;
	if(typeof(hasTime) == "undefined"){
		this.hasTime = false;
	}else{
		this.hasTime = hasTime;
	}

	this.months = new Array("Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември");

	this.daySize = 22;

	this.day = new Array();

	this.elm.caller = this;
	this.elm.setAttribute('eventCode', 'calendar');
	this.elm.setEvent('onclick', 'calendar');

	this.container = new domElement("div");
	this.container.setCssClass("calendarContainer");
	this.container.setStyle("marginTop", this.elm.elm.offsetHeight + "px");
	this.container.parent = this.elm.elm.parentNode;
	this.container.insert_before = this.elm.elm;
	this.container.render();
	this.container.setStyle("width", ((this.daySize + 4) * 7) + "px");

	this.currentDate = new Date();

	var i, day;

	//decrease controll 1x day
	control = new domElement("i");
	control.setCssClass("fa fa-angle-double-left calendarControll");
	control.setStyle("width", (this.daySize + 4) + "px");
	control.parent = this.container.elm;
	control.caller = this;
	control.render();
	control.setAttribute('control', '-1');
	control.setAttribute('eventCode', 'dateControl');
	control.setEvent('onclick', 'dateControl');
	//month width is 3x day
	this.month = new domElement("div");
	this.month.setCssClass("calendarMonth");
	this.month.setStyle("width", ((this.daySize + 4) * 3) + "px");
	this.month.parent = this.container.elm;
	this.month.setNewText(this.months[this.currentDate.getMonth()]);
	this.month.render();
	//year width is 2x day
	this.year = new domElement("div");
	this.year.setCssClass("calendarYear");
	this.year.setStyle("width", ((this.daySize + 4) * 2) + "px");
	this.year.parent = this.container.elm;
	this.year.setNewText(this.currentDate.getFullYear());
	this.year.render();
	//increase controll 1x day
	control = new domElement("i");
	control.setCssClass("fa fa-angle-double-right calendarControll");
	control.setStyle("width", (this.daySize + 4) + "px");
	control.parent = this.container.elm;
	control.caller = this;
	control.render();
	control.setAttribute('control', '1');
	control.setAttribute('eventCode', 'dateControl');
	control.setEvent('onclick', 'dateControl');

	for(i = 1; i <= 42; i++){
		this.day[i] = new domElement("div");
		this.day[i].setCssClass("calendarDay");
		this.day[i].setStyle("width", this.daySize + "px");
		this.day[i].setStyle("height", this.daySize + "px");
		this.day[i].parent = this.container.elm;
		this.day[i].setNewText(0);
		this.day[i].caller = this;
		this.day[i].render();
		this.day[i].setAttribute('eventCode', 'setDate');
		this.day[i].setEvent('onclick', 'setDate');
	}

	this.setDays = function(){
		var dayCounter = 1, i, month, year;
		//set important data
		var lastDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
		this.lastDay = lastDay.getDate();

		var firstDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
		this.firstDay = firstDay.getDay();
		if(this.firstDay == 0){
			this.firstDay = 7;
		}
		var prevMonthLastDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 0);
		year = parseInt(this.currentDate.getFullYear());
		month = parseInt(this.currentDate.getMonth()) + 1;
		month--;
		if(month == 0){
			year--;
			month = 12;
		}
		//set previous month
		for(i = 1; i < this.firstDay; i++){
			this.day[dayCounter].elm.firstChild.nodeValue = prevMonthLastDay.getDate() - this.firstDay + i + 1;
			this.day[dayCounter].setCssClass("calendarDay calendarDisable");
			this.day[dayCounter].setAttribute("date",
					year
					+ "-"
					+ month.pad(2)
					+ "-"
					+ (prevMonthLastDay.getDate() - this.firstDay + i + 1).pad(2)
					);
			dayCounter++;
		}
		//set current month
		month++;
		if(month > 12){
			year++;
			month = 1;
		}
		for(i = 1; i <= this.lastDay; i++){
			this.day[dayCounter].elm.firstChild.nodeValue = i;
			this.day[dayCounter].setCssClass("calendarDay");
			this.day[dayCounter].setAttribute("date",
					year
					+ "-"
					+ month.pad(2)
					+ "-"
					+ i.pad(2)
					);
			dayCounter++;
		}

		//set next month
		month++;
		if(month > 12){
			year++;
			month = 1;
		}
		var nextMonthDay = 1;
		for(i = dayCounter; i <= 42; i++){
			this.day[dayCounter].elm.firstChild.nodeValue = nextMonthDay;
			this.day[dayCounter].setCssClass("calendarDay calendarDisable");
			this.day[dayCounter].setAttribute("date",
					year
					+ "-"
					+ month.pad(2)
					+ "-"
					+ nextMonthDay.pad(2)
					);
			dayCounter++;
			nextMonthDay++;
		}
	};

	this.show = function(){
		this.container.setStyle("display", "block");
	};

	this.hide = function(){
		this.container.setStyle("display", "none");
	};

	this.handleOutsideEvent = function(obj, e){
		var call = obj.getAttribute("eventCode");
		if(call != "scroll"){
			console.log('event ' + call);
		}
		switch (call) {
			case 'calendar':
				this.show();
				break;
			case "dateControl":
				var control = parseInt(obj.getAttribute("control"));
				var month = parseInt(this.currentDate.getMonth() + control);
				var year = parseInt(this.currentDate.getFullYear());
				if(month < 0){
					month = 11;
					year--;
				}
				if(month > 11){
					month = 0;
					year++;
				}
				this.month.elm.firstChild.nodeValue = this.months[month];
				this.year.elm.firstChild.nodeValue = year;
				this.currentDate = new Date(year, month, 1);
				this.setDays();
				break;
			case 'setDate':
				if(this.hasTime){
					time = new Date();
					this.elm.elm.value = obj.getAttribute("date") + " "
						+ time.getHours().pad(2)
						+ ":"
						+ time.getMinutes().pad(2)
						+ ":"
						+ time.getSeconds().pad(2);
				}else{
					this.elm.elm.value = obj.getAttribute("date");
				}
				this.hide();
				console.log(this.caller);
				this.caller.handleOutsideEvent(this.elm, e, this.save);
				break;
		}
	};

	this.setDays();
}