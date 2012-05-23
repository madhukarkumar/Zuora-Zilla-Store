<?php 
	session_start();

	require_once 'lib/API.php';
	require_once 'backend/config.php';
	require_once 'ecomFunctions.php';

	//Contact info: WorkEmail, FirstName, lastName, Phone, Country, Address1, City, State, Postal Code
	//Account Info: Name
	//Payment Method: Card Type: Card Holder Name, Card Mask Number
	//Invoice: Balance, InvoiceNumber, Amount, InvoiceDate, DueDate

	if(!isset($_SESSION['accountId'])){
		header("Location: selectBaseProducts.php");
		return;		
	}
	$userResult = getUserInfo($_SESSION['accountId']);

	if($userResult['success']){
		$contactInfo = $userResult['contact'];
		$accountInfo = $userResult['account'];
		$paymentInfo = $userResult['payment'];
		$invoiceInfo = $userResult['invoice'];
		$subscriptionInfo = $userResult['subscription'];

		$productInfo = getProductInfo($subscriptionInfo->Id);
	} else {
		header("Location: selectBaseProducts.php");
		return;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>zuora</title>
<link href="css/layout.css" rel="stylesheet" type="text/css" />
<link href="css/layout.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.js" /></script>
<script type="text/javascript" src="js/jquery.cookie.js" /></script>
<script type="text/javascript" src="js/function.js" /></script>
<link type="text/css" rel="stylesheet" href="css/style.css">
<link type="text/css" rel="stylesheet" href="css/karma-teal-grey.css">
<script type="text/javascript">var SlideDeckLens={};</script><script type='text/javascript' src='http://sigmaestimates.com/wp-includes/js/jquery/jquery.js?ver=1.7.1'></script>
<link href="css/nav.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="wrapper">
<div id="header">

<div class="top-block">
<div class="top-holder">

      <div class="toolbar-left">  
  <ul class="sf-js-enabled">
  <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5172" id="menu-item-5172"><a href="http://sigmaestimates.com/">Home</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4987" id="menu-item-4987"><a href="http://sigmaestimates.com/blog/">Blog</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5192" id="menu-item-5192"><a href="http://sigmaestimates.com/partners/">Partners</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4993" id="menu-item-4993"><a href="http://sigmaestimates.com/news/">News &amp; Events</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4988" id="menu-item-4988"><a href="http://sigmaestimates.com/contact/">Contact Us</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4994" id="menu-item-4994"><a href="http://sigmaestimates.com/support/">Support</a></li>
  </ul>
  </div><!-- end toolbar-left -->

    
  
</div><!-- end top-holder -->
</div><!-- end top-block -->




<div class="header-holder">
<div class="rays">
<div class="header-area">

<a class="logo" href="selectBaseProducts.php"><img alt="Sigma Estimates" src="images/acme-logo.png"></a>



<ul id="menu-main-nav">
<li class="menu-item menu-item-type-custom menu-item-object-custom parent" id="item-5237"><a href="http://sigmaestimates.com/about-us/"><span><strong>About Us</strong></span></a>
<div class="drop" style="display: block; height: 0px; overflow: hidden; opacity: 0;"><div class="c"><ul class="sub-menu" style="display: block; left: 0px;">
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5240"><a href="http://sigmaestimates.com/about-us/overview/"><span>Overview</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5243"><a href="http://sigmaestimates.com/about-us/investors/"><span>Investors</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5365"><a href="http://sigmaestimates.com/about-us/board-of-directors/"><span>Board Of Directors</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5239"><a href="http://sigmaestimates.com/about-us/career/"><span>Careers</span></a></li>
</ul></div></div>
</li>
<li class="menu-item menu-item-type-custom menu-item-object-custom parent" id="item-5484"><a><span><strong>Products</strong></span></a>
<div class="drop" style="display: none; height: 0px; overflow: hidden; opacity: 0;"><div class="c"><ul class="sub-menu" style="display: block; left: 0px;">
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5245"><a href="http://sigmaestimates.com/products/overview/"><span>Overview</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5247"><a href="http://sigmaestimates.com/products/sigma-personal/"><span>Sigma Personal</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5279"><a href="http://sigmaestimates.com/products/sigma-professional/"><span>Sigma Professional</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5246"><a href="http://sigmaestimates.com/products/sigma-enterprise/"><span>Sigma Enterprise</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5278"><a href="http://sigmaestimates.com/products/database/"><span>Database</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5277"><a href="http://sigmaestimates.com/products/mobile/"><span>Mobile</span></a></li>
</ul></div></div>
</li>
<li class="menu-item menu-item-type-post_type menu-item-object-page parent" id="item-4984"><a href="http://sigmaestimates.com/features/"><span><strong>Solutions</strong></span></a>
<div class="drop" style="display: none; height: 0px; overflow: hidden; opacity: 0;"><div class="c"><ul class="sub-menu" style="display: block; left: 0px;">
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5589"><a href="http://sigmaestimates.com/products/benefits-of-sigma-estimates/"><span>Benefits to Your Company</span></a></li>
</ul></div></div>
</li>
<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5178"><a href="http://sigmaestimates.com/testimonials/"><span><strong>Features</strong></span></a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-4983"><a href="http://sigmaestimates.com/pricing/"><span><strong>Pricing</strong></span></a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom" id="item-5516"><a href="http://sigmaestimates.com/store/"><span><strong>Purchase</strong></span></a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom parent" id="item-5747"><a><span><strong>Resources</strong></span></a>
<div class="drop" style="display: none; height: 0px; overflow: hidden; opacity: 0;"><div class="c"><ul class="sub-menu" style="display: block; left: 0px;">
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5375"><a href="http://sigmaestimates.com/resources/use-cases/"><span>Customer Success Stories</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5376"><a href="http://sigmaestimates.com/resources/training/"><span>Training</span></a></li>
	<li class="menu-item menu-item-type-post_type menu-item-object-page" id="item-5374"><a href="http://sigmaestimates.com/resources/webinar/"><span>Webinars</span></a></li>
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
<div id="web_main">
<div id="top">
	<script type="text/javascript">
		login_type();
	</script>
</div>
<div id="main" style=" text-align:left;">
	<h1><span id="h1_firstname"></span> <span id="h1_lastname"></span>, thank you for your order!</h1>
	<div style="padding:12px;">
		<p id="invoicebalance_area" style="display:none;" class="billing_balance"><strong>Your current balance is <span id="invoice_balance" class="font-red font-24"></span></strong></p>
		<div class="home_colum_2 clear-block">
			<div class="home_colum_2_1">
				<div id="invoice_block" style="display:show;">
					<div class="block clear-block">
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr>
								<td>
									<table  cellpadding="0" cellspacing="0" border="0" width="100%">
										<tr>
											<td colspan="2">Your last invoice is <strong id="invoice_number" class="font-red"></strong>, total amount is <strong id="invoice_amount" class="font-red"></strong></td>
										</tr>
										<tr>
											<td width="50%"><span class="font-gray">Invoice Date:</span> <span id="invoice_date" class="font-red"></span></td>
											<td><span class="font-gray">Due Date:</span> <span id="due_date" class="font-red"></span></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
					<br class="clear" />
				</div>
				<div class="block-gray clear-block" style="display:show; text-align:left">
					<h4>Current Plan</h4>
					<table style="display:show;" cellpadding="0" cellspacing="0" border="0" width="100%" class="table_style_3" id="plan_fb">
						<tr class="title">
							<td width="250"><strong>Item Description</strong></td>
							<td width="121"><strong>Price</strong></td>
							<td width="123"><strong>#</strong></td>
						</tr>
<?php
foreach($productInfo['rateplans'] as $rateplan){
?>
						<tr class="block">
							<td colspan="4" align="left"><strong class="font-red font-14"><?= $rateplan['name'] ?></strong></td>
						</tr>
<?php
	foreach($rateplan['prices'] as $charge){
?>
						<tr>
							<td align="left" "><?= $charge['name'] ?></td>
						  <td align="left"><span><?= $charge['price'] ?></span></td>
							<td align="left"><span><?= $charge['quantity']?></span></td>
						</tr>
<?php
	}
}
?>
					</table>
				</div>
			</div>
			<div class="home_colum_2_2">
				<div class="info_block">
					<h4 class="font-red">Your information</h4>
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr>
							<td width="150"><span class="font-gray">Email Address:</span></td>
							<td><strong id="email"></strong></td>
						</tr>
						<tr>
							<td><span class="font-gray">First Name:</span></td>
							<td><strong id="firstname"></strong></td>
						</tr>
						<tr>
							<td><span class="font-gray">Last Name:</span></td>
							<td><strong id="lastname"></strong></td>
						</tr>
						<tr>
							<td><span class="font-gray">Phone:</span></td>
							<td><strong id="phone"></strong></td>
						</tr>
					</table>
				</div>
				
				<div class="info_block">
					<h4 class="font-red">Your company</h4>
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr>
							<td width="150"><span class="font-gray">Company:</span></td>
							<td id="company"></td>
						</tr>
						<tr>
							<td><span class="font-gray">Country:</span></td>
							<td id="country"></td>
						</tr>
						<tr>
							<td><span class="font-gray">Address:</span></td>
							<td id="address"></td>
						</tr>
						<tr>
							<td><span class="font-gray">City:</span></td>
							<td id="city"></td>
						</tr>
						<tr>
							<td><span class="font-gray">State:</span></td>
							<td id="state"></td>
						</tr>
						<tr>
							<td><span class="font-gray">Postal Code:</span></td>
							<td id="postal_code"></td>
						</tr>
					</table>
				</div>
				
				<div class="info_block">
					<h4 class="font-red">Payment Method</h4>
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr>
							<td width="150"><span class="font-gray">Payment Type:</span></td>
							<td id="payment_type"></td>
						</tr>
						<tr>
							<td><span class="font-gray">Credit Card Name:</span></td>
							<td><strong id="pay_cardname"></strong></td>
						</tr>
						<tr>
							<td><span class="font-gray">Credit Card Number:</span></td>
							<td><strong id="pay_account"></strong></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
<div id="footer">
<div class="footer-area">
<div class="footer-wrapper">
<div class="footer-holder">

<div class="one_fourth">				<h3>Latest from the blog</h3>		<ul>
				<li><a title="Welcome to Sigma Estimates Blog!" href="http://sigmaestimates.com/sigma-blog-is-coming-soon/">Welcome to Sigma Estimates Blog!</a></li>
				</ul>
		</div><div class="one_fourth"><h3>Sigma Estimates Products</h3><ul class="sub-menu"><li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5328" id="menu-item-5328"><a href="http://sigmaestimates.com/products/sigma-personal/">Sigma Personal</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5326" id="menu-item-5326"><a href="http://sigmaestimates.com/products/sigma-professional/">Sigma Professional</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5327" id="menu-item-5327"><a href="http://sigmaestimates.com/products/sigma-enterprise/">Sigma Enterprise</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5330" id="menu-item-5330"><a href="http://sigmaestimates.com/products/database/">Database</a></li>
<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5329" id="menu-item-5329"><a href="http://sigmaestimates.com/products/mobile/">Mobile</a></li>
</ul></div><div class="one_fourth"><h3>Follow Us		</h3>		



<ul class="social_icons">
<li><a class="rss" onclick="window.open(this.href);return false;" href="http://sigmaestimates.com/feed/">rss</a></li>
	
<li><a onclick="window.open(this.href);return false;" class="twitter" href="http://twitter.com/#!/sigmaestimates">Twitter</a></li>
</ul>
<br>


		</div><div class="one_fourth_last"><h3>Contact Us</h3>			<div class="textwidget"><p>Sigma Estimates</p>
<p><a target="_blank" href="http://maps.google.com/maps?q=401+edgewater+place,Wakefield,+MA+01880&amp;hl=en&amp;sll=42.523761,-71.041&amp;sspn=0.012525,0.027874&amp;gl=us&amp;hnear=401+Edgewater+Pl,+Wakefield,+Massachusetts+01880&amp;t=m&amp;z=16">401 edgewater place<br>
Suite 105<br>
Wakefield, MA 01880<br>
</a></p>
<p><a href="mailto:info@sigmaestimates.com">info@sigmaestimates.com </a></p>
<p>Toll Free: 1-855-378-2679<br>
Office:781-224-5601<br>
Fax: 781-246-1967</p>
</div>
		</div>

</div><!-- footer-holder -->
</div><!-- end footer-wrapper -->
</div><!-- end footer-area -->
</div>
<div id="footer_bottom">
  <div class="info">
      <div id="foot_left">&nbsp;Copyright			<div class="textwidget"><p>Copyright &copy; 2012 Sigma Estimates. All rights reserved.</p>
</div>
	</div><!-- end foot_left -->
      <div id="foot_right"><div class="top-footer"><a class="link-top" href="#">top</a></div>

<ul>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-4469" id="menu-item-4469"><a>Home</a></li>
<li class="menu-item menu-item-type-custom menu-item-object-custom menu-item-5167" id="menu-item-5167"><a href="http://sigmaestimates.com/wp-admin">Admin</a></li>
</ul>
      
		
                    
      </div><!-- end foot_right -->
  </div><!-- end info -->
</div>
<script type='text/javascript' src='http://sigmaestimates.com/wp-content/themes/Karma/truethemes_framework/js/truethemes.js?ver=2.0'></script>
<script type="text/javascript">

var enter_message = function(){
	$("#email").html("<?= $contactInfo->WorkEmail?>");
	$("#firstname").html("<?= $contactInfo->FirstName ?>");
	$("#h1_firstname").html("<?= $contactInfo->FirstName ?>");
	$("#lastname").html("<?= $contactInfo->LastName ?>");
	$("#h1_lastname").html("<?= $contactInfo->LastName ?>");
	
	$("#phone").html("<?= $contactInfo->WorkPhone ?>");
	$("#company").html("<?= $accountInfo->Name ?>");
	$("#country").html("<?= $contactInfo->Country ?>");
	$("#address").html("<?= $contactInfo->Address1 ?>");
	$("#city").html("<?= $contactInfo->City ?>");
	$("#state").html("<?= $contactInfo->State ?>");
	$("#postal_code").html("<?= $contactInfo->PostalCode ?>");
	if("<?= $paymentInfo->CreditCardType ?>" == "MasterCard"){
		$("#payment_type").html('<img style="vertical-align: middle;" src="images/mastercard_small_ico.gif" /> <strong><?= $paymentInfo->CreditCardType ?></strong>');
	}
	if("<?= $paymentInfo->CreditCardType ?>" == "Visa"){
		$("#payment_type").html('<img style="vertical-align: middle;" src="images/visa_small_ico.gif" /> <strong><?= $paymentInfo->CreditCardType ?></strong>');
	}
	if("<?= $paymentInfo->CreditCardType ?>" == "American Express"){
		$("#payment_type").html('<img style="vertical-align: middle;" src="images/american_express_small_ico.gif" /> <strong><?= $paymentInfo->CreditCardType ?></strong>');
	}
	$("#pay_cardname").html("<?= $paymentInfo->CreditCardHolderName ?>");
	$("#pay_account").html("<?= $paymentInfo->CreditCardMaskNumber ?>");
	
	$("#allinvoice_link").attr("href","user_billing_data.html?firstname="+escape("<?= $contactInfo->FirstName ?>")+"&lastname="+escape("<?= $contactInfo->LastName ?>"));

	if("<?= $invoiceInfo==null ? 0 : 1 ?>" == "0"){
		$("#invoice_block").css("display","none");
		$("#invoicebalance_area").css("display","none");
	} else {
		$("#invoice_block").css("display","block");
		$("#invoicebalance_area").css("display","block");
		$("#invoice_balance").html("$<?= $invoiceInfo!= null ? $invoiceInfo->Balance : "" ?>");
		$("#invoice_number").html("<?= $invoiceInfo!= null ? $invoiceInfo->InvoiceNumber : "" ?>");
		$("#invoice_amount").html("$<?= $invoiceInfo!= null ? $invoiceInfo->Amount : "" ?>");
		$("#invoice_date").html("<?= $invoiceInfo!= null ? substr($invoiceInfo->InvoiceDate,0,10) : "" ?>");
		$("#due_date").html("<?= $invoiceInfo!= null ? substr($invoiceInfo->DueDate,0,10) : "" ?>");
	}


/*
	if($.cookie('productName')){
		$.cookie('plan_state','complete');
	}
	var plan_state_text = $.cookie('plan_state');
	if(plan_state_text == "complete"){
		$("#username").html($.cookie('uomname'));
		$("#productName").text($.cookie('productName'));
		$("#planName").html($.cookie('planName'));
		$("#productFee").html($.cookie('productFee'));
		$("#productEachPrice").html($.cookie('productPrice'));
		$("#username").html($.cookie('uomname'));
		$("#uomNum").html($.cookie('uomnum'));
		$("#total_num").html($.cookie('totalnum'));
		$.cookie('plan_state','close');
		
		$("#plan_fb").css("display","none");
		$("#plan_area").css("display","");
	}else{
		
		$("#plan_fb").css("display","");
		$("#plan_area").css("display","show");
		
	}
*/
}
enter_message();
</script>
</body>
</html>