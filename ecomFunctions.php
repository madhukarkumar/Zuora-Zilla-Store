<?php

global $instance;

$config = new stdClass();
$config->wsdl = $wsdl;

$instance = Zuora_API::getInstance($config);
$instance->setQueryOptions($query_batch_size);

# LOGIN
$instance->setLocation($endpoint);
$instance->login($username, $password);

// Sfdc Credentials
define("USERNAME", $SfdcUsername);
define("PASSWORD", $SfdcPassword);
define("SECURITY_TOKEN", $SfdcSecurityToken);


function getProductsOfClassification($classification){
	global $instance;

    $result = $instance->query("select Id,Name,SKU,Description from Product where classification__c='".$classification."'");
    if ($result->result->size == 0){
        print "No Products found!";
        exit();
    }
    $products = $result->result->records;

    if(count($products)==1) $products = array($products);
	if(count($products)>1)usort($products,'cmpProd');

    return $products;
}

function getProductRatePlanMap($products){
	global $instance;

	$ratePlanMap = array();

	foreach($products as $p){	
	    $result = $instance->query("select Id,Name,Description from ProductRatePlan where ProductId='".$p->Id."'");
	    if ($result->result->size == 0){
	        return null;
	    }
	    $rateplans = $result->result->records;
  		if(count($rateplans)==1) $rateplans = array($rateplans);
		if(count($rateplans)>1)usort($rateplans,'cmpRp');
	    $ratePlanMap[$p->SKU] = $rateplans;
	}

    return $ratePlanMap;
}

function getRatePlanNameById($rpId){
	global $instance;

    $result = $instance->query("select Id,Name from ProductRatePlan where Id='".$rpId."'");
    if ($result->result->size == 0){
        return null;
    }
    $ratePlans = $result->result->records;

    if(count($ratePlans)!=1) return null;

    return $ratePlans->Name;
}

/*   
 *   getRatePlanDispalyPrice($rateplanId)
 *   Currently supports Fixed Fee and Per Unit
 *   Assumes one charge per rate plan an$d-> tier = per charge.
 *   Displays USD currency.
 */

function getRatePlanDisplayPrices($rpId){
	global $instance;

	$displayCurrency = "USD";

    $result = $instance->query("select Id,ChargeType,ChargeModel,UOM,BillingPeriod from ProductRatePlanCharge where ProductRatePlanId='".$rpId."'");

    if ($result->result->size == 0){
        return "No Charges";
    }
    $charges = $result->result->records;

    $priceResults = array();

  	if(count($charges)==1) $charges = array($charges);
	if(count($charges)>1)usort($charges,'cmpRpc');

    foreach($charges as $charge){
	    $uom = $charge->UOM;
	    $chargeModel = $charge->ChargeModel;
	    $chargeType = $charge->ChargeType;
	    $billingFrequency = $charge->BillingPeriod;
	    $amt;

	    $result = $instance->query("select Id,Price,Currency from ProductRatePlanChargeTier where ProductRatePlanChargeId='".$charge->Id."' AND Currency='".$displayCurrency."'");
	    
		$displayPrice = "";

	    if ($result->result->size == 1){
		    $tiers = $result->result->records;
		    $amt = $tiers->Price;
		    if($chargeModel=="Flat Fee Pricing"){
			    $displayPrice .= "$" . number_format($tiers->Price, 2, '.', ',') . " " . $tiers->Currency;	    	
		    } else if($chargeModel=="Per Unit Pricing"){
			    $displayPrice .= "$" . number_format($tiers->Price, 2, '.', ',') . " " . $tiers->Currency;
		    	$displayPrice .= " / " . $uom;
			}
		    if($chargeType!="OneTime"){
		    	$displayPrice .= " / " . $billingFrequency;
			} else {
				$displayPrice .= " (One Time)";
			}
	    } else {
		    // Handle pricing models with multiple tiers (Volume Pricing, Tiered Pricing)
		    // ...
	    }
	    array_push($priceResults, array("model"=>$chargeModel, "price"=>$displayPrice, "amt"=>$amt));
	}

    return $priceResults;
}

function getFirstPrice($priceResult, $quantity){
	if($priceResult['model'] == 'Flat Fee Pricing'){
		return $priceResult['amt'];
	} else if($priceResult['model'] == 'Per Unit Pricing'){
		return $quantity * $priceResult['amt'];
	}
}

