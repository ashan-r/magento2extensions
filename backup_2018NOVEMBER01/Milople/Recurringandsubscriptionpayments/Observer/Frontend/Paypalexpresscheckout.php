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
class Paypalexpresscheckout implements ObserverInterface
{
	protected $logger;
	protected $messageManager;
	const HASH_SEPARATOR = ":::";
	const DB_DELIMITER = "\r\n";

	 public function __construct(
		 \Magento\Store\Model\Store $storeManager,
		 \Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $data_helper,
		 \Magento\Quote\Model\Quote $quote,
		 \Psr\Log\LoggerInterface $logger
    ) {
		$this->storeManager=$storeManager;
		$this->helper = $data_helper;
		$this->quote=$quote;
		$this->logger = $logger;
    }
	
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
	/*$order = $observer->getOrder();
		$store_id = $this->storeManager->load($order->getStoreId());
		$quote = $this->quote->setStore($store_id)->load($order->getQuoteId()); 
		$this->helper->assignSubscriptionToCustomer($quote,$order);*/
	}
}