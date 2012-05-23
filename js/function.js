
$(document).ready(function() {
	$("#inner_block").animate({
		left:"0px",
		opacity:1,
	},800,
	function(){
		open_state = 1;
		timer=setInterval("closeinner()",5000);
	});
});

closeinner = function(){
	if(timer){
		clearInterval(timer);
	}
	$("#inner_block").animate({
		left:"153px",
		opacity:0.1
	},800,function(){open_state = 0;});
}

openinner = function(){
	$("#inner_block").animate({
		left:"0px",
		opacity:1
	},800,function(){open_state = 1;});
}

change_tip = function(){
	if(timer){
		clearInterval(timer);
	}
	if(open_state == 0){
		openinner();
	}else{
		closeinner();
	}
}


var addError = function(msg){
		for(var i=0;i<msg.length;i++){
			if(msg[i].field!=null) {
				//$("#"+msg[i].field).next(".error_msg");
				
				$("#"+msg[i].field).after("<span class='error_msg'>"+msg[i].msg+"</span>");
			}
			else {
				$("#infor").css("display","block");
				$($("#infor").find(".block_message")[0]).html("<div>"+msg[i].msg+"</div>");
			}
		}
};

var erro_active = function(obj){
	if($("#"+obj).val() == ""){
		var t = $("#"+obj).next(".error_msg").length;
		if(t > 0){
			$("#"+obj).next(".error_msg").html("please enter your "+obj);
		}else{
			$("#"+obj).after("<span class='error_msg'>Please enter "+obj+"</span>");
		}
		
	}
}


function QueryString(item){
     var sValue=location.search.match(new RegExp("[\?\&]"+item+"=([^\&]*)(\&?)","i"))
     return sValue?sValue[1]:sValue
}


function parseDateToString(d){
	var t = d.match(new RegExp(/(\d{4})-(\d{2})-(\d{2})T.*/i));
	if(t==null) {
		alert("incorrect date format");
		return null;
	}
	else return t[2]+"/"+t[3]+"/"+t[1];
}

function login_type(){
	$.getJSON("backend/?type=UserLogin",function(data){
		if(!data.login) {
			var htmltext = "";
			htmltext+="<a id='logo_button' href='login.html'>Member Login</a>";
			//htmltext+="<p id='top_message_button'><span>Need an account? <a href='create_account.html'>Click here</a> to create new account</span></p>";
			$(htmltext).appendTo($("#top"));
			login_zz = "0";
		}else {
			var htmltext = "";
			htmltext+="<p id='top_message_button'><span><a href='javascript:logout()'>Log Out</a></span></p>";
			$(htmltext).appendTo($("#top"));
			var t = $(".change_link");
			if(t.length == 0){
				return;
			}else{
				t.each(function(e){
					$(t[e]).attr("href","homepage.html");
				});
			}
		}
	});
}

logout = function(){
	$.getJSON("backend/?type=UserLogout",function(data){
		if(!data.login) {
			$.cookie('plan_state', 'close');
			/*$.cookie('productName', $("#productName").html());
			$.cookie('planName', $("#planName").html());
			$.cookie('productFee', $("#productFee").html());
			$.cookie('productPrice', $("#productEachPrice").html());
			$.cookie('uomname', $("#username").html());
			$.cookie('uomnum', $("#uomNum").html());
			$.cookie('totalnum', $("#total_num").html());*/
			window.location.href="index.html";
		}
	});
}