function cmpProd( $a, $b )
{
  if( $a->SKU ==  $b->SKU){ return 0 ; } 
  return ($a->SKU < $b->SKU) ? -1 : 1;
}
function cmpRp( $a, $b )
{
  if( $a->Name ==  $b->Name){ return 0 ; } 
  return ($a->Name < $b->Name) ? -1 : 1;
}
function cmpRpc( $a, $b )
{
  if( $a->ChargeType ==  $b->ChargeType){ return 0 ; } 
  return ($a->ChargeType < $b->ChargeType) ? -1 : 1;
}
function cmpInv( $a, $b )
{
  if( $a->InvoiceDate ==  $b->InvoiceDate){ return 0 ; } 
  return ($a->InvoiceDate > $b->InvoiceDate) ? -1 : 1;
}




function makeZuoraSubscription($crmId, $email, $firstName, $lastName, $phone, $company, $country, 
		$address1, $address2, $city, $state, $postalcode, $paymentmethodid, $ratePlans){
	global $instance;
	global $username;
	global $password;
	global $endpoint;

	$subscriptionId = null;	

	$date = date('Y-m-d\TH:i:s',time());
	$today = getdate();
	$mday = $today['mday'];

	try {
			
		//Set up account
		$account = new Zuora_Account();
		
		//*
		$account->AutoPay = 0;
		$account->CrmId = $crmId;
		$account->Currency = "USD";
		$account->Name = $email;
		$account->PaymentTerm = "Net 30";
		$account->Batch = "Batch1";
		$account->BillCycleDay = $mday;
		$account->Status = "Active";
		
		//Set up Payment Method
		$pm = new Zuora_PaymentMethod();
		$pm->Id = $paymentmethodid;
		
		//Set up contact
		$contact = new Zuora_Contact();
		$contact->Address1 = $address1;
		$contact->City = $city;
		$contact->Country = $country;
		$contact->FirstName = $firstName;
		$contact->LastName = $lastName;
		$contact->PostalCode = $postalcode;
		$contact->State = $state;
		$contact->WorkEmail = $email;
		$contact->WorkPhone = $phone;
		//*/

		//Set up subscription
		$sub = new Zuora_Subscription();
		$sub->AutoRenew = 0;
		$sub->InitialTerm = 12;
	    $sub->RenewalTerm = 12;
	    $sub->ContractEffectiveDate = $date;
	    $sub->ServiceActivationDate = $date;
	    $sub->ContractAcceptanceDate = $date;
		$sub->TermStartDate = $date;
		$sub->TermType = "TERMED";
		$sub->Status = "Active";
		$sub->Currency = "USD";
		$sub->AutoRenew = 1;
		$sub->InitialTerm = 12;
		$sub->RenewalTerm = 12;

		//Set up subscription data
		$sdata = new Zuora_SubscriptionData($sub);

		//Add Rate Plans
		foreach($ratePlans as $ratePlan){
			//Set up RatePlan
			$rp = new Zuora_RatePlan();
			$rp->ProductRatePlanId = $ratePlan['id'];

			//Set up RatePlanData
			$rpd = new Zuora_RatePlanData($rp);

			//For rate plans with quantity, set all Per Unit charges on the plan to this quantity
			$qty = $ratePlan['qty'];
			$result = $instance->query("select Id,ChargeModel,DefaultQuantity,Name from ProductRatePlanCharge where ProductRatePlanId='".$ratePlan['id']."'");



		    if ($result->result->size == 0){
		    	//No charges on this plan.
		    } else {
		    	if($result->result->size > 1){
				    $rateplancharges = $result->result->records;
				} else {
					$rateplancharges = array($result->result->records);
				}
		    	foreach($rateplancharges as $rc){
		    		if($rc->ChargeModel=='Per Unit Pricing'){
				 		$rpc = new Zuora_RatePlanCharge();
					    $rpc->ProductRatePlanChargeId = $rc->Id;

					    if($qty != null && $qty != ""){
						    $rpc->Quantity = $ratePlan['qty'];   	
					    } else {
					    	$rpc->Quantity = $rc->DefaultQuantity;
					    }

					    $rpcd = new Zuora_RatePlanChargeData($rpc);
					    $rpd->addRatePlanChargeData($rpcd); 
					}
		    	}
	    	}
			$sdata->addRatePlanData($rpd);			
		}

		//Set up subscribeoptions
		$so = new Zuora_SubscribeOptions(1,1);

		$subRes = $instance->subscribe($account, $sdata, $contact, $pm, $so);
//		$subRes = $instance->subscribe($account, $sdata, $contact, null, $so);

		$success = $subRes->result->Success;
		$subId = $subRes->result->SubscriptionId;

	    $accountId = null;
	    $contactId = null;
		if($success){
			$result = $instance->query("select accountId from Subscription where Id='".$subId."'");
		    $sub = $result->result->records;
		    $accountId = $sub->AccountId;
			$result = $instance->query("select BillToId from Account where Id='".$accountId."'");
		    $account = $result->result->records;
	    	$contactId = $account->BillToId;
		} else {
			rollbackSfdcAccount($crmId);
		}
		$dispMsg = ($success ? 
			'Subscription created!' : 
			$subRes->result->Errors->Message);
		$subscribeResult = array("success"=> $success, "message"=>$dispMsg, "accountId"=>$accountId, "contactId"=>$contactId);

	} catch (Exception $e) {
		rollbackSfdcAccount($crmId);
		$subscribeResult = array("success"=>false, "message"=>$e);
	}
	return $subscribeResult;
}

