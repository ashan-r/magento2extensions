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
namespace Milople\Recurringandsubscriptionpayments\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
class PaymentIsAvailable implements ObserverInterface
{
	private $logger;
	protected $messageManager;
	
	 public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
		 \Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $data_helper,
		 \Milople\Recurringandsubscriptionpayments\Model\Subscription $subscription,
		 \Psr\Log\LoggerInterface $logger
    ) {
        $this->_authorization = $authorization;
		$this->helper = $data_helper;
		$this->subscription=$subscription;
		$this->logger = $logger;
    }
	
	public function execute(EventObserver $observer)
    {
		$event = $observer->getEvent();
        $method = $event->getMethodInstance();
		$quote = $observer->getQuote();
		
		if (is_null($quote))
		{
           return;
        }
        if (!$quote instanceof \Magento\Quote\Model\Quote) {
            $observer->getResult()->isAvailable = false;
            return;
        }
		$haveItems = false;
		foreach ($quote->getAllItems() as $item)
        {
					$buyInfo = $item->getBuyRequest();
						$SubscriptionType = $buyInfo->getMilopleSubscriptionType();
					if ($this->helper->isSubscriptionType($item) && !is_null($SubscriptionType)) 
					{
						$haveItems = true;
						break;
					}
        }
        $hasMethod=$this->subscription->hasMethodInstance($method->getCode());
		if ($haveItems && !$hasMethod)
		{
			$result = $observer->getEvent()->getResult();
			$result->setData('is_available', false);
			
		}

	}
}
