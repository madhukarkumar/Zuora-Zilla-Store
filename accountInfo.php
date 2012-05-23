<?php


/*
 * Gather Info
 */

require_once 'lib/API.php';
require_once 'backend/config.php';
require_once 'ecomFunctions.php';


// Collect Post Data to parse out a list of charges selected on pages 1 and 2
session_start();

//Lists to display products
$bprodList = array();
$aprodList = array();
//List to post produts to next page
$postParams = array();

function stripToAlphaNumeric($str){
	//return ereg_replace("/[^A-Za-z0-9.@,!#$%&*+-/=?^_`{|}~ ]/", "", $str );
return htmlentities($str);
}

foreach ($_GET as $postKey => $postValue)
{
	$postKey = stripToAlphaNumeric($postKey); 
	$postValue = stripToAlphaNumeric($postValue);
 	list($bplanNumber) = sscanf($postKey,"BPlan%d");
 	list($aplanNumber) = sscanf($postKey,"APlan%d");
	if ($bplanNumber!=NULL){
		//If this is a selected Base Product ID, collect all passed information and save to bprodList;
		$bplan = $postValue;
		$bqty=null;
		array_push($postParams, array("BPlan".$bplanNumber, $bplan));
		foreach ($_GET as $postKey2 => $postValue2)
		{
			$postKey2 = stripToAlphaNumeric($postKey2);
			$postValue2 = stripToAlphaNumeric($postValue2);
			if($postKey2=="BQty".$bplanNumber){
				$bqty=$postValue2;
				array_push($postParams, array("BQty".$bplanNumber, $bqty));
			}
		}
		$bplanName = getRatePlanNameById($bplan);
		if($bplanName!=null){
			array_push($bprodList, array("id"=>stripToAlphaNumeric($bplan), "name"=>$bplanName, "qty"=>stripToAlphaNumeric($bqty), "num"=>$bplanNumber));
		}
	} else if ($aplanNumber!=NULL){
		//If this is an Add-on Plan ID, collect all passed information and save to aprodList;
		$aplan = $postValue;
		$aqty=null;
		array_push($postParams, array("APlan".$aplanNumber, $aplan));
		foreach ($_GET as $postKey2 => $postValue2)
		{
			if($postKey2=="AQty".$aplanNumber){
				$aqty=$postValue2;
				array_push($postParams, array("AQty".$aplanNumber, $aqty));
			}
		}
		$aplanName = getRatePlanNameById($aplan);
		if($aplanName!=null){
			array_push($aprodList, array("id"=>$aplan, "name"=>$aplanName, "qty"=>$aqty, "num"=>$aplanNumber));
		}
	}
}

$numPlans = count($bprodList) + count($aprodList);

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

$postQtyParams = array();
$destinationParams = "";
foreach($postParams as $planIndex => $planAry){
	if($destinationParams!="") 
		$destinationParams .= "&";
	$destinationParams .= stripToAlphaNumeric($planAry[0]) . "=" . stripToAlphaNumeric($planAry[1]);
	if(!startsWith($planAry[0], 'AQty') && !startsWith($planAry[0], 'BQty')){
		array_push($postQtyParams, array(stripToAlphaNumeric($planAry[0]), stripToAlphaNumeric($planAry[1])));
	}
}
$formDestination = "accountInfo.php?";


// If the form has been submitted, process the subscribe call

$errorResult="";
$HpmOutput = "";