function createSfdcAccount($accountName){
	require_once ('soapclient/SforceEnterpriseClient.php');

	//$mySforceConnection = new SforceEnterpriseClient();
	//$mySforceConnection->createConnection("soapclient/enterprise.wsdl.xml");
	//$mySforceConnection->login(USERNAME, PASSWORD.SECURITY_TOKEN);
	//Begin SFDC connection check
		$loginResult = null;
		  try {
		   $mySforceConnection = new SforceEnterpriseClient();
		$mySforceConnection->createConnection("soapclient/enterprise.wsdl.xml");
		$loginResult = $mySforceConnection->login(USERNAME, PASSWORD.SECURITY_TOKEN);
		  } catch (Exception $e) {
		   echo "error connecting to salesforce.com!";
		   echo $e->faultstring;
		  }

	//End SFDC check
	$records = array();

	$records[0] = new stdclass();
	$records[0]->Name = $accountName;

	$response = $mySforceConnection->create($records, 'Account');

	foreach ($response as $i => $result) {
	    $success = $result->id!=null?true:false;
		$msg = $success ? 
			"Account created at " . $result->id : 
			"Failed to make account";
		$result = array("success"=>$success, "id"=>$result->id, "message"=>$msg);
		return $result;
	}
}

function rollbackSfdcAccount($crmId){
	require_once ('soapclient/SforceEnterpriseClient.php');

	$mySforceConnection = new SforceEnterpriseClient();
	$mySforceConnection->createConnection("soapclient/enterprise.wsdl.xml");
	$mySforceConnection->login(USERNAME, PASSWORD.SECURITY_TOKEN);

	$ids = array($crmId);

	// $ids is an array of record ids built in a previous step
	$response = $mySforceConnection->delete($ids);

	$result = array("message"=>'Salesforce Account deleted.');

	return $result;
}

function userLogin($contactId){
	global $instance;
	
	$result = $instance->query("select Id, AccountId,LastName,FirstName,WorkPhone,Country,Address1,Address2,City,State,PostalCode from Contact where Id='".$contactId."'");

	if($result->result!=null && $result->result->size==1) {
		$record = $result->result->records;
		$_SESSION['accountId']=$record->AccountId;
		$_SESSION['contactId']=$record->Id;
		$_SESSION['contactInfo'] = $record;

		$result = $instance->query("select Id,Name from Account where Id = '".$record->AccountId."'");
		$record = $result->result->records;
		$_SESSION['accountInfo'] = $record;
	}
}

