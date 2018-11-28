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
namespace Milople\Recurringandsubscriptionpayments\Model;
class RecurringCron
{
	public function __construct(
		\Milople\Recurringandsubscriptionpayments\Model\Subscription $subscription,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\App\State $appState,
		\Milople\Recurringandsubscriptionpayments\Model\TermsFactory $terms,
		\Milople\Recurringandsubscriptionpayments\Model\SequenceFactory $sequenceFactory,
		\Milople\Recurringandsubscriptionpayments\Helper\EmailSender $emailSender,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
		){
		$this->logger = $logger;
		$this->scopeConfig=$scopeConfig;
		$this->sequenceFactory=$sequenceFactory;
		$this->terms=$terms;
		$this->subscription=$subscription;
		$this->emailSender=$emailSender;
		 try {
        $appState->setAreaCode('frontend');
  	  } catch (\Magento\Framework\Exception\LocalizedException $e) {
        // intentionally left empty
    	}
	}
	// Starting point of cron execution	
	public function execute(){
		$this->processTodaySubscriptions();
		$this->processUpcomingReminderEmails();
		$this->processBeforeExpireSubscriptionEmails();
		$this->checkForExpiredSubscriptionAndEmail();
		$this->setSkippedSubscriptionToActive();
	}
	// Process Today Subscription
	public function processTodaySubscriptions()
	{
		// Get all active subscriptions	
		foreach ($this->getTodayPendingSubscriptions() as $subscription) {	
        	$subscription->payForDate(new \Zend_Date);
	     }
	}
	//get All Pending Subscription of current Date.
	public function getTodayPendingSubscriptions()
	{
		$collection = $this->subscription
						->getCollection()
						->addActiveFilter()
						->addTodayFilter();
		return $collection;
	 	
	}
	 /**
   * Process Upcoming Remainder Emails
   */
   public function processUpcomingReminderEmails()
	 {
	   $configRemainder=$this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_REMINDER);
	  	$reminderBeforeDays =$this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_REMINDER_BEFORE_DAYS);
	   if($configRemainder== 1){
		    $todayDate=$this->getBeforeExpireDayDate($reminderBeforeDays);
		    $collection=$this->sequenceFactory->create()
						->getCollection()
						->setOrder('date', 'ASC')
						->addFieldToFilter('mailsent', 0)
				 		->addFieldToFilter('date', $todayDate)
				 		->addFieldToFilter('status','pending');
		 $sequence_id = -1;
		foreach ($collection as $sequence)
	        {
	             $subscription = $this->subscription->load($sequence->getSubscriptionId());
	            if ($subscription->getId() != $sequence_id)
	            {
	                $sequence_id = $subscription->getId();
	                if ($sequence->getMailsent() != 1 && ($configRemainder == '1') && $subscription->getStatus() == 1){
	              	   		$this->emailSender->sendUpComingPaymentReminderEmail($subscription);
	                     	$sequence->setMailsent(1)->save();
	               }
	            }
	         }
		   	
	    }
	 }
	/**
  * Check and process Upcoming Expiry of Subscription and Emails
   */
	 public function processBeforeExpireSubscriptionEmails(){
	 	$configExpiryRemainder=$this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_EXPIRY_REMINDER_EMAIL);
		$reminderBeforeDays =$this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_EXPIRY_REMINDER_BEFORE_DAYS);
		$beforeDate=$this->getBeforeExpireDayDate($reminderBeforeDays);
		$collection = $this->subscription->getCollection()
	 				->addActiveFilter()
	 				->addFieldToFilter('date_expire', $beforeDate)
	 				->addFieldToFilter('expirymail',0);
		foreach ($collection as $subscription) {
		 	#send expiry email and set expiry mail status in subscription table 
			if ($configExpiryRemainder==1){
		 	   	$this->emailSender->sendExpiredSubscriptionAndEmail($subscription);
				$expiredSubscription=$this->subscription->load($subscription->getId());
				$expiredSubscription->setExpirymail(1)->save();
			}	
		}

	 }
	 /**
   *  Check and process for expired subscription.Set status expired and send email
   */
	 public function checkForExpiredSubscriptionAndEmail(){
	 	$afterExpireDate=$this->getAfterExpireDaysDate(1);
		$collection = $this->subscription->getCollection()
	 				->addActiveFilter()
	 				->addFieldToFilter('date_expire', $afterExpireDate);
	 	foreach ($collection as $subscription) {
		 	# set status expire and send expired status email
			$expiredSubscription=$this->subscription->load($subscription->getId());
			$expiredSubscription->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Subscription::STATUS_EXPIRED)->save();
			$this->emailSender->sendAlreadyExpiredSubsciptionStatusEmail($subscription);	
		}

	 }
	/**
  * Set skipped sequences to active
  */
	public function setSkippedSubscriptionToActive(){

		$collection = $this->subscription
			  			->getCollection()
			  			->addFieldToFilter('status',\Milople\Recurringandsubscriptionpayments\Model\Subscription::STATUS_SKIPPED);
	      if(sizeof($collection) > 0){
				$now = \Zend_Date::now();
				$todayDate = new \DateTime($now->get('YYYY-MM-dd'));
				$todayDate=$todayDate->format('Y-m-d');
				foreach($collection as $collect){

	            	$term_data = $this->terms->create()->load($collect->getTermType());
					$data = $term_data->getData();	
	            if(!empty($data))
		        	{		
				        $sequences =$this->sequenceFactory->create()->getCollection()
							->addFieldToFilter('subscription_id',$collect->getId())
							->addFieldToFilter('date',array('gteq'=> $todayDate))
							->addFieldToFilter('status','skipped')
						 	->setOrder('date','desc'); 
								if(sizeof($sequences->getData()) == 0){
									$collect->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Subscription::STATUS_ENABLED)->save();
								}
	           	}
				}
		  }
	}
	/**
    * Get Recurring Cofing
    * @return true/false
    */
	 public function getConfig ($config){
		return $this->scopeConfig ->getValue($config,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
	 /*
	 *  It will return date with number of succeding days to present day.
	 */
	 public function getBeforeExpireDayDate($reminderBeforeDays){
	 	$now = \Zend_Date::now();
		$todayDate = new \DateTime($now->get('YYYY-MM-dd'));
		$todayDate=$todayDate->format('Y-m-d');
		$todayDate=date('Y-m-d', strtotime($todayDate ."+$reminderBeforeDays day"));
		return $todayDate;
	 }
	 /*
	 * It will return date of one day preceeding to present day.
	 */
	 public function getAfterExpireDaysDate($reminderAfterDay){
	 	$now = \Zend_Date::now();
		$todayDate = new \DateTime($now->get('YYYY-MM-dd'));
		$todayDate=$todayDate->format('Y-m-d');
		$todayDate=date('Y-m-d', strtotime($todayDate ."-$reminderAfterDay day"));
		return $todayDate;
	 }
}// Recurring Cron Ends
