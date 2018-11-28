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
namespace Milople\Recurringandsubscriptionpayments\Helper;
use Magento\Sales\Model\Order;
class Recurringandsubscriptionpayments extends \Magento\Framework\App\Helper\AbstractHelper {

	protected $scopeConfig;
	protected $customerGroups;
	const HASH_SEPARATOR = ":::";
	const DB_DELIMITER = "\r\n";

	public function __construct(
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Milople\Recurringandsubscriptionpayments\Helper\Config $confighelper,
	\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localedate,
	\Milople\Recurringandsubscriptionpayments\Model\Plans\ProductFactory $planProductFactory,
	\Milople\Recurringandsubscriptionpayments\Model\PlansFactory $planFactory,
	\Milople\Recurringandsubscriptionpayments\Model\Config\Source\Customergroups $customerGroups,
	\Milople\Recurringandsubscriptionpayments\Model\Terms $terms,
	\Magento\Framework\Stdlib\DateTime\DateTime $date,
	\Milople\Recurringandsubscriptionpayments\Model\SubscriptionFactory $subscription,
	\Magento\Customer\Model\Session $session,
	\Magento\Customer\Model\Customer $customer,
	\Milople\Recurringandsubscriptionpayments\Helper\EmailSender $emailSender,
	\Milople\Recurringandsubscriptionpayments\Model\Sequence $sequence,
	\Psr\Log\LoggerInterface $logger,
	\Magento\Framework\App\Request\Http $request,
	\Magento\Checkout\Model\Session $checkoutSession
	) {
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->customerGroups	= $customerGroups;
		$this->configHelper = $confighelper;
		$this->logger=$logger;
		$this->_localeDate = $localedate;
		$this->planProductFactory=$planProductFactory;
		$this->planFactory=$planFactory;
		$this->terms=$terms;
		$this->emailSender=$emailSender;
		$this->subscription=$subscription;
		$this->storedate = $date; 
		$this->session=$session;
		$this->customer=$customer;
		$this->sequence=$sequence;
		$this->_checkoutSession = $checkoutSession;
		$this->request = $request;
	}
	# Check Extension is enabled or not
	public function isEnabled()
 	{
		if(($this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_MODULE_STATUS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1') &&
		   (!$this->scopeConfig->getValue('advanced/modules_disable_output/Milople_Recurringandsubscriptionpayments',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)))
		{
	  		 return true;
   		}	
  	 	return false; 
  }
	# Recurring available to which customer group 
	# It will check customer group inside general setting.
	public function isAvailableTo()
	{
		return $this->scopeConfig->getValue(
            \Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_GENERAL_ANONYMOUS_SUBSCRIPTIONS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Check how to apply discount
	 * return number
	 */
	public function discountAvailableTo()
	{
		return $this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_DISCOUNT_AVAILABLE_TO,
																				 \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	//function for getting value of apply Discount On
	public function applyDiscountOn()
	{
		return $this->scopeConfig->getValue('recurringandsubscription/discount_group/apply_discount_on',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	# Recurring available to which customer group 
	# It is specific customer group setting.
	public function allowedToSpecificCustomerGroups()
	{
		$groups =  explode(',',$this->scopeConfig->getValue(
            \Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_CUSTOMER_GROUP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
		return  $groups;
	}
	# Check for valid customer group for the dropdown option
	# Allowed specfic group
	public function isValidCustomerGroup()
	{
		$allGroups = $this->customerGroups->toOptionArray();
		$selectedGroups = $this->allowedToSpecificCustomerGroups();
		$selectegGroups=explode(',',$selectedGroups);
		if($selectedGroups[0]==0)//if guest is selected
			return false;
		foreach($allGroups as $group)
		{
			if($group['value'] != 0 && !in_array($group['value'],$selectedGroups))//if except guest any other is not selected then return false
			{
				return false;
			}
		}
		return true;//if except guest all customer group is selected in configuration
	}
	public function isApplyDiscount()
	{
		if($this->scopeConfig->getValue(
       	 \Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_APPLY_DISCOUNT,
       	 \Magento\Store\Model\ScopeInterface::SCOPE_STORE
   		 ) && $this->isEnabled() == 1)
		{
			return true;
		}
        return false;
	} 
	# Get Brand Label
	public function getBrandLabel()
  {
		return $this->scopeConfig->getValue(
       	 \Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_BRAND_LABEL,
       	 \Magento\Store\Model\ScopeInterface::SCOPE_STORE
   		 ); 
  }
	# Get Discount Amount from configuration
	public function discountAmount()
	{
		return $this->scopeConfig->getValue(
       	 \Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_DISCOUNT_AMOUNT,
       	 \Magento\Store\Model\ScopeInterface::SCOPE_STORE
   		 ); 
	} 	
	# Get Configuration of discount type
	public function applyDiscountType()
	{
		return $this->scopeConfig->getValue(
       	 \Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_DISCOUNT_CAL_TYPE,
       	 \Magento\Store\Model\ScopeInterface::SCOPE_STORE
   	);
	} 
	# Get Subscription type from item
	public function isSubscriptionType($item){
		if ($item instanceof \Magento\Catalog\Model\Product){
			$typeId = $item->getTypeId();
    } 
		elseif (($item instanceof \Magento\Sales\Model\Order\Item) || ($item instanceof \Magento\Quote\Model\Quote\Item)){
		$plans_product = $this->getPlanProducts()->load($item->getId(),'product_id');
		if($this->getPlan()->load($plans_product->getPlanId(),'plan_id')){
				$typeId = $item->getProductType();
				return true;
			}
    }
    return false;
	}
	# Check order status valid for activation.
	public function isOrderStatusValidForActivation($status)
	{
		if (
            ($status == Order::STATE_COMPLETE && 
						($this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS) != 'manuallybyadmin')) ||
            ($status == $this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS)) ||
            ($status == Order::STATE_NEW && ($this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS) == 'pending')||  
						($status == Order::STATE_PROCESSING) && ($this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS) == 'pending'))
        )		
		{
			return true;
		}
      return false;
	}
	# Assign subscription to customer
	public function assignSubscriptionToCustomer($quote,$order)
	{
		//$postdata = $this->request->getPost();
		//$subscription_type = $postdata['item'];
		//$subscription_start_date = $postdata['milople_subscription_start_date'];
		//end of initialization for backend
		$items = $order->getAllVisibleItems();
		$paymentMethod = $order->getPayment()->getMethod();
        $period_date_hashs = array();
		$subscription=false;
		if(!$this->subscription->create()->getId())
		{
			foreach ($items as $item)
      {
				$buyInfo = $item->getBuyRequest();
				if($this->isSubscriptionType($item))
				{
					$period_type = $buyInfo->getMilopleSubscriptionType();
					//$period_type = $subscription_type [$item->getProductId()] ['milople_select_subscription_type'];
					$Options =$this->terms->load($period_type);
					$planNo = $this->getPlan()->load($Options->getPlanId())->getStartDate();
					$startdate =  $this->_localeDate ->formatdate($this->_localeDate ->date(),\IntlDateFormatter::LONG);
					if($planNo==1){
						$startdate = $buyInfo->getMilopleSubscriptionStartDate();
					}
					if($planNo==3){
						$startdate= date('Y-m-01');
						$startdate = new \Zend_Date(date('M-01-Y', strtotime("+1 months", strtotime(date("Y-m-d")))));

					}
				   if($planNo != 2) {
						if (preg_match('/[\d]{4}-[\d]{2}-[\d]{2}/', $startdate)){
							$date = new \Zend_Date($startdate);
						}
						else{
							$date = new \Zend_Date($startdate, $this->_localeDate ->getDateFormat(\IntlDateFormatter::SHORT));			
						}
						$date_start = $date->toString(\Milople\Recurringandsubscriptionpayments\Model\Subscription::DB_DATE_FORMAT);	
					}
					else {
						$date_start = $this->storedate->date('Y-m-d');
					}
					if ($period_type > 0) {
						if (!isset($period_date_hashs[$period_type . self::HASH_SEPARATOR . $date_start])) {
							$period_date_hashs[$period_type . self::HASH_SEPARATOR . $date_start] = array();
						}
						$period_date_hashs[$period_type . self::HASH_SEPARATOR . $date_start][] = $item;
					}
				}
		}
		foreach ($period_date_hashs as $hash => $OrderItems)
        {
	       		$discountamount = 'null';
				$applydiscounton = 0;

				if($this->isApplyDiscount())   // (enable/disable)
				{
					$amount = $this->discountAmount() ;
					$calculation_type = $this->applyDiscountType();
					if($this->discountAvailableTo() == 3 )   // Specific customer group
					{
						$customer_group = explode(',',$this->selectedCustomerGroup());
						$groupId = $this->session->getCustomerGroupId();
						if(in_array($groupId,$customer_group))
						{
							$add_discount = 1;
						}
						else
						{
							$add_discount = 0;
						}
					 }
					 else
					 { 
					    $add_discount = 1;
					 }
					 
					 if($add_discount == 1)
					 {
							if($this->applyDiscountOn()!= 3) 
							{
								if($calculation_type == 1)  //Fixed
										$discountamount = $amount;
								else
									$discountamount = $amount.'%';
								
								$applydiscounton = $this->applyDiscountOn();
							}
							else
							{
								if($calculation_type == 1)  //Fixed
										$discountamount = $amount;
								else
									$discountamount = $amount.'%';

								$applydiscounton = 3;
							}
					}
					
				}
            list($period_type, $date_start) = explode(self::HASH_SEPARATOR, $hash);
							$status=\Milople\Recurringandsubscriptionpayments\Model\Subscription::STATUS_SUSPENDED;
                if($this->isOrderStatusValidForActivation($order->getState())) {
                	  if (($order->hasInvoices()  &&  ($this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS) == 'processing')) 
             				 || ($order->hasShipments() &&  ($this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS) == 'complete')) ||
											 ($this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS) == 'pending'))
										{
              					$status=\Milople\Recurringandsubscriptionpayments\Model\Subscription::STATUS_ENABLED;	
                		}
								}
							$subscription =$this->subscription->create()
                      ->setCustomer($this->customer->load($order->getCustomerId()))
										  ->setCustomerName($order->getCustomerName())
                      ->setPrimaryQuoteId($quote->getId())
                      ->setDateStart($date_start)
                      ->setStatus($status)
                      ->setTermType($period_type)
                      ->initFromOrderItems($OrderItems, $order)
					  ->setDiscountAmount($discountamount)
					  ->setApplyDiscountOn($applydiscounton)
					  ->save();
			  $subscription->creteSubscription(false);
			// Make change for add first sequence as a paid of order when it place
				 $this->sequence
              ->setSubscriptionId($subscription->getId())
              ->setDate($date_start)
							->setOrderId($order->getId())
							->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_PAYED)
							->setMailsent(1)
              ->save();
			 $this->sequence->unsetData();
									// Run payment method trigger
							if($paymentMethod == 'paypal_express' || $paymentMethod == 'Paypal_express')
							{
								$subscription->getMethodInstance($paymentMethod)->onSubscriptionCreate($subscription, $order, $quote);
							}
							if($paymentMethod == 'authorizenet_directpost')
							{
								$customer_profile_id = $this->_checkoutSession->getCustomerProfileId();
								$payment_profile_id = $this->_checkoutSession->getPaymentProfileId();
								$transcation_id = $customer_profile_id . "," . $payment_profile_id;
								$subscription->setTransactionId($transcation_id)->save();
							}
						 // Send Order Confirmation email
          }
		}
	}
	/*  Return same object where we need model
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
	*  Function responsible to return help HTML 
	*  @param termId 
	*  @param productPrice   
	*  @param productType
	*  @param productSymbol
	*  return html  
	*/
	public function getHelpHtml($termId,$productPrice,$productType,$symbol){
		$help_tooltip = '';
		try {
			if ($termId > - 1) {
				$id = $termId;
				$update = $this->terms->load($id);
				$price = $update->getPrice();
				if ($productPrice && $update->getPriceCalculationType() == 1) // Term price calculatioin is percentage
				{
					$price = $productPrice * $update->getPrice() / 100;
				}

				$help_tooltip.= '<p>Your subscription of this product will repeat ';
				$repeat_each = $update->getTermsper();
				if ($update->getRepeateach() > 1) {
					$repeat_each = $update->getTermsper() . 's';
				}

				if ($update->getNoofterms() == 0) {
					if ($productType == 'grouped') {
						if ($update->getPriceCalculationType() == 1) //  %
						{
							$help_tooltip.= 'at every ' . $update->getRepeateach() . ' ' . $repeat_each . ' with the ' . $price . '% of product price. </p>';
						}
						else {
							$help_tooltip.= 'at every ' . $update->getRepeateach() . ' ' . $repeat_each . ' with the ' . $symbol . $update->getPrice() . ' of product price. </p>';
						}
					}
					else {
						if ($update->getPriceCalculationType() == 1) //  %
						{
							$help_tooltip.= 'at every ' . $update->getRepeateach() . ' ' . $repeat_each . ' with the price of ' . $symbol . $price . '</p>';
						}
						else {
							$help_tooltip.= 'at every ' . $update->getRepeateach() . ' ' . $repeat_each . ' with the price of ' . $symbol . $update->getPrice() . '</p>';
						}
					}
				}
				else {
					if ($productType == 'grouped') {
						if ($update->getPriceCalculationType() == 1) //  %
						{
							$help_tooltip.= $update->getNoofterms() . ' times at every ' . $update->getRepeateach() . ' ' . $repeat_each . ' with the ' . $update->getPrice() . '% of product price.</p>';
						}
						else {
							$help_tooltip.= $update->getNoofterms() . ' times at every ' . $update->getRepeateach() . ' ' . $repeat_each . ' with the ' . $symbol . $update->getPrice() . ' of product price.</p>';
						}
					}
					else {
						if ($update->getPriceCalculationType() == 1) //  %
						{
							$help_tooltip.= $update->getNoofterms() . ' times at every ' . $update->getRepeateach() . ' ' . $repeat_each . ' with the price of ' . $symbol . $price . '</p>';
						}
						else {
							$help_tooltip.= $update->getNoofterms() . ' times at every ' . $update->getRepeateach() . ' ' . $repeat_each . ' with the price of ' . $symbol . $update->getPrice() . '</p>';
						}
					}
				}
				if ($this->isApplyDiscount()) {
					$amount = $this->discountAmount();
					$calculation_type = $this->applyDiscountType();
					if ($calculation_type == 1) {//Fixed{
					$discountamount = $symbol . $amount;
					}
					else {
						$discountamount = $amount . '%';
					}
					$help_tooltip.= '<p>You will get ' . $discountamount . ' discount on your subscription.</p>';
				}
				
			}
			$help_tooltip.= '<p>Final amount varies depending on shipping, tax and other charges.</p>';
			return $help_tooltip;
		}
		catch(\Exception $e) {
		}
	}
	# Get Plan Status for product page.To decided display the option or not
	public function getTempPlanStatus($plans)
	{
			$planid = 0;
			foreach($plans as $plan)
			{
				$planid=$plan->getPlanId();
				$isnormal=$plan->getIsNormal();
			}
			$_planTemp  = $this->getPlan()->load($planid);
		  return $_planTemp->getPlanStatus();
	}
	/**
   * Get Recurring Cofing
   * @return true/false
   */
	 public function getConfig ($config){
		return $this->scopeConfig->getValue($config,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
}