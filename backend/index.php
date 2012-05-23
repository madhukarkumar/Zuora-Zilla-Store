<?php
set_time_limit(200);
session_start() ;
require_once('login.php');

$debug = 1; //debug mode

$errors = array();
$messages = null;

//debug($client->__getFunctions());

dispatcher($_REQUEST['type']);



function addErrors($field,$msg){
	global $errors;
	$error['field']=$field;
	$error['msg']=$msg;
	$errors[] = $error;
}

function dispatcher($type){
	
	switch($type) {
		case 'CreateAccount' : createAccount();
		break;
		case 'GetProducts' : getProducts();
		break;
		case 'GetProdcutDetail' : getProdcutDetail();
		break;
		case 'UserLogin' : userLogin();
		break;
		case 'UserLogout' : userLogout();
		break;
		case 'GetUserInfo' : getUserInfo();
		break;
		case 'UserPurchase' : userPurchase();
		break;
		case 'GetInvoice' : getInvoice();
		break;
		case 'GetSubscription' : getSubscription();
		break;
		case 'UpdatePayment' : updatePayment();
		break;
		case 'UpdateAccount' : updateAccount();
		break;
		case 'VerifyAccount' : verifyAccount();
		break;
		case 'test' : test();
		break;
		default : addErrors(null,'no action specified');
	}
	
}

function verifyAccount() {
	global $client,$header,$errors;
	if(getAccount($_REQUEST['accountName'])>0) {
		addErrors('accountName','this account is already in our system, please contact your administrator');
		return false;
	}

	if(getEmail($_REQUEST['email'])>0) {
		addErrors('email','this email address is already in our system');
		return false;
	}
}

function createAccount(){
	global $client,$header,$errors;
	if(getAccount($_REQUEST['accountName'])>0) {
		addErrors('accountName','this account is already in our system, please contact your administrator');
		return false;
	}

	if(getEmail($_REQUEST['email'])>0) {
		addErrors('email','this email address is already in our system');
		return false;
	}

	$account = array(
           "Name"=>$_REQUEST['accountName'],
           "Status"=>"Draft",
		   "Currency" => 'USD',
		   "BillCycleDay" => 1
    );
    $accounts = array(new SoapVar($account, SOAP_ENC_OBJECT, "Account", "http://object.api.zuora.com/"));

	$create = array(
   		"zObjects"=>$accounts
    );

	$result = $client->__soapCall("create", $create, null, $header); 

	 if ($result->result->Id == null) {
		addErrors(null,'Create Account error');
	 }
	 else {
		 //create contact
		 $accountid = $result->result->Id;
		$contact = array(
				"AccountId"=>$accountid,
				"WorkEmail"=>$_REQUEST['email'],
				"LastName"=>$_REQUEST['lastName'],
				"FirstName"=>$_REQUEST['firstName'],
				"WorkPhone"=>$_REQUEST['phone'],
				"Country"=>$_REQUEST['country'],
				"Address1"=>$_REQUEST['address1'],
				"Address2"=>$_REQUEST['address2'],
				"City"=>$_REQUEST['city'],
				"State"=>$_REQUEST['state'],
				"PostalCode"=>$_REQUEST['postalCode']
		
		);
		$contacts = array(new SoapVar($contact, SOAP_ENC_OBJECT, "Contact", "http://object.api.zuora.com/"));
		$create = array(
			"zObjects"=>$contacts
		);
		$result = $client->__soapCall("create", $create, null, $header);
		if(!$result->result->Success) {
			addErrors(null,'Create Contact error');
			return false;
		}
		
		$contactid = $result->result->Id;
		

		
		// create payment method

		$payment = array(
				"AccountId"=>$accountid,
				"CreditCardType"=>$_REQUEST['creditCardType'],
				"CreditCardNumber"=>$_REQUEST['creditCardNumber'],
				"Type"=>'CreditCard',
				"CreditCardHolderName"=>$_REQUEST['firstName'].' '.$_REQUEST['lastName'],
				"CreditCardExpirationMonth"=>$_REQUEST['creditCardExpirationMonth'],
				"CreditCardExpirationYear"=>$_REQUEST['creditCardExpirationYear']
		
		);
		$payments = array(new SoapVar($payment, SOAP_ENC_OBJECT, "PaymentMethod", "http://object.api.zuora.com/"));
		$create = array(
			"zObjects"=>$payments
		);
		$result = $client->__soapCall("create", $create, null, $header);
		if(!$result->result->Success) {
			addErrors(null,'Create PaymentMethod error');
			return false;
		}

		//update Account
		
		$account = array(
           "Id"=>$accountid,
           "BillToId"=>$contactid,
		   "SoldToId" =>$contactid,
			"PaymentTerm"=>"Due Upon Receipt",
			"DefaultPaymentMethodId"=>$result->result->Id,
			"Batch"=>"Batch1",
			"Status"=>"Active"
		);
		$accounts = array(new SoapVar($account, SOAP_ENC_OBJECT, "Account", "http://object.api.zuora.com/"));

		$update = array(
			"zObjects"=>$accounts
		);

		$result = $client->__soapCall("update", $update, null, $header); 
		
		//print_r($result);
		if(!$result->result->Success) {
			addErrors(null,'Update Account/SellTo/BillTo error');
			return false;
		}

	 }
}

