function user(){
	this.username = $("input[name*='username']");
	this.password = $("input[name*='password']");
	this.loginForm = $(".login");
	this.msg = "";
	$(".login").data("instance", this);
	this.login = function(){
		$(this.loginForm).fadeOut(800);
		$.ajax({
			method: "POST",
			url: "index.php",
			data: {
				"group": "include",
				"className": "user",
				"methodName": "xValidateLogin",
				"argument": {
					"username" : this.username.val(),
					"password" : this.password.val()
				}
			}
		}).done(function(xml) {
			$(".login").data("instance").valid(xml);
		});
	};
	this.valid = function(dataStr){
		var data = JSON.parse(dataStr);
		$(".loginMsg span").text(data.loginAttempt);
		if(parseInt(data.valid) == 1){
			$(".login").remove();
			var item;
			if(typeof(data.framework) != "undefined"){
				window.__userFramework = data.framework;
				leadFramework();
			}
		}else{
			$(this.loginForm).fadeIn(400);
		}
	};
}

function leadFramework(){
	if(window.__userFramework.length > 0){
		var head = document.getElementsByTagName("head")[0];
		var data = window.__userFramework[0];
		window.__userFramework.shift();
		
		switch(data.type){
			case "js":
				item = document.createElement("script");
				item.onload = function(){
					leadFramework();
				};
				item.setAttribute("src", "javascript/" + data.value);
				item.setAttribute("type", "text/javascript");
				break;
			case "css":
				item = document.createElement("link");
				item.onload = function(){
					leadFramework();
				};
				item.setAttribute("href", "css/" + data.value);
				item.setAttribute("type", "text/css");
				item.setAttribute("rel", "stylesheet");
				item.setAttribute("media", "screen");
				break;
		}
		head.appendChild(item);
	}else{
		var cms = new builder();
		cms.init();
	}
}

$(window).ready(function() {
	var login = new user();

	$("input[name*='username']").focus();

	$(".loginBtn a").click(function(){
		login.login();
	});

	$("input[name*='username']").keypress(function (e) {
		 var key = e.which;
		 if(key == 13){
			 if($("input[name*='password']").val() != ""){
				 login.login();
			 }else{
				 $("input[name*='password']").focus();
			 }
		 }
	});

	$("input[name*='password']").keypress(function (e) {
		 var key = e.which;
		 if(key == 13){
			 if($("input[name*='username']").val() != ""){
				 login.login();
			 }else{
				 $("input[name*='username']").focus();
			 }
		 }
	});
});