if(isset($_POST['submitted']) && $_POST['submitted']!=NULL){
	if(isset($_POST['paymentMethodId']) && $_POST['paymentMethodId'] != ''){

		$paymentMethodId = $_POST['paymentMethodId'];
//		$errorResult.="P-Message: Payment method " . $paymentMethodId . "<br>";

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

		$sresult = createSfdcAccount($accountName);
//		$errorResult .= "S-Message: " . $sresult['message'];
//		$errorResult .= "<br>";

		if($sresult['success']){

			$ratePlans = array();
			foreach($aprodList as $plan){
				array_push($ratePlans, array("id"=>$plan['id'], "qty"=>$plan['qty']));
			}
			foreach($bprodList as $plan){
				array_push($ratePlans, array("id"=>$plan['id'], "qty"=>$plan['qty']));
			}

			$zresult = makeZuoraSubscription($sresult['id'],$email,$firstName,$lastName,$phone,$accountName,$country,$address1,$address2,$city,$state,$postalcode,$paymentMethodId, $ratePlans);
			$errorResult .= "Error: " . $zresult['message'];
			$errorResult .= "<br>";

			if($zresult['success']){
				userLogin($zresult['contactId']);
				header("Location: homepage.php");
				return;
			}
		}
	} else {
		$errorResult.="Credit Card was not validated.<br>";
		if(isset($_POST['errorMessage']) && strpos($_POST['errorMessage'], 'lacklist token already exists')) {
			$errorResult .= "You have already tried submitting this payment. Please refresh and try again." . "<br>";
		} else if(isset($_POST['errorMessage']) && $_POST['errorMessage']!="") { $HpmOutput .= $_POST['errorMessage'] . "<br>";
		} else {

			//if($_POST['ef_creditCardType']!="") { $HpmOutput .= "Card Type: " . $_POST['ef_creditCardType'] . "<br>"; }
			//if($_POST['ef_creditCardNumber']!="") { $HpmOutput .= "Card Number: " . $_POST['ef_creditCardNumber'] . "<br>"; }
			//if($_POST['ef_creditCardExpirationMonth']!="") { $HpmOutput .= "Expiration Month: " . $_POST['ef_creditCardExpirationMonth'] . "<br>"; }
			//if($_POST['ef_creditCardExpirationYear']!="") { $HpmOutput .= "Expiration Year: " . $_POST['ef_creditCardExpirationYear'] . "<br>"; }
			//if($_POST['ef_cardSecurityCode']!="") { $HpmOutput .= "Security Code: " . $_POST['ef_cardSecurityCode'] . "<br>"; }
			//if($_POST['ef_creditCardHolderName']!="") { $HpmOutput .= "Card Holder Name: " . $_POST['ef_creditCardHolderName'] . "<br>"; }
			$errorResult .= addHpmFieldError(ef_creditCardType);
			$errorResult .= addHpmFieldError(ef_creditCardNumber);
			$errorResult .= addHpmFieldError(ef_creditCardExpirationMonth);
			$errorResult .= addHpmFieldError(ef_creditCardExpirationYear);
			$errorResult .= addHpmFieldError(ef_cardSecurityCode);
			$errorResult .= addHpmFieldError(ef_creditCardHolderName);
		}
	}
}

function addHpmFieldError($fieldName){
	$result = '';

	$cardFieldNames = array(
		"ef_creditCardType"=>"Card Type",
		"ef_creditCardNumber"=>"Card Number",
		"ef_creditCardExpirationMonth"=>"Expiration Month",
		"ef_creditCardExpirationYear"=>"Expiration Year",
		"ef_cardSecurityCode"=>"Security Code",
		"ef_creditCardHolderName"=>"Card Holder Name");


	if($_POST[$fieldName]!="") { 
		$result .= $cardFieldNames[$fieldName].": ";
		if($_POST[$fieldName]=='NullValue'){
			$result .= "Can not be blank.<br>";
		} else {
			$result .= $_POST[$fieldName] . "<br>"; 
		}
	}
	return $result;
}

function printField($fieldName){
	if(isset($_POST[$fieldName]) && $_POST[$fieldName]!=NULL){
		print stripToAlphaNumeric($_POST[$fieldName]);
	}
}


//Get HPM iframe source
//################################################# CHANGE THESE INFORMATION ACCORDINGLY #####################################

//generate random token
$token_length = 32;
$token_bound = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
$token = "";
while(strlen($token) < $token_length) {
	$token .= $token_bound{mt_rand(0, strlen($token_bound)-1)};
}
//end generate random token

//get current time in utc milliseconds
list($usec, $sec) = explode(" ", microtime());
$timestamp = (float)$sec - 2;
$queryString = 'id=' . $pageId . '&tenantId=' . $tenantId . '&timestamp=' . $timestamp . '000&token=' . $token;

//concatenate API security key with query string
$queryStringToHash = $queryString . $apiSecurityKey;
//get UTF8 bytes
$queryStringToHash = utf8_encode($queryStringToHash);
//create MD5 hash
$hashedQueryString = md5($queryStringToHash);
//encode to Base64 URL safe
$hashedQueryStringBase64ed = strtr(base64_encode($hashedQueryString), '+/', '-_');
//formulate the url
$iframeUrl = $appUrl . "/apps/PublicHostedPaymentMethodPage.do?" .
		"method=requestPage&" .
		$queryString . "&" .
		"signature=" . $hashedQueryStringBase64ed;


