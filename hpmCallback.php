<script type="text/javascript">
function callback() {

	var success = "<?= isset($_REQUEST['success']) ? $_REQUEST['success'] : "" ?>";

	if (success == "true") {
		var refId = "<?= isset($_REQUEST['refId']) ? $_REQUEST['refId'] : "" ?>";
		parent.hp_cback_success(refId);
	} else {

		var ef_creditCardHolderName = "<?= isset($_REQUEST['errorField_creditCardHolderName']) ? $_REQUEST['errorField_creditCardHolderName'] : "" ?>";
		var ef_cardSecurityCode = "<?= isset($_REQUEST['errorField_cardSecurityCode']) ? $_REQUEST['errorField_cardSecurityCode'] : "" ?>";
		var ef_creditCardExpirationYear = "<?= isset($_REQUEST['errorField_creditCardExpirationYear']) ? $_REQUEST['errorField_creditCardExpirationYear'] : "" ?>";
		var ef_creditCardExpirationMonth = "<?= isset($_REQUEST['errorField_creditCardExpirationMonth']) ? $_REQUEST['errorField_creditCardExpirationMonth'] : "" ?>";
		var ef_creditCardNumber = "<?= isset($_REQUEST['errorField_creditCardNumber']) ? $_REQUEST['errorField_creditCardNumber'] : "" ?>";
		var ef_creditCardType = "<?= isset($_REQUEST['errorField_creditCardType']) ? $_REQUEST['errorField_creditCardType'] : "" ?>";
		var errorMessage = "<?= isset($_REQUEST['errorMessage']) ? $_REQUEST['errorMessage'] : "" ?>";

		var errorCode = "<?= isset($_REQUEST['errorCode']) ? $_REQUEST['errorCode'] : "" ?>";

		parent.hp_cback_fail(errorCode, errorMessage, ef_creditCardType, ef_creditCardNumber,ef_creditCardExpirationMonth, ef_creditCardExpirationYear, ef_cardSecurityCode,ef_creditCardHolderName);
//		parent.hp_cback_fail(errorCode);
	}
}
</script>



<title>HPM Result</title>
</head>
<body onload="callback();">

	<p>Processing payment...</p>
	
</body>
</html>