function updateAccount(){

	if(!$_SESSION['contactId'] && !$_SESSION['accountId'] ) {
		addErrors(null,'please login first');
		return false;
	}
	if(!empty($_REQUEST['email'])) {
		// check email address;
		$result = zQuery("select Id from Contact where Id != '".$_SESSION['contactId']."' and WorkEmail = '".$_REQUEST['email']."'");
		//$result = zQuery("select Id from Contact where Id != '".$_SESSION['contactId']."' ");
		if($result->result->size>0){
			addErrors('email','this email address has been taken');
			return false;
		}
	}

	

	$contact = array(
			"Id"=>$_SESSION['contactId']
	);
	if(!empty($_REQUEST['email']))	$contact["WorkEmail"]=$_REQUEST['email'];
	if(!empty($_REQUEST['lastName']))	$contact["LastName"]=$_REQUEST['lastName'];
	if(!empty($_REQUEST['firstName']))	$contact["FirstName"]=$_REQUEST['firstName'];
	if(!empty($_REQUEST['phone']))	$contact["WorkPhone"]=$_REQUEST['phone'];
	if(!empty($_REQUEST['country']))	$contact["Country"]=$_REQUEST['country'];
	if(!empty($_REQUEST['address1']))	$contact["Address1"]=$_REQUEST['address1'];
	if(!empty($_REQUEST['address2']))	$contact["Address2"]=$_REQUEST['address2'];
	if(!empty($_REQUEST['city']))	$contact["City"]=$_REQUEST['city'];
	if(!empty($_REQUEST['state']))	$contact["State"]=$_REQUEST['state'];
	if(!empty($_REQUEST['postalCode']))	$contact["PostalCode"]=$_REQUEST['postalCode'];

	//debug($contact);
	
	$result = zCreateUpdate("update", $contact, 'Contact');
	if(!$result->result->Success) {
		addErrors(null,'Update Contact error');
		return false;
	}

}

function updatePayment() {
	if(!$_SESSION['contactId'] && !$_SESSION['accountId'] ) {
		addErrors(null,'please login first');
		return false;
	}
	if(empty($_REQUEST['pid'])) {
		addErrors(null,'payment id is required');
		return false;
	}
	
	$result = zQuery("select Id,AccountId,CreditCardType,CreditCardExpirationMonth,CreditCardExpirationYear,CreditCardHolderName from PaymentMethod where Id='".$_REQUEST['pid']."' and AccountId='".$_SESSION['accountId']."'");

	if($result->result->size!=1) {
		addErrors(null,'no such payment');
		return false;
	}
	else {
		$record=$result->result->records;
		$paymentmethod = array(
			"Id"=>$record->Id,
			"CreditCardType"=>$_REQUEST['creditCardType'],
			"CreditCardNumber"=>$_REQUEST['creditCardNumber'],
			//"Type"=>'CreditCard',
			"CreditCardHolderName"=>$_REQUEST['firstName'].' '.$_REQUEST['lastName'],
			"CreditCardExpirationMonth"=>$_REQUEST['creditCardExpirationMonth'],
			"CreditCardExpirationYear"=>$_REQUEST['creditCardExpirationYear']
		);

		$result = zCreateUpdate('update',$paymentmethod,"PaymentMethod");
		if(!$result->result->Success) {
			debug($result);
			addErrors('creditCardNumber','invalid credit card number');
			return false;
		}
	}

}



function getAccount($name) {
   $result = zQuery("select id from account where Name='$name'");   
   return $result->result->size;
}

function getEmail($email){
	$result = zQuery("select Id from Contact where WorkEmail = '".$email."'");
	return $result->result->size;
}

