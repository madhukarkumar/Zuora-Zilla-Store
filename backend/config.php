<?php

/*
 *    Copyright (c) 2010 Zuora, Inc.
 *    
 *    Permission is hereby granted, free of charge, to any person obtaining a copy of 
 *    this software and associated documentation files (the "Software"), to use copy, 
 *    modify, merge, publish the Software and to distribute, and sublicense copies of 
 *    the Software, provided no fee is charged for the Software.  In addition the
 *    rights specified above are conditioned upon the following:
 *    
 *    The above copyright notice and this permission notice shall be included in all
 *    copies or substantial portions of the Software.
 *    
 *    Zuora, Inc. or any other trademarks of Zuora, Inc.  may not be used to endorse
 *    or promote products derived from this Software without specific prior written
 *    permission from Zuora, Inc.
 *    
 *    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *    FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 *    ZUORA, INC. BE LIABLE FOR ANY DIRECT, INDIRECT OR CONSEQUENTIAL DAMAGES
 *    (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *    LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *    ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *    SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/* NOTE: To use Product select pages, tenant must include a custom Product field: Classification__c, with products deisgnated as "Base Product" or "Add-On Product"  */
$username = 'REPLACE WITH YOUR ZUORA API USERID';
$password = 'REPLACE WITH YOUR ZUORA API PASSWORD';

//REPLACE WITH ZUORA PROD ENDPOINT AND WSDL FILE FROM YOUR TENANT
$endpoint = 'https://apisandbox.zuora.com/apps/services/a/38.0';

//REPLACE THE FOLLOWING WITH THE DETAILS FROM YOUR ZUORA TENANT'S Z HOSTED PAYMENT PAGE
$pageId = "REPLACE";
$tenantId = "REPLACE";
$apiSecurityKey = "REPLACE"; //get your API security key from the HPM listing page


$appUrl = "https://apisandbox.zuora.com";


//REPLACE WITH YOUR SALESFORCE ORG ID DETAILS
$SfdcUsername = "REPLACE WITH SALESFORCE USER NAME";
$SfdcPassword = "REPLACE WITH SALESFORCE USER PASSWORD";
$SfdcSecurityToken="REPLACE WITH SALESFORCE API TOKEN";
//MK creds end

$usernameSRC = 'xxxx';
$passwordSRC = 'xxxx';
$endpointSRC = 'https://apisandbox.zuora.com/apps/services/a/38.0';

$debug=true;
$query_batch_size = 2000;
$wsdl='zuora.a.38.0.wsdl';//REPLACE WITH THE FILE NAME OF THE WSDL THAT OU DOWNLOADED FROM YOUR TENANT



?>