/*
 * Print Page Data
 */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Subscribe Now</title>
		<link href='css/layout.css' rel='stylesheet' type='text/css' />
<link href="css/layout.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.js" /></script>
<script type="text/javascript" src="js/jquery.cookie.js" /></script>
<script type="text/javascript" src="js/function.js" /></script>
<link type="text/css" rel="stylesheet" href="css/style.css">
<link type="text/css" rel="stylesheet" href="css/karma-teal-grey.css">
<script type="text/javascript">var SlideDeckLens={};</script><script type='text/javascript' src='http://sigmaestimates.com/wp-includes/js/jquery/jquery.js?ver=1.7.1'></script>
<link href="css/nav.css" rel="stylesheet" type="text/css" />

		<script type='text/javascript' src='js/jquery.js' ></script>
		<script type='text/javascript' src='js/country.js' ></script>
		<script type='text/javascript' src='js/function.js' ></script>
		<script type='text/javascript' src='js/postmessage.js' ></script>
		<script type='text/javascript'>

			function validateForm(){
				var subscribe_button = document.getElementById('subscribe_button');

				var validationPassed = true;
				var validationError = "";

				if(<?= $numPlans ?> == 0){
					validationPassed = false;
					validationError += "You must select at least one product to purchase.\n";
				}

				var requiredFields = ["email", "firstName", "lastName", "phone", "accountName", "country", "address1", "city", "state"];
				for(i in requiredFields){
					if(document.forms['subscribe_form'][requiredFields[i]].value == ""){
						validationPassed = false;
						validationError += requiredFields[i] + " is required.\n";
					}
				}

				//var paymentMethodId = document.forms["subscribe_form"]["paymentMethodId"].value;

				if(validationPassed!=true){
					alert (validationError);
					return false;
				} else {
					subscribe_button.onClick="return false;"; 
					subscribe_button.value='Please wait.';
					submitHostedPage("z_hppm_iframe");
					return true;
				}
			}

			function hp_cback_success(ref_id) {

				// Verify the Callback Response 

				document.forms['subscribe_form']['paymentMethodId'].value = ref_id;
				document.forms['subscribe_form'].submit();
			}

			function hp_cback_fail(errorCode, errorMessage, ef_creditCardType, ef_creditCardNumber,ef_creditCardExpirationMonth, ef_creditCardExpirationYear, ef_cardSecurityCode,ef_creditCardHolderName) {
				document.forms['subscribe_form']['errorMessage'].value = errorMessage;
				document.forms['subscribe_form']['ef_creditCardType'].value = ef_creditCardType;
				document.forms['subscribe_form']['ef_creditCardNumber'].value = ef_creditCardNumber;
				document.forms['subscribe_form']['ef_creditCardExpirationMonth'].value = ef_creditCardExpirationMonth;
				document.forms['subscribe_form']['ef_creditCardExpirationYear'].value = ef_creditCardExpirationYear;
				document.forms['subscribe_form']['ef_cardSecurityCode'].value = ef_cardSecurityCode;
				document.forms['subscribe_form']['ef_creditCardHolderName'].value = ef_creditCardHolderName;
				document.forms['subscribe_form'].submit();
			}
		</script>
	</head>
<body>
<div id="wrapper">
<div id="header">
<div class="top-block">
<div class="top-holder">

      <div class="toolbar-left">  
  <ul class="sf-js-enabled">
  <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5172" id="menu-item-5172"><a href="#">Home</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4987" id="menu-item-4987"><a href="#">Blog</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5192" id="menu-item-5192"><a href="#">Partners</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4993" id="menu-item-4993"><a href="#">News &amp; Events</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4988" id="menu-item-4988"><a href="#">Contact Us</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4994" id="menu-item-4994"><a href="#">Support</a></li>
  </ul>
  </div><!-- end toolbar-left -->

    
  
</div><!-- end top-holder -->
</div><!-- end top-block -->




<div class="header-holder">
<div class="rays">
<div class="header-area">

<a class="logo" href="/subsCommApp/index.html"><img alt="Zuora sCommerce App" src="images/acme-logo.png"></a>