function getProducts(){
	global $client,$header,$messages;

	$zoql = array(
		"queryString"=>"select Id, Name,SKU,Description,EffectiveEndDate,EffectiveStartDate from Product where EffectiveEndDate > ".date('Y-m-d')."T00:00:00"
	);
	$queryWrapper = array(
   		"query"=>$zoql
    );
	$result = $client->__soapCall("query", $queryWrapper, null, $header); 

	if($result->result!=null) {

		$messages = $result->result->records;
	}

}

function getProdcutDetail($pid=null){
	global $client,$header,$messages;

	$pid = $pid == null ? $_REQUEST['id'] : $pid; //product id;


	if(!$pid) {
		addErrors(null,'no product id specified');
		return false;
	}

	

	$result = zQuery("select Id, Name,SKU,Description,EffectiveEndDate,EffectiveStartDate from Product where Id = '".$pid."'");

	if($result->result->size==0) {
		addErrors(null,'no such product');
		return false;
	}

	$messages['product'] = $result->result->records;

	//get rate plan
	$zoql = array(
		"queryString"=>"select Id, Name,Description,EffectiveEndDate,EffectiveStartDate,ProductId from ProductRatePlan where ProductId = '".$pid."'"
	);
	$queryWrapper = array(
   		"query"=>$zoql
    );
	$result = $client->__soapCall("query", $queryWrapper, null, $header);
	
	if($result->result!=null) {
		$records = $result->result->records;
		$pids = array();
		$productRatePlans = array();
		for($i=0;$i<count($records);$i++){
			if($result->result->size>1) $record = $records[$i];
			else $record = $records;
			$productRatePlans[$i]['Id']=$record->Id;
			$productRatePlans[$i]['Name']=$record->Name;
			$productRatePlans[$i]['Description']=$record->Description;
			$productRatePlans[$i]['EffectiveEndDate']=$record->EffectiveEndDate;
			$productRatePlans[$i]['EffectiveStartDate']=$record->EffectiveStartDate;
			$productRatePlans[$i]['RatePlanCharge'] = array();
			$pids[] = " ProductRatePlanId = '".$record->Id."' ";
		}

		//print_r($productRatePlans);
		
		//get Rate Plan Charge

		$result = null;
		
		$result = zQuery("select Id, AccountingCode,Description, DefaultQuantity, MaxQuantity , MinQuantity , ChargeModel , ProductRatePlanId, ChargeType, UOM from ProductRatePlanCharge where ".implode(" or ",$pids));

		$rids = array();


		if($result->result!=null) {
			$records = $result->result->records;
			//print_r($records);
			for($i=0;$i<count($records);$i++){
				if(count($records) == 1)
					$record = $records;
				else
					$record =  $records[$i];
				for($j=0;$j<count($productRatePlans);$j++) {
				
					if($productRatePlans[$j]['Id']==$record->ProductRatePlanId) {

						$rateplan = array();
						$rateplan['Id'] = $record->Id;
						$rateplan['AccountingCode'] = $record->AccountingCode;
						$rateplan['Description'] = $record->Description;
						$rateplan['DefaultQuantity'] = $record->DefaultQuantity;
						$rateplan['MaxQuantity'] = $record->MaxQuantity;
						$rateplan['MinQuantity'] = $record->MinQuantity;
						$rateplan['ChargeModel'] = $record->ChargeModel;
						$rateplan['ProductRatePlanId'] = $record->ProductRatePlanId;
						$rateplan['ChargeType'] = $record->ChargeType;
						$rateplan['UOM'] = $record->UOM;
						$rateplan['ChargeTier'] = array();
						
						$productRatePlans[$j]['RatePlanCharge'][] = $rateplan;
						
					}
					
				}
				
				$rids[] = " ProductRatePlanChargeId = '".$record->Id."' ";
			
			}
			
			$result = null;

if(sizeof($rids) > 0)
{
			$result = zQuery("select Id, Tier,StartingUnit, ProductRatePlanChargeId, Price , EndingUnit from ProductRatePlanChargeTier where ".implode(" or ",$rids));
}
			
			if($result->result!=null) {
				$records = $result->result->records;
				
				
					for($j=0;$j<count($productRatePlans);$j++) {
						for($z=0;$z<count($productRatePlans[$j]['RatePlanCharge']);$z++) {
							
							for($i=0;$i<count($records);$i++){
								//$i=0; // get the first row
								$record =  $records[$i];
								if($productRatePlans[$j]['RatePlanCharge'][$z]['Id'] == $record->ProductRatePlanChargeId){
									$productRatePlans[$j]['RatePlanCharge'][$z]['ChargeTier'][]=$record;
								}
							}
						}
					}
				
			}

		}
	
		$messages['rateplans']=$productRatePlans;
	}
//print_r($productRatePlans);
}

