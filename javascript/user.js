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
					"password" : sha512(this.password.val())
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
			$.ajax({
				method: "POST",
				url: "index.php",
				dataType: "script",
				data: {
					"group": "js",
					"className": data.include
				}
			}).done(function(xml) {
				eval(xml);
				$(".login").remove();
				var css = document.getElementsByTagName('link');
				for(var i = 0; i < css.length; i++){
					if(css[i].getAttribute('href').match('user')){
						css[i].setAttribute('href', css[i].getAttribute('href').replace('user', 'main'));
					}
				}
			});
		}else{
			$(this.loginForm).fadeIn(400);
		}
	};
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