<ul id="menu-main-nav">
<li class="menu-item menu-item-type-custom menu-item-object-custom parent" id="item-5237"><a href="#"><span><strong>About Us</strong></span></a>
<div class="drop" style="display: block; height: 0px; overflow: hidden; opacity: 0;"><div class="c"><ul class="sub-menu" style="display: block; left: 0px;">
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5240"><a href="#"><span>Overview</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5243"><a href="#"><span>Investors</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5365"><a href="#"><span>Board Of Directors</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5239"><a href="#"><span>Careers</span></a></li>
</ul></div></div>
</li>
<li class="menu-item menu-item-type-custom menu-item-object-custom parent" id="item-5484"><a><span><strong>Products</strong></span></a>
<div class="drop" style="display: none; height: 0px; overflow: hidden; opacity: 0;"><div class="c"><ul class="sub-menu" style="display: block; left: 0px;">
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5245"><a href="#"><span>Overview</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5247"><a href="#"><span>Personal</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5279"><a href="#"><span>Professional</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5246"><a href="#"><span>Enterprise</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5278"><a href="#"><span>Database</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5277"><a href="#"><span>Mobile</span></a></li>
</ul></div></div>
</li>
<li class="menu-item menu-item-type-post_type menu-item-object-page parent" id="item-4984"><a href="#"><span><strong>Solutions</strong></span></a>
<div class="drop" style="display: none; height: 0px; overflow: hidden; opacity: 0;"><div class="c"><ul class="sub-menu" style="display: block; left: 0px;">
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5589"><a href="#"><span>Benefits to Your Company</span></a></li>
</ul></div></div>
</li>
<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5178"><a href="#"><span><strong>Features</strong></span></a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-4983"><a href="#"><span><strong>Pricing</strong></span></a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom" id="item-5516"><a href="#"><span><strong>Purchase</strong></span></a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom parent" id="item-5747"><a><span><strong>Resources</strong></span></a>
<div class="drop" style="display: none; height: 0px; overflow: hidden; opacity: 0;"><div class="c"><ul class="sub-menu" style="display: block; left: 0px;">
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5375"><a href="#"><span>Customer Success Stories</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5376"><a href="#"><span>Training</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5374"><a href="#"><span>Webinars</span></a></li>
</ul></div></div>
</li>
</ul>

</div><!-- header-area -->
</div><!-- end rays -->
</div><!-- end header-holder -->
</div><!-- end header -->


<!-- end main -->
<div style="clear:both;"></div>
</div>
<div>
	<div id="web_main">
		<div class="edit_dv" style="width:960px;">
			<h3>Create Account</h3>
			<form action='accountInfo.php' id='quantity_form' method='get'>
				<h4>Base Products</h4>
				<div class='field_area'>
<?php
$totalAmount = 0;
if(count($bprodList)==0){
?>
					<div class='field_area'>
				    	<div class='planName'>No Base Product Selected</div>
				        <div class='planDivide'></div>
					</div>
<?php
} else {
	foreach($bprodList as $bProd){
?>
					<div class='field_area'>
			        	<div class='planName'><?= $bProd["name"]?></div>
			        	<div class='planPrice'>
<?php 
		$priceResults = getRatePlanDisplayPrices($bProd["id"]);
		$ratePlanHasQuantity=false;
		$firstLine=true;
		foreach($priceResults as $priceResult){
			$totalAmount += getFirstPrice($priceResult, $bProd["qty"]);
			if($priceResult['model']=='Per Unit Pricing'){
				$ratePlanHasQuantity = true;
			}
			if(!$firstLine) {
				echo "						<br>
						".$priceResult['price']."
";
			} else {
				$firstLine = false;
				echo "						".$priceResult['price']."
";
			}
		}
?>
						<?=$bProd["qty"]!=NULL ? "<br>Quantity: <input type='text' name='BQty".stripToAlphaNumeric($bProd['num'])."' value='" . stripToAlphaNumeric($bProd["qty"]) . "'/>
" : "" ?>
					</div>
				</div>
				<div class='planDivide'></div>
<?php
	}
}
?>
				</div>
				<h4>Selected Add-On Products</h4>
				<div class='field_area'>
