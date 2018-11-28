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
use Milople\Recurringandsubscriptionpayments\Model\Subscription;
class SavePaymentInfoInSession implements ObserverInterface
{
	private $logger;
	protected $messageManager;
	public function __construct(
    \Magento\Framework\AuthorizationInterface $authorization,
		\Milople\Recurringandsubscriptionpayments\Helper\Data $data_helper,
		\Magento\Customer\Model\Session $customersession,
		\Psr\Log\LoggerInterface $logger
    ) {
    $this->_authorization = $authorization;
		$this->helper = $data_helper;
		$this->logger = $logger;
		$this->customersession = $customersession; 
    }
	public function execute(EventObserver $observer)
  {
		try
		{
			if (!Subscription::isIterating()) 
			{
				$quote = $observer->getQuote();
				if (!$quote->getPaymentsCollection()->count())
					return;
				$payment = $quote->getPayment();
				if ($payment && $payment->getMethod()) {
					if ($payment->getMethodInstance() instanceof \Magento\Payment\Model\Method\Cc) {
						// Credit Card number
						if ($payment->getMethodInstance()->getInfoInstance() && ($ccNumber = $payment->getMethodInstance()->getInfoInstance()->getCcNumber())) {
							$ccCid = $payment->getMethodInstance()->getInfoInstance()->getCcCid();
							$ccType = $payment->getMethodInstance()->getInfoInstance()->getCcType();
							$ccExpMonth = $payment->getMethodInstance()->getInfoInstance()->getCcExpMonth();
							$ccExpYear = $payment->getMethodInstance()->getInfoInstance()->getCcExpYear();
							$this->customersession->setrecurringandrentalpaymentsCcNumber($ccNumber);
							$this->customersession->setrecurringandrentalpaymentsCcCid($ccCid);
						}
					}
				}
			}
		 }
		 catch (\Exception $e)
		 {
				$this->logger->addDebug($e->getMessage());
		 }
	}
}