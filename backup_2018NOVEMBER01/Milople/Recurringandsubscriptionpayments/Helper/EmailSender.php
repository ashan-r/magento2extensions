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

/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailSender extends \Magento\Framework\App\Helper\AbstractHelper
{
	 public function __construct(
    \Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $stateInterface,
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Magento\Framework\Pricing\Helper\Data $priceHelperData,
		\Magento\Framework\App\State $state,
		//\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Directory\Model\Currency $currency
		){
        parent::__construct($context);
		    $this->storeManager = $storeManager;
				$this->_transportBuilder = $transportBuilder;
				$this->inlineTranslation = $stateInterface;
				$this->dateTime = $dateTime;
		    //$this->logger=$logger;
				$this->scopeConfig=$context->getScopeConfig();
				$this->priceHelper = $priceHelperData;
				$this->_currency = $currency;
    }
    /**
    * get Logo URL for the EMAIL template
    */
		public function getLogoUrl()
		{
			return $this->storeManager->getStore()->getBaseUrl().$this->scopeConfig->getValue(
									'design/header/logo_src',
									\Magento\Store\Model\ScopeInterface::SCOPE_STORE
							);
		}
		/**
    * get Logo Alternate text
    */
		public function getLogoAlt()
		{
			return $this->scopeConfig->getValue(
								 'design/header/logo_alt',
									\Magento\Store\Model\ScopeInterface::SCOPE_STORE
							);
		}
    #Actuall Mail Sender Function
		public function sendMail($customerEmailId,$sender,$template,$templateVars,$emailCcData)
		{

			$templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
			$from = $sender;
			$to = array($customerEmailId);
			$transport = $this->_transportBuilder->setTemplateIdentifier($template)
							->setTemplateOptions($templateOptions)
							->setTemplateVars($templateVars)
							->setFrom($from)
							->addTo($to);		
			if(!empty($emailCcData))
			{
				$transport->addBcc($emailCcData);
			}
			$transport->getTransport()->sendMessage();

		}
		/** 
    * Fetch email sender email and name
    * @return $sender
  	*/
   public function getEmailsender($data)
   { 
      $sender = array();
      switch ($data)
      {
         case 'general':
            $sender = array(
               'name'=>$this->getConfig('trans_email/ident_general/name'),
               'email'=>$this->getConfig('trans_email/ident_general/email')
            );
            break;
         case 'sales':
            $sender = array(
               'name'=>$this->getConfig('trans_email/ident_sales/name'),
               'email'=>$this->getConfig('trans_email/ident_sales/email')
            );
            break;
         case 'support':
            $sender = array(
               'name'=>$this->getConfig('trans_email/ident_support/name'),
               'email'=>$this->getConfig('trans_email/ident_support/email')
            );
            break;
         case 'custom1':
            $sender = array(
               'name'=>$this->getConfig('trans_email/ident_custom1/name'),
               'email'=>$this->getConfig('trans_email/ident_custom1/email')
            );
            break;
         case 'custom2':
            $sender = array(
               'name'=>$this->getConfig('trans_email/ident_custom2/name'),
               'email'=>$this->getConfig('trans_email/ident_custom2/email')
            );
            break;
      }
      return $sender;
   }
	# get Email CC data from respective Email confirmation.
	public function getEmailCC($data)
	{
		switch ($data)
   	{ 
	   		case 'Order Confirmation':
     				return array_filter(explode(',',$this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_SEND_ORDER_CONFORMATION_EMAIL_CC_TO)));
	   		case 'Upcoming Payment':
		 			return array_filter(explode(',',$this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_REMINDER_CC_TO)));
			 	case 'Payment Confirmation':		
		 			return array_filter(explode(',',$this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_CONFORMATION_CC_TO)));
			 	case 'Subscription Status':
		 			return array_filter(explode(',',$this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ORDER_STATUS_CHANGE_CC_TO)));
			 	case'Subscription Expiry':
		 			return array_filter(explode(',',$this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_EXPIRY_REMINDER_EMAIL_CC_TO)));
		 }
	}
	# Get Respective Template
	public function getTemplate($data)
	{
			switch ($data)
     	{ 
		  		case 'Order Confirmation':
						return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_SEND_ORDER_CONFORMATION_EMAIL_TEMPLATE);
			 		
					case 'Upcoming Payment':
		 				return  $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_REMINDER_TEMPLATE);
					
					case 'Payment Confirmation':		
		 				return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_CONFORMATION_TEMPLATE);
		 				
			 		case 'Subscription Active Status':
		 				return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ORDER_STATUS_ACTIVE_TEMPLATE);
			 		
					case 'Subscription Suspend Status':
		 				return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ORDER_STATUS_SUSPEND_TEMPLATE);
		 			
		 			case 'Subscription Cancel Status':
		 		 		return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ORDER_STATUS_CANCLE_TEMPLATE);
		 				
		 			case 'Subscription Expire Status':
		 				return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ORDER_STATUS_EXPIRE_TEMPLATE);
		 				
		 			case 'Subscription Expiry':
		 				return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_EXPIRY_REMINDER_EMAIL_TEMPLATE);
		 				
		 }
	}
	/**
    * Get Recurring & Subscription Confing
    * @return true/false
    */
	 public function getConfig ($config){
		return $this->scopeConfig ->getValue($config,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
		/**
    * Get Enable/Disable Upcoming Remainder Confing
    * @return true/false
    */
	 public function isEnableUpComingPaymentReminderEmail()
	 {
	 	return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_REMINDER);
	 }
		/**
    * Get Enable/Disable Expired Subscription Email Confing
    * @return true/false
    */
	 public function isEnableUpExpiredSubscriptionAndEmail()
	 {
	 	return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_EXPIRY_REMINDER_EMAIL);
	 }
		/**
    * Get Enable/Disable Status Confing
    * @return true/false
    */
	 public function isEnableStatusChangeEmail()
	 {
	 	return $this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_EXPIRY_REMINDER_EMAIL);
	 }
	 /**
   *  It will send upcoming payment reminder to customer.
   */
	 public function sendUpComingPaymentReminderEmail($subscription)
	 {
	 	try{
	 		if($this->isEnableUpComingPaymentReminderEmail()){
			 $sender=$this->getEmailSender($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_REMINDER_SENDER));
			 $emailCC=$this->getEmailCC('Upcoming Payment');
			 $template=$this->getTemplate('Upcoming Payment');
			 $customer=$this->getCustomer($subscription->getCustomerId());
			 $subscriptionId=$subscription->getId();
			 $templateVars = 
			 array(
				'store' => $this->storeManager->getStore()->getName(),
				'logo_url' => $this->getLogoUrl(),
				'logo_alt' => $this->getLogoAlt(),
				'customer_name' => $customer->getName(),
				'next_reminder_date' => $subscription->getNextPaymentDate(),
				'store_url'   => $this->storeManager->getStore()->getBaseUrl(),
				'user_subscription' => $this->getUserSubscriptionUrl($subscriptionId)
			 );
			 $this->sendMail($customer->getEmail(),$sender,$template,$templateVars,$emailCC);
		 	}
		 }catch(\Exception $e){
			throw new \Exception($e->getMessage());
		 }
	 	
	 }
	 /**
   	* It will send upcoming expiry email to customer.
    */
	  public function sendExpiredSubscriptionAndEmail($subscription)
	 	{
			try{
				if($this->isEnableUpExpiredSubscriptionAndEmail()){
				 $sender=$this->getEmailSender($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_EXPIRY_REMINDER_EMAIL_SENDER));
				 $emailCC=$this->getEmailCC('Subscription Expiry');
				 $template=$this->getTemplate('Subscription Expiry');
				 $nextReminderDate=$subscription->getNextPaymentDate();
				 $customer=$this->getCustomer($subscription->getCustomerId());
				 $subscriptionId=$subscription->getId();
				 $templateVars = 
				 array(
					'store' => $this->storeManager->getStore()->getName(),
					'logo_url' => $this->getLogoUrl(),
					'logo_alt' => $this->getLogoAlt(),
					'customer_name' => $customer->getName(),
					'next_reminder_date' => $nextReminderDate,
					'store_url'   => $this->storeManager->getStore()->getBaseUrl(),
					'user_subscription' => $this->getUserSubscriptionUrl($subscriptionId)
				 );
				 $this->sendMail($customer->getEmail(),$sender,$template,$templateVars,$emailCC);
				 }
			 }catch(\Exception $e){
				throw new \Exception($e->getMessage());
			 }
	 	
	 }
	 /**
   	 *  It will send email regarding expired subscription to customer.
     */
	  public function sendAlreadyExpiredSubsciptionStatusEmail($subscription){
	  	try{
	 		if($this->isEnableStatusChangeEmail()){
			 $sender=$this->getEmailSender($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ORDER_STATUS_CHANGE_SENDER));
			 $emailCC=$this->getEmailCC('Subscription Status');
			 $template=$this->getTemplate('Subscription Expire Status');
			 $customer=$this->getCustomer($subscription->getCustomerId());
			 $subscriptionId=$subscription->getId();
			 $templateVars = 
			 array(
				'store'    => $this->storeManager->getStore()->getName(),
				'logo_url' => $this->getLogoUrl(),
				'logo_alt' => $this->getLogoAlt(),
				'store_url'=> $this->storeManager->getStore()->getBaseUrl(),
				'user_subscription' => $this->getUserSubscriptionUrl($subscriptionId)
			 );
			 $this->sendMail($customer->getEmail(),$sender,$template,$templateVars,$emailCC);
		 	 }
		 }catch(\Exception $e){
			throw new \Exception($e->getMessage());
		 }
	  }
	# Function will process the order status mail
	public function processStatusChangeEmails($subscription,$status){
		try{
	 
			 $sender=$this->getEmailSender($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ORDER_STATUS_CHANGE_SENDER));
			 $emailCC=$this->getEmailCC('Subscription Status');
			 $template=$this->getTemplate($status);
			 $customer=$this->getCustomer($subscription->getCustomerId());
			 $subscriptionId=$subscription->getId();
			 $templateVars = 
			 array(
				'store'    => $this->storeManager->getStore()->getName(),
				'logo_url' => $this->getLogoUrl(),
				'logo_alt' => $this->getLogoAlt(),
				'customer_name' => $customer->getName(),
				'store_url'=> $this->storeManager->getStore()->getBaseUrl(),
				'user_subscription' => $this->getUserSubscriptionUrl($subscriptionId)
			 );
			 $this->sendMail($customer->getEmail(),$sender,$template,$templateVars,$emailCC);
		 	
		 }catch(\Exception $e){
			throw new \Exception($e->getMessage());
		 }
	}
	# Function will process the order status mail
		public function processConfirmationMails($subscription,$status){
	
		try{
	 	   
			 $sender=$this->getEmailSender($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_SEND_ORDER_CONFORMATION_EMAIL_SENDER));
			 $emailCC=$this->getEmailCC($status);
			 $template=$this->getTemplate($status);
		 	 $customer=$this->getCustomer($subscription->getCustomerId());
			 $subscriptionId=$subscription->getId();
			 $price=$subscription->getOrderedItemsPrice($subscription->getParentOrderId());
			 if($subscription->getDateExpire()!=NULL){
				$endDate=date('Y-m-d', strtotime($subscription->getDateExpire()));
			 }else{
				 $endDate='-';
			 }	 
			 $templateVars = 
			 array(
				'store' => $this->storeManager->getStore()->getName(),
				'logo_url' => $this->getLogoUrl(),
				'logo_alt' => $this->getLogoAlt(),
				'customer_name' => $customer->getName(),
				'subscription_order' => $subscription->getParentOrderId(),
				'subscription_items' => $subscription->getOrderedItems($subscription->getParentOrderId()),
				'subscription_items_price'=>$price,
				'subscription_plan'=>$subscription->getPlanName($subscription->getTermType()),
				'subscription_term'=>$subscription->getTermName($subscription->getTermType()),
				'subscription_startdate'=> date('Y-m-d', strtotime($subscription->getDateStart())),
				'subscription_upcoming'=> date('Y-m-d', strtotime($subscription->getNextPaymentDate())),
		  	'subscription_end_date' => $endDate,
				'subscription_status_label'=>$subscription->getSubscriptionStatusLabelForEmail($subscription->getStatus()),
				'store_url'   => $this->storeManager->getStore()->getBaseUrl(),
				'user_subscription' => $this->getUserSubscriptionUrl($subscriptionId)
			 );
			$this->sendMail($customer->getEmail(),$sender,$template,$templateVars,$emailCC);
		 }catch(\Exception $e){
			throw new \Exception($e->getMessage());
		 }
	}
	# Function will process the order status mail
	public function processNextPaymentConfirmationEmails($subscription){
		try{
	 
			 $sender=$this->getEmailSender($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ORDER_STATUS_CHANGE_SENDER));
			 $emailCC=$this->getEmailCC('Payment Confirmation');
			 $template=$this->getTemplate('Payment Confirmation');
			 $customer=$this->getCustomer($subscription->getCustomerId());
			 $subscriptionId=$subscription->getId();
			 $templateVars = 
			 array(
				'store'    => $this->storeManager->getStore()->getName(),
				'customer_name' => $customer->getName(),
				'logo_url' => $this->getLogoUrl(),
				'logo_alt' => $this->getLogoAlt(),
				'store_url'=> $this->storeManager->getStore()->getBaseUrl(),
				'user_subscription' => $this->getUserSubscriptionUrl($subscriptionId)
			 );
			 $this->sendMail($customer->getEmail(),$sender,$template,$templateVars,$emailCC);
		 	
		 }catch(\Exception $e){
			throw new \Exception($e->getMessage());
		 }
	}
	#function will return Url of User Subscription Page
	public function getUserSubscriptionUrl($subscriptionId)
	{
		return $this->storeManager->getStore()->getUrl('recurringandsubscriptionpayments/customer/view',array('id'=>$subscriptionId));
	}
	# Function will return customer
	public function getCustomer($id){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		return $objectManager->get('\Magento\Customer\Model\Customer')->load($id);
	}
	
}