<?php
if(count($aprodList)==0){
?>
					<div class='field_area'>
				    	<div class='planName'>No Add-On Product Selected</div>
				        <div class='planDivide'></div>
					</div>
<?php
} else {
	foreach($aprodList as $aProd){
?>
					<div class='field_area'>
			        	<div class='planName'><?= $aProd["name"]?></div>
			        	<div class='planPrice'>
<?php 
		$priceResults = getRatePlanDisplayPrices($aProd["id"]);
		$ratePlanHasQuantity=false;
		foreach($priceResults as $priceResult){
			$totalAmount += getFirstPrice($priceResult, $aProd["qty"]);
			if($priceResult['model']=='Per Unit Pricing'){
				$ratePlanHasQuantity = true;
			}
			if(!$firstLine) {
				echo "						<br>
						".$priceResult['price']."
";
			} else {
				$firstLine = false;
				echo "						".$priceResult['price']."
";
			}
		}
?>
						<?=$aProd["qty"]!=NULL ? "<br>Quantity: <input type='text' name='AQty".stripToAlphaNumeric($aProd['num'])."' value='" . stripToAlphaNumeric($aProd["qty"]) . "'/>
" : "" ?>
					</div>
				</div>
	            <div class='planDivide'></div>
<?php
	}
?>
<?php
}
foreach($postQtyParams as $postQtyParam){
?>
				<input type='hidden' name='<?= $postQtyParam[0]?>' value='<?= $postQtyParam[1]?>'/>
<?php
}
?>
	        <div class='planDivide'></div>
				<div class='field_area'>
					<fieldset class='form_edit_area'>
						<a class='button1_style l_anniu'><input class="l_anniu"  type='submit' value='Change Quantities' onClick='setupQuantities()'/></a>
					</fieldset>
					<fieldset class='form_edit_area'>
			        	<div class='total_label'><b>Total of first invoice <br>(before tax)</b></div>
			        	<div class='total_price'>
							<p><b> $<?=  number_format($totalAmount, 2, '.', ',') ?> </b></p>
						</div>
					</fieldset>
				</div>
			</form>
			<form action='<?= $formDestination . $destinationParams?>' id='subscribe_form' method='post'>
					<h4>Your information</h4>
					<div style="color:red;"><?= ($errorResult!="" ? ("Transaction failed: <br>" . $errorResult ): "") ?></div>
					<div class='field_area'>
						<fieldset>
							<label>Email Address:</label>
							<p>
								<span class='must'><input type='text' id='email' name='email' value='<?php printField('email');?>' /></span><span class='example_text'>Example: test@test.com</span>
							</p>
						</fieldset>
						<fieldset>
							<label>First Name:</label>
							<p>
								<span class='must'><input type='text' id='firstName' name='firstName' value='<?php printField('firstName');?>' /></span>
							</p>
						</fieldset>
						<fieldset>
							<label>Last Name:</label>
							<p>
								<span class='must'><input type='text' id='lastName' name='lastName' value='<?php printField('lastName');?>' /></span>
							</p>
						</fieldset>
						<fieldset>
							<label>Phone:</label>
							<p>
								<span class='must'><input type='text' id='phone' name='phone' value='<?php printField('phone');?>' /></span>
							</p>
						</fieldset>
					</div>
					<h4>Your company</h4>
					<div class='field_area'>
						<fieldset>
							<label>Company Name:</label>
							<p>
								<span class='must'><input type='text' id='accountName' name='accountName' value='<?php printField('accountName');?>' /></span>
							</p>
						</fieldset>
						<fieldset>
							<label>Country:</label>
							<p class="xiala">
								<select id='country' name='country'>
									<option value='United States'>United States</option>
									<option value='Canada'>Canada</option>
									<option value='Mexico'>Mexico</option>
								</select>
							</p>
						</fieldset>
						<fieldset>
							<label>Address 1:</label>
							<p>
								<span class='must'><input id='address1' type='text' name='address1' value='<?php printField('address1');?>' /></span>
								<span class='example_text'>Street address, c/o</span>
							</p>
						</fieldset>
						<fieldset>
							<label>Address 2:</label>
							<p>
								<span class='need'><input id='address2' type='text' name='address2' value='<?php printField('address2');?>' /></span>
								<span class='example_text'>Apartment, unit, building, floor, etc.</span>
							</p>
						</fieldset>
						<fieldset>
							<label>City:</label>
							<p>
								<span class='must'><input id='city' type='text' name='city' value='<?php printField('city');?>' /></span>
							</p>
						</fieldset>
						<fieldset>
							<label>State/Province:</label>
							<p>
								<span class='must'><input id='state' type='text' name='state' value='<?php printField('state');?>' /></span>
							</p>
						</fieldset>
						<fieldset>
							<label>Postal Code:</label>
							<p>
								<span class='must'><input id='postalcode' type='text' name='postalcode' value='<?php printField('postalcode');?>' /></span>
							</p>
						</fieldset>
					</div>
					<h4>Payment Method</h4>
					<div class='field_area'>
						<div id='hpm_error_console' style="color:red;"><?=$HpmOutput?></div>
						<div id='card_dv'>
							<iframe frameborder='1' src='<?php echo $iframeUrl."&errorField_cardSecurityCode=NullValue" ?>' id='z_hppm_iframe'  width='580px' height='200px' ></iframe> 
						</div>
						<input type='hidden' id='paymentMethodId' name='paymentMethodId' value='<?php printField('paymentMethodId');?>'/>
						<input type='hidden' id='errorMessage' name='errorMessage' value='<?php printField('errorMessage');?>'/>
						<input type='hidden' id='ef_creditCardType' name='ef_creditCardType' value='<?php printField('ef_creditCardType');?>'/>
						<input type='hidden' id='ef_creditCardNumber' name='ef_creditCardNumber' value='<?php printField('ef_creditCardNumber');?>'/>
						<input type='hidden' id='ef_creditCardExpirationMonth' name='ef_creditCardExpirationMonth' value='<?php printField('ef_creditCardExpirationMonth');?>'/>
						<input type='hidden' id='ef_creditCardExpirationYear' name='ef_creditCardExpirationYear' value='<?php printField('ef_creditCardExpirationYear');?>'/>
						<input type='hidden' id='ef_cardSecurityCode' name='ef_cardSecurityCode' value='<?php printField('ef_cardSecurityCode');?>'/>
						<input type='hidden' id='ef_creditCardHolderName' name='ef_creditCardHolderName' value='<?php printField('ef_creditCardHolderName');?>'/>
						<input type='hidden' id='submitted' name='submitted' value='1'/>
					</div>
					<div class='field_area'>
						<fieldset class='form_edit_area'>
							<a class='button1_style l_anniu'><input class="l_anniu"  type='button' id='subscribe_button' onClick='validateForm()' value='Subscribe' name='subscribe'></a>
						</fieldset>
					</div>

