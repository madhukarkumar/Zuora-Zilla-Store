<?php


/*
 * Gather Info
 */

require_once 'lib/API.php';
require_once 'backend/config.php';
require_once 'ecomFunctions.php';

$products = getProductsOfClassification("Base Product");
if(count($products)>1)usort($products,'cmpProd');
$ratePlanMap = getProductRatePlanMap($products);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Choose Base Products</title>
<link href="css/layout.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.js" /></script>
<script type="text/javascript" src="js/jquery.cookie.js" /></script>
<script type="text/javascript" src="js/function.js" /></script>
<link type="text/css" rel="stylesheet" href="css/style.css">
<link type="text/css" rel="stylesheet" href="css/karma-teal-grey.css">
<script type="text/javascript">var SlideDeckLens={};</script><script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'></script>
<link href="css/nav.css" rel="stylesheet" type="text/css" />
<script>
$(document).ready(function(){
	$('.rateplan_hide').click(function(){
	$(this).parents('.rateplan_menu').find('.rateplan').slideToggle();
	})
	$('.input_click').click(function(){
	$(this).parents('.product').find('.rateplan').slideToggle();
	})
	})

</script>

	<script>
      function isNumberKey(evt)
      {
         var charCode = (evt.which) ? evt.which : event.keyCode
         if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;

         return true;
      }

		function validateForm(){
			var validationPassed = true;
			var validationError = "";

			prodChosen = false;
			planChosen = true;
			qtyChosen = true;
			for(var i = 1; i <= <?= count($products) ?>; i++){
				if(document.forms['base_product_form']['BProd' + i].checked == 1){
					prodChosen = true;
					var j = 0;
					var curPlanChosen = false;
					for(var j = 0; j < document.forms['base_product_form']['BPlan' + i].length; j++){
						var curRadio = document.forms['base_product_form']['BPlan' + i].item(j);
						if(curRadio.checked){
							curPlanChosen = true;
						}
					}
					if(!curPlanChosen) planChosen = false;
					if(document.forms['base_product_form']['BQty' + i] != undefined && document.forms['base_product_form']['BQty' + i].value==""){
						qtyChosen = false;
					}
				}
			}
			if(!prodChosen){
				validationPassed = false;
				validationError += "Please select at least one product before continuing.\n";
			}
			if(!planChosen){
				validationPassed = false;
				validationError += "Please select a billing option for each selected product.\n";
			}
			if(!qtyChosen){
				validationPassed = false;
				validationError += "Please enter a quantity for each selected product.\n";
			}

			if(!validationPassed){
				alert (validationError);
				return false;
			} else {
				document.forms.base_product_form.submit();
				return false;
			}
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
  <li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-5172" id="menu-item-5172"><a href="h#">Home</a></li>
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

<a class="logo" href="index.html"><img alt="Zuora" src="images/acme-logo.png"></a>



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
<li class="menu-item menu-item-type-custom menu-item-object-custom" id="item-5516"><a href="#"><span><strong>Subscribe</strong></span></a></li>
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
	<div id="web_main" style=" text-align:center; width:100%;">
	    <form action='selectAddOns.php' method='get' id='base_product_form'>
			<div  class="edit_dv" style="width:960px; margin:0 auto; text-align:left;word-wrap : break-word; overflow-:hidden;">
				<h3>Product Catalog</h3>
				<ul class="list_dv" id="productlist">
<?php
$prodNum = 1;
foreach($products as $p){
	$rps = $ratePlanMap[$p->SKU];
?>
					<li>
						<div class="product">
							<div style="width:30px; float:left;"><input class="input_click" type='checkbox' name='BProd<?= $prodNum ?>' value='1' /></div>
							<h4><?= $p->Name ?></h4><br>
							
							<p class="product_description"><?= $p->Description ?></p>
							<p class='clear-block'></p>
                            <div class="rateplan_menu">
                             <div class="rateplan_hide">Click Here to View Billing Option Details</div>
<?php
	//Determines whether a product needs a quantity
	$productHasQuantity = false;
	$planNum = 1;
	foreach($rps as $rp){
?>
                            <div class="rateplan rateplan1">
								<input style="float:left" type='radio' name='BPlan<?= $prodNum?>' value='<?= $rp->Id?>'/>
								<h4><?= $rp->Name ?></h4>
<?php 
		$priceResults = getRatePlanDisplayPrices($rp->Id);
		foreach($priceResults as $priceResult){
			if($priceResult['model']=='Per Unit Pricing'){
				$productHasQuantity = true;
			}
			echo "								<p class='price_line'>".$priceResult['price']."</p><br>
";
		}
		$planNum++;
?>
								<p class='clear-block'></p>
                            
<?php
	}
	if($productHasQuantity){
		echo "		<p class='licenses'>Number of Licenses: <input class='shuru' type='text' onkeypress='return isNumberKey(event)' name='BQty".$prodNum."' /></p>
						</div></div></div>
					</li>
";
	}
	$prodNum++;
}
?>
				</ul>
                <div style="clear:both"></div>
				<input class="l_anniu" type='button' onClick='validateForm()' value='Next'/>
                <div style="clear:both"></div>
			</div>
		</form>
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