function userPurchase(){
	global $client,$header,$messages;

	if(!$_REQUEST['uom'] || !$_REQUEST['rid'] || !$_REQUEST['cid']) {
		addErrors(null,'uom/rateplan id/rateplancharge id is missing');
		return false;
	}

	if(empty($_REQUEST['accountName'])){
		addErrors('accountName','company name is required');
		return false;
	}

	if(empty($_REQUEST['email'])){
		addErrors('email','email address is required');
		return false;
	}

	if(getAccount($_REQUEST['accountName'])>0) {
		addErrors('accountName','this account is already in our system, please contact your administrator');
		return false;
	}

	if(getEmail($_REQUEST['email'])>0) {
		addErrors('email','this email address is already in our system');
		return false;
	}

	//create subscription

	//$result = zQuery("select Id,Name,BillToId,BillCycleDay,SoldToId,Status,PaymentTerm from Account where Id ='".$_SESSION['accountId']."'");
	//$record = $result->result->records;
	$account = array(
		"AccountNumber"=>"t-". microtime(true),	
		"AllowInvoiceEdit"=>true,	
		"AutoPay"=>false,	
		"Batch"=>"Batch1",	
		"BillCycleDay"=>1,
		"CrmId"=>"SFDC-1230273269317",
		"Currency"=>"USD",
		"CustomerServiceRepName"=>"CSR Dude",
		"Name"=>$_REQUEST['accountName'],
		"PurchaseOrderNumber"=>"PO-1230273269317",
		"PaymentTerm"=>"Due Upon Receipt",
		"SalesRepName"=>"Sales Dude",
		"Status"=>"Draft"
	);
/*
	<ns2:AccountNumber>t-1230273269317</ns2:AccountNumber>
			<ns2:AllowInvoiceEdit>true</ns2:AllowInvoiceEdit>
			<ns2:AutoPay>false</ns2:AutoPay>
			<ns2:Batch>Batch1</ns2:Batch>
			<ns2:BillCycleDay>1</ns2:BillCycleDay>
			<ns2:CrmId>SFDC-1230273269317</ns2:CrmId>
			<ns2:Currency>USD</ns2:Currency>
			<ns2:CustomerServiceRepName>CSR Dude</ns2:CustomerServiceRepName>
			<ns2:Name>SomeAccount1230273269317</ns2:Name>
			<ns2:PaymentTerm>Due Upon Receipt</ns2:PaymentTerm>
			<ns2:PurchaseOrderNumber>PO-1230273269317</ns2:PurchaseOrderNumber>
			<ns2:SalesRepName>Sales Dude</ns2:SalesRepName>
			<ns2:Status>Draft</ns2:Status>
*/
	$accountVar = new SoapVar($account, SOAP_ENC_OBJECT, "Account", "http://object.api.zuora.com/");
	
	//$result = zQuery("select Id,AccountId,WorkEmail,Country,City,Address1,FirstName,LastName,PostalCode from Contact where Id ='".$_SESSION['contactId']."'");
	//$record = $result->result->records;
	$contact = array(
		//"AccountId"=>$record->AccountId,	
		"WorkEmail"=>$_REQUEST['email'],	
		"Country"=>$_REQUEST['country'],	
		"City"=>$_REQUEST['city'],	
		"Address1"=>$_REQUEST['address1'],	
		"Address2"=>$_REQUEST['address2'],	
		"FirstName"=>$_REQUEST['firstName'],	
		"LastName"=>$_REQUEST['lastName'],	
		"PostalCode"=>$_REQUEST['postalCode'],	
		"State"=>$_REQUEST['state'],
		"WorkPhone"=>$_REQUEST['phone']
	);
		/*

			<ns2:Address1>52 Vexford Lane</ns2:Address1>
			<ns2:City>Anaheim</ns2:City>
			<ns2:Country>United States</ns2:Country>
			<ns2:FirstName>Firstly1230273271957</ns2:FirstName>
			<ns2:LastName>Secondly1230273271957</ns2:LastName>
			<ns2:PostalCode>92808</ns2:PostalCode>
			<ns2:State>California</ns2:State>
			<ns2:WorkEmail>contact@test.com</ns2:WorkEmail>
			<ns2:WorkPhone>4152225151</ns2:WorkPhone>
		*/

	$contactVar = new SoapVar($contact, SOAP_ENC_OBJECT, "Contact", "http://object.api.zuora.com/");
	
	//$result = zQuery("select Active,CreditCardAddress1,CreditCardAddress2,CreditCardCity,CreditCardCountry,CreditCardExpirationMonth,CreditCardExpirationYear,CreditCardHolderName,CreditCardPostalCode,CreditCardState,CreditCardType,LastTransactionDateTime,LastTransactionStatus,Name,PaypalBaid,PaypalEmail,Type,UpdatedDate,CreditCardMaskNumber from PaymentMethod where AccountId = '".$_SESSION['accountId']."'");
	
	//$record = $result->result->records;
	$paymentmethod = array(
		"CreditCardAddress1"=>$_REQUEST['address1'],	
		//"CreditCardAddress2"=>"22222 dd",	
		"CreditCardCity"=>$_REQUEST['city'],	
		"CreditCardCountry"=>$_REQUEST['country'],	
		"CreditCardExpirationMonth"=>$_REQUEST['creditCardExpirationMonth'],	
		"CreditCardExpirationYear"=>$_REQUEST['creditCardExpirationYear'],	
		"CreditCardHolderName"=>$_REQUEST['firstName'].' '.$_REQUEST['lastName'],	
		"CreditCardPostalCode"=>$_REQUEST['postalCode'],	
		"CreditCardState"=>$_REQUEST['state'],
		"CreditCardNumber"=>$_REQUEST['creditCardNumber'],	
		"CreditCardType"=>$_REQUEST['creditCardType'],	
		"Type"=>"CreditCard"
	);

	/*
		<ns2:CreditCardAddress1>52 Vexford Lane</ns2:CreditCardAddress1>
			<ns2:CreditCardCity>Anaheim</ns2:CreditCardCity>
			<ns2:CreditCardCountry>United States</ns2:CreditCardCountry>
			<ns2:CreditCardExpirationMonth>1</ns2:CreditCardExpirationMonth>
			<ns2:CreditCardExpirationYear>2009</ns2:CreditCardExpirationYear>
			<ns2:CreditCardHolderName>Firstly Lastly</ns2:CreditCardHolderName>
			<ns2:CreditCardNumber>4959911773775979</ns2:CreditCardNumber>
			<ns2:CreditCardPostalCode>92808</ns2:CreditCardPostalCode>
			<ns2:CreditCardState>California</ns2:CreditCardState>
			<ns2:CreditCardType>Visa</ns2:CreditCardType>
			<ns2:Type>CreditCard</ns2:Type>

	*/
	
	$paymentmethodVar = new SoapVar($paymentmethod, SOAP_ENC_OBJECT, "PaymentMethod", "http://object.api.zuora.com/");

	
	
	$subscription = array(
		"AutoRenew"=>false,
		"ContractAcceptanceDate"=>date('Y-m-d').'T12:00:00',
		"ContractEffectiveDate"=>date('Y-m-d').'T12:00:00',
		"ServiceActivationDate"=>date('Y-m-d').'T12:00:00',
		"TermStartDate"=>date('Y-m-d').'T12:00:03',
		"InitialTerm"=>12,
		"RenewalTerm"=>12,
		"Name"=>'A-S000000'.date('YmdHms'),
		"Version"=>1,
	);

	/*
				<ns2:AutoRenew>false</ns2:AutoRenew>
				<ns2:ContractAcceptanceDate>2008-12-25T22:34:31.957-08:00</ns2:ContractAcceptanceDate>
				<ns2:ContractEffectiveDate>2008-12-25T22:34:31.957-08:00</ns2:ContractEffectiveDate>
				<ns2:InitialTerm>12</ns2:InitialTerm>
				<ns2:Name>SomeSubscription1230273271957</ns2:Name>
				<ns2:RenewalTerm>12</ns2:RenewalTerm>
				<ns2:ServiceActivationDate>2008-12-25T22:34:31.957-08:00</ns2:ServiceActivationDate>
				<ns2:TermStartDate>2008-12-25T22:34:31.957-08:00</ns2:TermStartDate>
				<ns2:Version>1</ns2:Version>
	*/

	$subscriptionVar = new SoapVar($subscription, SOAP_ENC_OBJECT, "Subscription", "http://object.api.zuora.com/");

	//$SubscriptionData = new SoapVar($subscription, SOAP_ENC_OBJECT, "SubscriptionData", "http://object.api.zuora.com/");
	$SubscriptionData = array(
		"ns2:Subscription"=>$subscriptionVar
	);

	//$SubscriptionData = new SoapVar($SubscriptionData, SOAP_ENC_OBJECT, "SubscriptionData", "http://object.api.zuora.com/");

	//$result = zQuery("select Id,Name,ProductId,EffectiveStartDate,EffectiveEndDate,Description from ProductRatePlan where Id ='".$_REQUEST['rid']."'");
	//$record = $result->result->records;
	/*
	$ProductRatePlan = array(
		"Id"=>$record->Id,	
		"Name"=>$record->Name,	
		"ProductId"=>$record->ProductId,	
		"EffectiveStartDate"=>$record->EffectiveStartDate,	
		"EffectiveEndDate"=>$record->EffectiveEndDate,	
		"Description"=>$record->Description
	);
	*/
	$rateplan = array("ProductRatePlanId"=>$_REQUEST['rid']);
	
	$rateplanVar = new SoapVar($rateplan, SOAP_ENC_OBJECT, "RatePlan", "http://object.api.zuora.com/");

	
	$result = zQuery("select Id, AccountingCode,Description, DefaultQuantity, MaxQuantity , MinQuantity , ChargeModel , ProductRatePlanId, ChargeType, UOM from ProductRatePlanCharge where Id ='".$_REQUEST['cid']."'");
	$record = $result->result->records;
	$RatePlanCharge = array(
		"Name"=>"test11111",	
		"ProductRatePlanChargeId"=>$record->Id,
		"Quantity"=>$_REQUEST['uom']
	);

	$RatePlanChargeVar = new SoapVar($RatePlanCharge, SOAP_ENC_OBJECT, "RatePlanCharge", "http://object.api.zuora.com/");



	$RatePlanData = array(
		"ns2:RatePlan"=>$rateplanVar,
		"ns2:RatePlanCharge"=>$RatePlanChargeVar
	);

	$RatePlanData = new SoapVar($RatePlanData, SOAP_ENC_OBJECT, "RatePlanData", "http://object.api.zuora.com/");

	$SubscriptionData["ns2:RatePlanData"] = $RatePlanData;

	$SubscriptionDataVar = new SoapVar($SubscriptionData, SOAP_ENC_OBJECT, "SubscriptionData", "http://object.api.zuora.com/");

	$SubscribeRequest = array(
		"ns2:Account" =>$accountVar,
		"ns2:PaymentMethod" =>$paymentmethodVar, 
		"ns2:BillToContact" =>$contactVar, 
		"ns2:SubscriptionData"=>$SubscriptionDataVar
	);
	
	$SubscribeRequest = new SoapVar($SubscribeRequest, SOAP_ENC_OBJECT, "SubscribeRequest", "http://object.api.zuora.com/");
	
	$subscribes = array(
		"subscribes"=>array($SubscribeRequest)
	);

	//$subscribe = new SoapVar($subscribe, SOAP_ENC_OBJECT, "SubscribeRequest", "http://object.api.zuora.com/");
	//echo "REQUEST:\n" . $client->__getLastRequest() . "\n";

	try {
		$result = $client->__soapCall('subscribe', $subscribes, null, $header);
		if(!$result->result->Success){
			addErrors(null,$result->result->Errors->Message);
			//return;
		}
	}
	catch(Exception $e){

		echo "REQUEST:\n" . $client->__getLastRequest() . "\n";

		debug($e);
	}

	//debug($result);
	


}

