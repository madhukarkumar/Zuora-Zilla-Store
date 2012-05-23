<?php


/*
 * Gather Info
 */

require_once 'lib/API.php';
require_once 'config.php';
//require_once 'functions.php';

require_once 'ecomFunctions.php';


// Collect Post Data to parse out a list of charges selected on pages 1 and 2

$postKeys = $_POST;
$planList = array();
$postParams = array();

foreach ($postKeys as $postKey => $postValue)
{
 	list($bplanNumber) = sscanf($postKey,"BPlan%d");  // scan into a formatted string and return values passed by reference
 	list($aplanNumber) = sscanf($postKey,"APlan%d");  // scan into a formatted string and return values passed by reference
	if ($bplanNumber!=NULL){
		//If this is a selected Base Product ID, collect all passed information and save to bprodList;
		$bplan = $postValue;
		$bqty=null;
		array_push($postParams, array("BProd".$bplanNumber, $bplan));
		foreach ($postKeys as $postKey2 => $postValue2)
		{
			if($postKey2=="BQty".$bplanNumber){
				$bqty=$postValue2;
				array_push($postParams, array("BQty".$bplanNumber, $bqty));
			}
		}
		$bplanName = getRatePlanNameById($bplan);
		if($bplanName!=null){
			array_push($planList, array($bplan, $bqty));
		}
	} else if ($aplanNumber!=NULL){
		//If this is an Add-on Plan ID, collect all passed information and save to aprodList;
		$aplan = $postValue;
		$aqty=null;
		array_push($postParams, array("APlan".$aplanNumber, $aplan));
		foreach ($postKeys as $postKey2 => $postValue2)
		{
			if($postKey2=="AQty".$aplanNumber){
				$aqty=$postValue2;
				array_push($postParams, array("AQty".$aplanNumber, $aqty));
			}
		}
		$aplanName = getRatePlanNameById($aplan);
		if($aplanName!=null){
			array_push($planList, array($aplan, $aqty));
		}
	}
}


/* 
 * Create Subscription
 */

$email = $_POST['email'];
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$phone = $_POST['phone'];
$accountName = $_POST['accountName'];
$country = $_POST['country'];
$address1 = $_POST['address1'];
$address2 = $_POST['address2'];
$city = $_POST['city'];
$state = $_POST['state'];
$postalcode = $_POST['postalcode'];
$paymentmethodid = $_POST['paymentMethodId'];

$errorMessages = array();

//String crmId = makeSfdcAccount(aemail,afirstName,alastName,aphone,aaccountName,acountry,aaddress1,aaddress2,acity,astate,apostalcode);

//String subId = makeZuoraSubscription(crmId,aemail,afirstName,alastName,aphone,aaccountName,acountry,aaddress1,aaddress2,acity,astate,apostalcode, apaymentmethodid);
$subId = makeZuoraSubscription("PWRYBVYQOLXKWEJ",$email,$firstName,$lastName,$phone,$accountName,$country,$address1,$address2,$city,$state,$postalcode,"4025646546546");

/*
 * Print Page Data
 */

?>

	<head>
		<title>Choose Add-on Products</title>
		<link href='css/layout.css' rel='stylesheet' type='text/css' />

		<script type='text/javascript' src='js/jquery.js' /></script>
		<script type='text/javascript' src='js/country.js' /></script>
		<script type='text/javascript' src='js/function.js' /></script>
		<script type='text/javascript' src='js/postmessage.js' /></script>
		<script type='text/javascript' src='js/accountInfo.js' /></script>

	</head>

<body>
	<div id='mesdv'></div>
		<div id='main' class='edit_dv'>
<?php foreach($_POST as $key=>$value){ ?>
			<div class='field_area'>
				<div class='field_area'>
			    	<div class='planName'><?php echo $key ?></div>
			    	<div class='planPrice'><?php echo $value ?></div>
			        <div class='planDivide'></div>
				</div>
			</div>
<?php } ?>
		<div id='foot'>
	</div>

</body>