<?php
foreach($postParams as $postParam){
?>
	<input type='hidden' name='<?php $postParam[0]?>' value='<?= $postParam[1]?>'/>
<?php
}
?>
</div>
			</form>
		</div>
        <div style="clear:both"></div>
	</div>
</div>
<div id="footer">
<div class="footer-area">
<div class="footer-wrapper">
<div class="footer-holder">

<div class="one_fourth">				<h3>Latest from the blog</h3>		<ul>
				<li><a title="Welcome to sCommerce Blog!" href="#">Welcome to Our Blog!</a></li>
				</ul>
		</div><div class="one_fourth"><h3>Our Products</h3><ul class="sub-menu"><li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5328" id="menu-item-5328"><a href="#/">Personal</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5326" id="menu-item-5326"><a href="#">Professional</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5327" id="menu-item-5327"><a href="#">Enterprise</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5330" id="menu-item-5330"><a href="#">Database</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5329" id="menu-item-5329"><a href="#">Mobile</a></li>
</ul></div><div class="one_fourth"><h3>Follow Us		</h3>		



<ul class="social_icons">

	
<li><a onclick="window.open(this.href);return false;" class="twitter" href="http://twitter.com/#!/zuora">Twitter</a></li>
</ul>
<br>


		</div><div class="one_fourth_last"><h3>Contact Us</h3>			<div class="textwidget"><p>Zuora</p>
<p><a target="_blank" href="#">3400 Bridge Parkway<br>
Suite 101<br>
Redwood City, CA<br>
</a></p>
<p><a href="mailto:info@zuora.com">info@zuora.com </a></p>
<p>Toll Free: 1-555-555-5555<br>
Office:1-555-555-5555<br>
Fax: 1-555-555-5555</p>
</div>
		</div>

</div><!-- footer-holder -->
</div><!-- end footer-wrapper -->
</div><!-- end footer-area -->
</div>
<div id="footer_bottom">
  <div class="info">
      <div id="foot_left">&nbsp;
<div class="textwidget"><p>Copyright &copy; 2012 Zuora All rights reserved.</p>
</div>
	</div><!-- end foot_left -->
      <div id="foot_right"><div class="top-footer"><a class="link-top" href="#">top</a></div>


      
		
                    
    </div><!-- end foot_right -->
  </div><!-- end info -->
</div>

</body>
</html>