function userLogin(){
	global $client,$header,$messages;

	if(!$_REQUEST['email']) {
		addErrors('email','email address is required');
		return false;
	}

	unset($_SESSION['accountId']);

	$result = zQuery("select Id, AccountId,LastName,FirstName,WorkPhone,Country,Address1,Address2,City,State,PostalCode from Contact where WorkEmail ='".$_REQUEST['email']."'");

	if($result->result!=null && $result->result->size==1) {
		$record = $result->result->records;
		$_SESSION['accountId']=$record->AccountId;
		$_SESSION['contactId']=$record->Id;
		$_SESSION['contactInfo'] = $record;

		$result = zQuery("select Id,Name from Account where Id = '".$record->AccountId."'");
		$record = $result->result->records;
		$_SESSION['accountInfo'] = $record;

	}
	else {
		userLogout();
		addErrors('email','email address is incorrect');
		return false;
	}
}

function userLogout(){
	unset($_['SESSION']);
	unset($_SESSION['accountId']);
	unset($_SESSION['contactId']);
	session_destroy();
}

function getUserInfo() {

	global $messages;

	if(!$_SESSION['contactId'] && !$_SESSION['accountId'] ) {
		addErrors(null,'please login first');
		return false;
	}

	//get contact info 
	$result = zQuery("select Id, AccountId,LastName,FirstName,WorkEmail,WorkPhone,Country,Address1,Address2,Fax,City,State,PostalCode from Contact where Id  ='".$_SESSION['contactId']."'");
	
	if($result->result!=null && $result->result->size==1) {
		$record = $result->result->records;
		$_SESSION['accountId']=$record->AccountId;
		$_SESSION['contactId']=$record->Id;
		$_SESSION['contactInfo'] = $record;

		$messages['contactInfo'] = $_SESSION['contactInfo'];
		
		//get account info
		$result = zQuery("select Id,Name from Account where Id = '".$record->AccountId."'");
		$record = $result->result->records;
		$_SESSION['accountInfo'] = $record;

		$messages['accountInfo'] = $_SESSION['accountInfo'];

		//get invoice info
		
		$result = zQuery("select Amount,AccountId,Balance,DueDate,InvoiceDate,InvoiceNumber,Status,TargetDate from Invoice where AccountId = '".$_SESSION['accountId']."'");
		$records = $result->result->records;
		
		$messages['invoiceInfo'] = $records;

		//get payment method

		$result = zQuery("select Active,CreditCardAddress1,CreditCardAddress2,CreditCardCity,CreditCardCountry,CreditCardExpirationMonth,CreditCardExpirationYear,CreditCardHolderName,CreditCardPostalCode,CreditCardState,CreditCardType,LastTransactionDateTime,LastTransactionStatus,Name,PaypalBaid,PaypalEmail,Type,UpdatedDate,CreditCardMaskNumber from PaymentMethod where AccountId = '".$_SESSION['accountId']."'");
		$records = $result->result->records;
		
		$messages['paymentInfo'] = $records;
		
		//get subscriptions

		getSubscription();
	}

}

