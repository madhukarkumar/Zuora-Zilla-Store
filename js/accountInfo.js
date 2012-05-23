function validateForm(){
	var lastName = document.forms["subscribe_form"]["lastName"].value;
	
	var validationPassed = true;

	<% //TODO: Validate all account fields. %>

	
	if(validationPassed!=true){
		<% //TODO: Refresh page and display validation errors. %>
		return false;
	} else {
		submitHostedPage("z_hppm_iframe");
		return true;
	}
}

window.onload = function () {document.forms['subscribe_form'].onsubmit = validateForm;}

function hp_cback_success(ref_id) {

	// TODO: Verify the Callback Response 

	document.forms["subscribe_form"]["paymentMethodId"].value = ref_id;
	document.forms["subscribe_form"].submit();
}

//		function hp_cback_fail(errorCode, errorMessage, ef_creditCardType, ef_creditCardNumber,ef_creditCardExpirationMonth, ef_creditCardExpirationYear, ef_cardSecurityCode,ef_creditCardHolderName) {
function hp_cback_fail(errorCode, errorMessage) {
	alert("Credit card could not be validated.");
}