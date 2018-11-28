<?php
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
**/
namespace Milople\Recurringandsubscriptionpayments\Block\Product\View;
class Recurringoptions extends \Magento\Framework\View\Element\Template
{
	public function __construct(

	 \Magento\Catalog\Block\Product\Context $context,
	 \Magento\Framework\App\Http\Context $httpContext,
	 \Milople\Recurringandsubscriptionpayments\Helper\Data $data_helper,	
	 \Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $helper,
	 \Milople\Recurringandsubscriptionpayments\Model\Plans\ProductFactory $planProductFactory,
	 \Milople\Recurringandsubscriptionpayments\Model\PlansFactory $planFactory,
	 \Milople\Recurringandsubscriptionpayments\Model\TermsFactory $terms,
	 //\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localedate,
	 //\Magento\Framework\UrlInterface $urlInterface,    
	 \Magento\Directory\Model\Currency $currency,
	 \Magento\Customer\Model\Session $session,
	 \Magento\Checkout\Model\Cart $cart,
	 array $data = []
   	 ) {
		 $this->_scopeConfig = $context->getScopeConfig();
		 $this->_coreRegistry = $context->getRegistry();
		 $this->helper = $data_helper;
		 $this->localeDate = $context->getLocaleDate();
		 $this->recurring_helper = $helper;
		 $this->planProductFactory=$planProductFactory;
		 $this->planFactory=$planFactory;
		 $this->_urlInterface = $context->getUrlBuilder();
		 $this->session=$session;
		 $this->terms=$terms;
		 $this->_currency = $currency;   
		 $this->cart=$cart;
		 $this->httpContext = $httpContext;
		 parent::__construct(
            $context,
            $data
        );
    }
	public function getProduct()
	{
		return $this->_coreRegistry->registry('product');
	}
	/*
	*	display calendar on product page.
	*/
	public function getDisplayCalendar($id)
	{
		$isavailable = $this->recurring_helper->isAvailableTo();	
		$plans_product = $this->getPlanProducts()->load($id,'product_id');
		$plan = $this->getPlan()->load($plans_product->getPlanId(),'plan_id');
		$customer_group = explode(',',$this->_scopeConfig->getValue(
       	 \Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_CUSTOMER_GROUP,
       	 \Magento\Store\Model\ScopeInterface::SCOPE_STORE
   		 ));
		$groupId =  $this->session->getCustomerGroupId();
		if(($isavailable == 1 ) || ($isavailable == 2 ) || (($isavailable == 3) && in_array($groupId,$customer_group))){
			if(($plan->getPlanStatus() == 1))
			{
				return $plan->getStartDate();
			}
		}
		return 0;
	}

	/*
		Check subscription option is there on product.
	*/
	public function hasSubscriptionOptions(){
		
		$plans = $this->getPlanProducts()->load($this->getProduct()->getId(),'product_id');
		if($plans->getProductId()){		   
			return true;
		}
		return false;
	}
	/* 
	*  return Calendar HTML on every product which is assign to subscription. 
	*/
	public function getCalendarHtml(){
		$html = '<input type="text" class="input-text required-entry" id="milople_subscription_start_date" name="milople_subscription_start_date" aria-required="true" >';
		$html .=
		'<script>
			 require([
				  "jquery",
				  "mage/calendar"
			 ], function($){
			 	 $.extend(true, $, {
				    calendarConfig: {
				    	dayNames: ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
			            dayNamesMin: ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],
			            monthNames: ["January","February","March","April","May","June","July","August","September","October","November","December"],
			            monthNamesShort: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
				 		 showButtonPanel: true,
          				 showWeek: false,
          				 showOn: "button",
          				 prevText: "Previous",
			             nextText: "Next",
			             changeMonth: true,
           				 changeYear: true,
           				 dateFormat: "mm/dd/yy",
			             
				     }
				  });
				 $("#milople_subscription_start_date").calendar({
					  buttonText:"Select Date",
				 });
			   });
		</script>';
		return $html;
	}
	/* 
	*  Return same object where we need model
	*  return planProductFactory object
	*/
	public function getPlanProducts(){
		return $this->planProductFactory->create();
	}
	/* 
	*  Return same object where we need model
	*  return planProductFactory object
	*/
	public function getPlan(){
		return $this->planFactory->create();
	}
	/* 
	*  Return same object where we need model
	*  return planProductFactory object
	*/
	public function getTerms(){
		return $this->terms->create();
	}
	/*
	*	Check subscription option is there on product.
	*/
	public function isRegisteredCustomer(){
		return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);		
	}
}