function getSubscription(){
	
	global $messages;

	if(!$_SESSION['contactId'] && !$_SESSION['accountId'] ) {
		addErrors(null,'please login first');
		return false;
	}

	$zoql = "select Id,AutoRenew,ContractEffectiveDate,InitialTerm,Name,Notes,RenewalTerm,ServiceActivationDate,Status,TermStartDate,Version from Subscription where AccountId = '".$_SESSION['accountId']."'";
	
	if($_REQUEST['sid']) { // subscription id
		$zoql .= " and  Id = '".$_REQUEST['sid']."'";
	}

	$result = zQuery($zoql);

	debug($result);
		
	if($result->result->size>0) {
		$subscriptions = $result->result->records;
		
		$messages['subscriptionInfo'] = $subscriptions;

		$subscriptionIds = array();

		if($result->result->size==1) {
			$subscriptionIds[] = " SubscriptionId = '".$subscriptions->Id."' ";
		}
		else {
			for($i=0;$i<count($subscriptions);$i++) {
				$subscriptionIds[] = " SubscriptionId = '".$subscriptions[$i]->Id."' ";
				//$subscriptions[$i]->RatePlan = array();
			}
		}

		// get  rate plan
		
		$result = zQuery("select Id, AmendmentId,AmendmentSubscriptionRatePlanId,AmendmentType,ProductRatePlanId,SubscriptionId from RatePlan where ".implode(" or ",$subscriptionIds));

		debug($result);

		if($result->result->size>0) {

			$rateplans =  $result->result->records;
			$rateplanIds = array();
			
			$productRatePlanId = null;
		

			if($result->result->size==1) {
				$rateplanIds[] = " RatePlanId = '".$rateplans->Id."' ";
				$productRatePlanId = $rateplans->ProductRatePlanId;
			}
			else {
				for($i=0;$i<count($rateplans);$i++) {
					$rateplanIds[] = " RatePlanId = '".$rateplans[$i]->Id."' ";
					//$rateplanIds[$i]->RatePlanCharge = array();
				}
			}
			
			$messages['rateplans'] = $rateplans;

			/*

			// get RatePlanCharge

			$result = zQuery("select Id,AccountingCode,ChargeModel,ChargeType,Description,IncludedUnits,Name,Quantity,TriggerEvent,UOM,RatePlanId from RatePlanCharge where ".implode(" or ",$rateplanIds));
			
			
			if($result->result->size>0) {


				$rateplanCharges =  $result->result->records;
				$rateplanChargeIds = array();

				if($result->result->size==1) {
					$rateplanChargeIds[] = " RatePlanChargeId = '".$rateplanCharges->Id."' ";
				}
				else {
					for($i=0;$i<count($rateplanCharges);$i++) {
						$rateplanChargeIds[] = " RatePlanChargeId = '".$rateplanCharges[$i]->Id."' ";
						//$rateplanChargeIds[$i]->RatePlanChargeTier = array();
					}
				}

				$messages['rateplanCharges'] = $rateplanCharges;

				if(count($rateplanChargeIds)>0) {

					$result = zQuery("select Id,EndingUnit,Price,PriceFormat,RatePlanChargeId,StartingUnit,Tier from RatePlanChargeTier where ".implode(" or ",$rateplanChargeIds));

					$rateplanChargeTiers = $result->result->records;
				}

				$messages['rateplanChargeTiers'] = $rateplanChargeTiers;
			}

			*/
			
			//get the product detail
			if($_REQUEST['sid']) { // subscription id
				if($productRatePlanId==null) {
					addErrors(null,'can not find ProductRatePlanId');
					return;
				}
				
				$result = zQuery("select id, ProductId,Name from ProductRatePlan where id = '".$productRatePlanId."'");
				//$result = zQuery("select id, ProductId,Name from ProductRatePlan where ProductId = '4028e6991e4b92ba011e5dcc991d7416'");
				//rateplan id : 4028e6991e4b92ba011e5dcd4c4f7419
				$record = $result->result->records;
				debug($productRatePlanId);
				die();
				getProdcutDetail($record->ProductId);

			}
		}

	}
}