function getUserInfo($accountId){
	global $instance;

	//Contact info: WorkEmail, FirstName, lastName, Phone, Country, Address1, City, State, Postal Code
	//Account Info: Name, BillToId, DefaultPaymentMethod
	//Payment Method: Card Type: Card Holder Name, Card Mask Number
	//Invoice: Balance, InvoiceNumber, Amount, InvoiceDate, DueDate

	$contactInfo = null;
	$accountInfo = null;
	$paymentInfo = null;
	$invoiceInfo = null;
	$subscriptionInfo = null;


	//Get Account
	$result = $instance->query("select Id, Name, DefaultPaymentMethodId, BillToId from Account where Id ='".$accountId."'");
	if($result->result!=null && $result->result->size==1) {
		$record = $result->result->records;
		$accountInfo = $record;
	}
	if($accountInfo!=null){
		//Get Contact
		$result = $instance->query("select Id, AccountId,LastName,FirstName,WorkPhone,Country,Address1,Address2,City,State,PostalCode,WorkEmail from Contact where Id ='".$accountInfo->BillToId."'");
		if($result->result!=null && $result->result->size==1) {
			$record = $result->result->records;
			$contactInfo = $record;
		}
		//Get Payment
		$result = $instance->query("select Id,CreditCardHolderName,CreditCardMaskNumber,CreditCardType from PaymentMethod where Id ='".$accountInfo->DefaultPaymentMethodId."'");
		if($result->result!=null && $result->result->size==1) {
			$record = $result->result->records;
			$paymentInfo = $record;
		}
		//Get Invoices
		$result = $instance->query("select Id, Balance, InvoiceNumber, Amount, InvoiceDate, DueDate from Invoice where AccountId ='".$accountInfo->Id."'");
		if($result->result!=null && $result->result->size>0) {
			if($result->result->size==1) $records = array($result->result->records);
			else $records = $result->result->records;
			usort($records,'cmpInv');

			$record = $records[0];
			$invoiceInfo = $record;
		}
		//Get Subscription
		$result = $instance->query("select Id from Subscription where AccountId ='".$accountInfo->Id."'");
		if($result->result!=null && $result->result->size>0) {
			if($result->result->size==1) $records = array($result->result->records);
			else $records = $result->result->records;
			$record = $records[0];
			$subscriptionInfo = $record;
		}
	}
	
	if($contactInfo!=null && $accountInfo!=null && $paymentInfo!=null && $subscriptionInfo!=null){
		$success = true;
	} else {
		$success = false;
	}

	$userResult = array();
	$userResult = array("success"=>$success, "contact"=>$contactInfo, "account"=>$accountInfo, "payment"=>$paymentInfo, "invoice"=>$invoiceInfo, "subscription"=>$subscriptionInfo);
	return $userResult;
}

function getProductInfo($subId){
	global $instance;
    $rateplanInfo = array();



	$success = false;
	
	$displayCurrency = "USD";

	//Get all rate plans on this subscription.
    $result = $instance->query("select Id,Name from RatePlan where SubscriptionId='".$subId."'");
    if ($result->result->size == 0){
        return array("success"=>false, "message"=>'No rate plans.');
    }
	else if ($result->result->size == 1) $rateplans = array($result->result->records);
    else $rateplans = $result->result->records;
    foreach($rateplans as $rateplan) {

    	//For each rate plan, get all charges
	    $result = $instance->query("select Id,Name,ChargeType,ChargeModel,UOM,Price,ProductRatePlanChargeId,Quantity from RatePlanCharge where RatePlanId='".$rateplan->Id."'");
	    if ($result->result->size == 0){
	        return array("success"=>false, "message"=>'Subscription has rate plan with no charges.');
	    }
		else if ($result->result->size == 1) $charges = array($result->result->records);
	    else $charges = $result->result->records;


	    $priceResults = array();

	    foreach($charges as $charge){
	    	//For all charges, get pricing information
		    $chargeModel = $charge->ChargeModel;
		    $chargeType = $charge->ChargeType;

		    $displayQty = $charge->Quantity ? "# ".$charge->UOM."s: " . $charge->Quantity : "";

		    $result = $instance->query("select BillingPeriod from ProductRatePlanCharge where Id='".$charge->ProductRatePlanChargeId."'");
		    $billingFrequency = $result->result->records->BillingPeriod;

			$result = $instance->query("select Id,Price from RatePlanChargeTier where RatePlanChargeId='".$charge->Id."'");

			$displayPrice = "";
		    if ($result->result->size == 1){
			    $tiers = $result->result->records;
			    if($chargeModel=="Flat Fee Pricing"){
				    $displayPrice .= "$" . $tiers->Price;	    	
			    } else if($chargeModel=="Per Unit Pricing"){
				    $displayPrice .= "$" . $tiers->Price;
			    	$displayPrice .= " / " . $charge->UOM;
				}
			    if($chargeType!="OneTime"){
			    	$displayPrice .= " / " . $billingFrequency;
				} else {
					$displayPrice .= " (One Time)";
				}
		    } else {
			    // Handle pricing models with multiple tiers (Volume Pricing, Tiered Pricing)
			    // ...
		    }
		    array_push($priceResults, array("name"=>$charge->Name, "model"=>$chargeModel, "quantity"=>$displayQty, "price"=>$displayPrice));
		}
		$ratePlan = array("name"=>$rateplan->Name, "prices"=>$priceResults);
		array_push($rateplanInfo, $ratePlan);
	}

    return array("success"=>true, "message"=>'success', "rateplans"=>$rateplanInfo);

}

?>