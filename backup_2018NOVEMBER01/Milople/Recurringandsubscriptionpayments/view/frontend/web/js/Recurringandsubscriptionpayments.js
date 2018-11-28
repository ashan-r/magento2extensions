/**
*
* Do not edit or add to this file if you wish to upgrade the module to newer
* versions in the future. If you wish to customize the module for your
* needs please contact us to https://www.milople.com/contact-us.html
*
* @category    Ecommerce
* @package     Milople_Recurringandsubscriptionpayments
* @copyright   Copyright (c) 2017 Milople Technologies Pvt. Ltd. All Rights Reserved.
* @url         https://www.milople.com/magento2-extensions/ecurring-and-subscription-payments-m2.html
*
***/
// this function called when click on No Subscription
var global_discount_value = null;
require(["jquery"], function(jQuery) {
		fullsubscriptionChecked = function (flag) {

			if(flag)
			{
				jQuery("#ajax-load").hide(); // hide ajax refresh
				jQuery("#nosubscription").addClass("_active"); // set no subscription as active
				jQuery("#subscription").removeClass("_active"); // set subscription  as none-active
				jQuery('#allow-subscription-options').hide();
				jQuery("#milople_subscription_type").val(-1);
				jQuery('#recurring-help').remove();
								
			}else if(flag==0){
			 	var ajaxLoader = jQuery("#ajax-load");
				
				jQuery("#subscription").addClass("_active"); // set no subscription as active
				jQuery("#nosubscription").removeClass("_active"); // set subscription  as none-active
				jQuery('#allow-subscription-options').show();
				jQuery("#milople_subscription_type").val(jQuery("#milople_select_subscription_type").val());
				jQuery("#milople_subscription_type_label").val(jQuery("#milople_select_subscription_type option:selected").text());
			}
			
		}
				// Change the status of hidden field when subscription type selected.
		jQuery('select#milople_select_subscription_type').on('change', function() {
				/*jQuery("#milople_subscription_type").val(jQuery("#milople_select_subscription_type").val());
				jQuery('#recurring-help').hide();
		  	jQuery("#milople_subscription_type_label").val(jQuery("#milople_select_subscription_type option:selected").text());*/
		});
		// Get help for Recurring and Subscription on click on Subscription tab
	jQuery( document ).ready(function() {
		jQuery('#subscription').on('click',function(){
				var ajaxLoader = jQuery("#ajax-load");
				var dataUrl= jQuery(this).attr('data-url');
				ajaxLoader.show(); //display ajax  loader in recurring radio button
				ajaxLoader.addClass("rotate-me");
				var productType = document.getElementById("product_type").value;
				var symbol = document.getElementById("symbol").value;
				var getPrice = document.getElementById("product_price").value;
				var termid = document.getElementById("milople_subscription_type").value;
				//alert(termid);
				if(termid == -1){
					exit();
					termid = 1;}
				if (productType == 'grouped'){
					var productprice = 0; 
				}
				else if(productType == 'configurable'){
					var productprice = jQuery(".price-wrapper").attr('data-price-amount');
				}
					else{
					var productprice = getPrice;
				}	 
				if (termid > 0) {

						jQuery.ajax(
						{
							 url:dataUrl, 
							 type: 'Post',
							 dataType : 'json',
							 data: { termid : termid,  productPrice : productprice, productType :productType, symbol : symbol }
						}).done(function(transport) {
									jQuery('#ajax-load').css("display","none");
									jQuery('#recurring-help').remove();
									jQuery('section.subscription').append('<div id="recurring-help">'+transport.html+'</div>');					
							 }
						)
				} 
			});
		
			//get help for recurring and subscription on change of subscription type
			jQuery('#milople_select_subscription_type').on('change',function(){
				
				jQuery("#milople_subscription_type").val(jQuery("#milople_select_subscription_type").val());
		 		jQuery('#recurring-help').hide();
		  	jQuery("#milople_subscription_type_label").val(jQuery("#milople_select_subscription_type option:selected").text());
				
				var ajaxLoader = jQuery("#ajax-load");
				var dataUrl= jQuery(this).attr('data-url');
				ajaxLoader.show(); //display ajax  loader in recurring radio button
				ajaxLoader.addClass("rotate-me");
				var productType = document.getElementById("product_type").value;
				var symbol = document.getElementById("symbol").value;
				var getPrice = document.getElementById("product_price").value;
				var termid = document.getElementById("milople_subscription_type").value;
				if (productType == 'grouped'){
					var productprice = 0; 
				}
				else if(productType == 'configurable'){
					var productprice = jQuery(".price-wrapper").attr('data-price-amount');
				}
					else{
					var productprice = getPrice;
				}	 
				if (termid > 0) {

						jQuery.ajax(
						{
							 url:dataUrl, 
							 type: 'Post',
							 dataType : 'json',
							 data: { termid : termid,  productPrice : productprice, productType :productType, symbol : symbol }
						}).done(function(transport) {
									jQuery('#ajax-load').css("display","none");
									jQuery('#recurring-help').remove();
									jQuery('section.subscription').append('<div id="recurring-help">'+transport.html+'</div>');					
							 }
						)
				}
			});
	});
}); // Required