function getInvoice(){

	global $messages;

	if(!$_SESSION['contactId'] && !$_SESSION['accountId'] ) {
		addErrors(null,'please login first');
		return false;
	}

	$result = zQuery("select Id,Amount,AccountId,Balance,DueDate,InvoiceDate,InvoiceNumber,Status,TargetDate from Invoice where AccountId = '".$_SESSION['accountId']."'");
		
	$messages = $result->result->records;

}

function zQuery($q){
	global $client,$header,$messages;
	$zoql = array(
			"queryString"=>$q
	);

	debug($zoql);

	$queryWrapper = array(
		"query"=>$zoql
	);
	try {
		return $client->__soapCall("query", $queryWrapper, null, $header);
	}catch(Exception $e){
		addErrors(null,$e->faultstring);
	}
	
}

function zCreateUpdate($action,$o,$ztype) {

	global $client,$header,$messages;

	if(!is_array($o)) return null;

	$d = is_array($o[0]) ? 2 : 1;

	$zObjs = array();
	
	if($d==1) {
		$zObjs[] = new SoapVar($o, SOAP_ENC_OBJECT, $ztype, "http://object.api.zuora.com/");
	}
	else {
		for($i=0;$i<count($o);$i++) {
			$zObjs[] = new SoapVar($o[$i], SOAP_ENC_OBJECT, $ztype, "http://object.api.zuora.com/");
		}
	}

	$zo = array(
		"zObjects"=>$zObjs 
	);
	try {
		return $client->__soapCall($action, $zo, null, $header); 
	}catch(Exception $e){
		addErrors(null,$e->faultstring);
	}
}



