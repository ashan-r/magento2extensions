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
use Magento\Framework\Event\Observer;
class AssignSubscriptionToCustomer implements ObserverInterface
{
	protected $logger;
	protected $messageManager;
	const HASH_SEPARATOR = ":::";
	const DB_DELIMITER = "\r\n";

	 public function __construct(
		 \Magento\Store\Model\StoreManagerInterface $storeManager,
		 \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
		 \Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $data_helper,
		 \Magento\Framework\App\State $state,
		 \Psr\Log\LoggerInterface $logger
    ) {
		$this->helper = $data_helper;
		$this->cartRepository=$cartRepository;
		$this->storeManager=$storeManager;
		$this->logger = $logger;
		$this->state = $state;
    }
	
	/* This will create subscription and one paid sequence */
	public function execute(Observer $observer)
    {
		$order = $observer->getOrder();
		$store_id = $this->storeManager->getStore()->getId();
		if($this->state->getAreaCode() == 'adminhtml')
		{
			$this->logger->addDebug('Admin order creating');
			$this->logger->addDebug($order->getStoreId());
			$this->storeManager->setCurrentStore($order->getStoreId());
		}
		$quoteRepository = $this->cartRepository;
         /** @var \Magento\Quote\Model\Quote $quote */
   	$quote = $quoteRepository->get($order->getQuoteId());
		/* Create Subscription */
		$this->helper->assignSubscriptionToCustomer($quote,$order);
		
	}

}