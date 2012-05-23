<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Browse Products Page</title>
</head>

<body>
<?php 

//add your Z-Commerce Platform user name and password here
$username = "madhukar.kumar@zuora.com";
$password = "!Zuora123456";

$client = new SoapClient("zuora.a.38.0.wsdl",array('trace'=>true));
$client->__setLocation($url);

try {

   $header = login($client, $username, $password, $debug);
   //var_dump($header);

} catch (Exception $e) {
   var_dump($e);
   die();
}
set_time_limit(200);
session_start() ;
$errors = array();
$messages = null;

dispatcher($_REQUEST['type']);

echo ($_REQUEST[type]);


function dispatcher($type){
	
	//This will get you getProducts by default if you dont specify the method name
	if ($type == null)
	$type = 'GetProducts';
	
	switch($type) {
		case 'GetProducts' : getProducts();
		break;
		case 'GetProductDetail' : getProductDetail();
		break;
		case 'test' : test();
		break;
		default : addErrors(null,'no action specified');
	}
	
}


//Fundction to get high level Products
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
		echo "<br>---------------------------------------------------Printing all Products--------------------------------------------------------</br>";
		//print_r($messages);
		
			echo "<ul>";
     			echo displayTree($messages);
     		echo "</ul>";
		echo "<br>---------------------------------------------------Printing all Prodcuts complete -----------------------------------------------</br>";
		//echo $messages[0]->Id;
		getProductDetail($messages[0]->Id);
		//getProductDetail('4028e69634172d0201341ff863e5617a');//Currently hardcoded. Change to RatePlanId from the product
	}

}

//Function to get ProductDetail and charges
function getProductDetail($pid=null){
	global $client,$header,$messages;

	$pid = $pid == null ? $_REQUEST['id'] : $pid; //product id;


	if(!$pid) {
		addErrors(null,'no product id specified');
		echo '<br> No ID Specified</br>';
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

		//echo "<br>-----------------------------------------Now Printing all ProductRatePlans------------------------------------------------</br>";
		//print_r($productRatePlans);
		//echo "<ul>";
     		//	echo displayTree($messages);
     		//echo "</ul>";
		//echo "<br>------------------------------------------Done printing ProductRatePlans---------------------------------------------------</br>";	
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
	
	echo "<br>----------------------------------------------Now Printing all ProductRatePlans with Tiers-----------------------------------------</br>";
		//print_r($productRatePlans);
		echo "<ul>";
     			echo displayTree($messages['rateplans']);
     		echo "</ul>";
	echo "<br>----------------------------------------Done printing ProductRatePlans with Tiers---------------------------------------------------</br>";	
//print_r($productRatePlans);
}

//Helper method to make a query
function zQuery($q){
	
	global $client,$header,$messages;
	$zoql = array(
			"queryString"=>$q
	);

	//debug($zoql);

	$queryWrapper = array(
		"query"=>$zoql
	);
	try {
		return $client->__soapCall("query", $queryWrapper, null, $header);
		
	}catch(Exception $e){
		addErrors(null,$e->faultstring);
		
	}
	
}

//Helper function to login
function login($client, $username, $password, $debug){

   # do the login
   $login = array(
   		"username"=>$username,
   		"password"=>$password
   );

   $result = $client->login($login);
   //if ($debug) var_dump($result);

   $session = $result->result->Session;
   $url = $result->result->ServerUrl;
   //print "\nSession: " . $session;
   //print "\nServerUrl: " . $url;

   # set the authentication
   $sessionVal = array('session'=>$session);
   $header = new SoapHeader('http://api.zuora.com/',
					'SessionHeader',
                    $sessionVal);

   return $header;
}

//Helperfunction
function displayTree($var) {
     $newline = "\n";
     foreach($var as $key => $value) {
         if (is_array($value) || is_object($value)) {
             $value = $newline . "<ul>" . displayTree($value) . "</ul>";
         }

         if (is_array($var)) {
             if (!stripos($value, "<li class=")) {
                $output .= "<li class=\"file\">" . $value . "</li>" . $newline;
             }
             else {
                $output .= $value . $newline;
             }
         
         }
         else { // is_object
            if (!stripos($value, "<li class=")) {
               $value = "<ul><li class=\"file\">" . $value . "</li></ul>" . $newline;
            } 
            
            $output .= "<li class=\"folder\">" . $key . $value . "</li>" . $newline;
         }
         
     }
     
     return $output;
}



session_destroy();

?>



</body>
</html>