function debug($a) {
	global $debug ;
	if($debug) {
		echo "/*";
		var_dump($a);
		echo "*/";
	}
}

function test(){
	//$result = zQuery("select Id, Name,Description,EffectiveEndDate,EffectiveStartDate,ProductId from ProductRatePlan");
	$result = zQuery("select Id from ProductRatePlanCharge");
	debug($result);

	$ProductRatePlans = $result->result->records;

	//$result = zQuery("select Id, AmendmentId,AmendmentSubscriptionRatePlanId,AmendmentType,ProductRatePlanId,SubscriptionId from RatePlan");
	$result = zQuery("select Id,ProductRatePlanChargeId from RatePlanCharge");
	debug($result);

	$RatePlans = $result->result->records;

	for($i=0;$i<count($ProductRatePlans);$i++){
		$record1 = $ProductRatePlans[$i];
		for($j=0;$j<count($RatePlans);$j++){
			$record2 = $RatePlans[j];
			//echo $i.'..........';
			if($record1->Id == $record2->ProductRatePlanChargeId) {
				
				echo "****".$record1->Id."****";
			}
		}
	}

	
}

function output(){
	global $errors,$messages;
	$msg = array();
	$msg['login']=false;
	if(count($errors)>0) {
		debug($errors);
		$msg['success'] = false;
		$msg['msg'] = $errors;
	}
	else {
		$msg['success'] = true;
		if(!is_array($messages)) $messages = array($messages);
		$msg['msg'] = $messages;
	}
	if($_SESSION['accountId']){
		$msg['login']= true;
	}
	
	debug($msg);
	
	echo json_encode($msg);

}

output();
?>