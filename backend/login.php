<?php

//add your Z-Commerce Platform user name and password here
require_once("config.php");

$client = new SoapClient("zuora.a.38.0.wsdl",array('trace'=>true));
$client->__setLocation($endpoint);

try {

   $header = login($client, $username, $password, $debug);
   //var_dump($header);

} catch (Exception $e) {
   var_dump($e);
   die();
}

function login($client, $username, $password, $debug){

   # do the login
   $login = array(
   		"username"=>$username,
   		"password"=>$password
   );

   $result = $client->login($login);
   //if ($debug) var_dump($result);

   $session = $result->result->Session;

   $endpoint = $result->result->ServerUrl;
   //print "\nSession: " . $session;
   //print "\nServerUrl: " . $endpoint;

   # set the authentication
   $sessionVal = array('session'=>$session);
   $header = new SoapHeader('http://api.zuora.com/',
					'SessionHeader',
                    $sessionVal);

   return $header;
}

?>