test_mail = function(obj){
	var strEmail = obj.value;
	if(strEmail == ""){
		$(obj).removeClass("mail_erro");
		$(obj).removeClass("mail_success");
		$(obj).next().html("");
		$(obj).next().css("display","none");
		return;
	}
	var emailReg = /^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/;
	if(emailReg.test(strEmail) ){
		
	}else{
		obj.value = "";
		var t = $(obj).next(".error_msg").length;
		if(t > 0){
			$(obj).next(".error_msg").html("please enter your correct email");
		}else{
			$(obj).after("<span class='error_msg'>please enter your correct email</span>");
		}
	}
}

function isNumber(obj){
	var s = obj.value;
	var regu = "^[0-9]+$";
	var re = new RegExp(regu);
	if (re.test(s)) {
		return true;
	}else{
		obj.value = "";
		alert("error");
	}
} 


function isNumberOrline(obj){
	var s = obj.value;
	var regu = "^[0-9\_\-]+$";
	var re = new RegExp(regu);
	if (re.test(s)) {
		return true;
	}else{
		obj.value = "";
		alert("error");
	}
} 



ccClientValidate = function(arguments){
	var cc = arguments.value;
	var ccSansSpace;
	var i, digits, total;
	ccSansSpace = cc.replace(/\D/g, "");
	if(ccSansSpace.length != 16) {
		arguments.IsValid = false;
		$("#infor").css("display","block");
		$("#infor").html("please true Card Number");
		return	
	}
	digits = new Array(16);
	for(i=0; i<16; i++) digits[i] = Number(ccSansSpace.charAt(i));
 
	for(i=0; i<16; i+=2) {
		digits[i] *= 2;
		if(digits[i] > 9) digits[i] -= 9;
	}
	total = 0;
	for(i=0; i<16; i++) total += digits[i];
	if( total % 10 == 0 ) {
		arguments.IsValid = true;
		return; // valid ccn
	}else {
		arguments.IsValid = false;
		arguments.value = "";
		alert("please true Card Number");
		return; // invalid ccn
	}
}