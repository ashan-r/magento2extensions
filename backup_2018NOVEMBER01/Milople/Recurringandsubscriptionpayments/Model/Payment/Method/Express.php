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
namespace Milople\Recurringandsubscriptionpayments\Model\Payment\Method;
use Magento\Sales\Model\Order\Payment;
use Magento\Paypal\Model\Express\Checkout as ExpressCheckout;
class Express extends \Magento\Paypal\Model\Express
{
     /**
     * Website Payments Pro instance
     *
     * @var Mage_Paypal_Model_Pro
     */
    protected $_pro = null;
		 public function __construct(
			  \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Paypal\Model\ProFactory $proFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
       \Magento\Paypal\Model\InfoFactory $paypalInfoFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $proFactory,
            $storeManager,
            $urlBuilder,
            $cartFactory,
            $checkoutSession,
            $exception,
            $transactionRepository,
            $transactionBuilder,
            $resource,
            $resourceCollection,
            $data
        );
		$this->_checkoutSession = $checkoutSession;
		$this->_logger = $logger;
     }
		 /**
     * Place an order with authorization or capture action
     *
     * @param Payment $payment
     * @param float $amount
     * @return $this
     */
    protected function _placeOrder(Payment $payment, $amount)
    {
			$order = $payment->getOrder();
			$subscription_billingagreement = $this->_checkoutSession->getBillingAgreementData();
			$this->_checkoutSession->unsBillingAgreementData();
			if($subscription_billingagreement !='')
			{
				 //prepare api call
				 //Here come while capture amount of subscription
						$api = $this->_pro->getApi()
							->setPayerId($payment->getAdditionalInformation(ExpressCheckout::PAYMENT_INFO_TRANSPORT_PAYER_ID))
							->setAmount($amount)
							->setPaymentAction($this->_pro->getConfig()->getValue('paymentAction'))
							->setNotifyUrl($this->_urlBuilder->getUrl('paypal/ipn/'))
							->setInvNum($order->getIncrementId())
							->setCurrencyCode($order->getBaseCurrencyCode())
							->setReferenceId($subscription_billingagreement);
							$api->callDoReferenceTransaction();
			}
			else
			{
				$token = $payment->getAdditionalInformation(ExpressCheckout::PAYMENT_INFO_TRANSPORT_TOKEN);
				$cart = $this->_cartFactory->create(['salesModel' => $order]);
							$api = $this->getApi()
				->setToken($token)
				->setPayerId($payment->getAdditionalInformation(ExpressCheckout::PAYMENT_INFO_TRANSPORT_PAYER_ID))
				->setAmount($amount)
				->setPaymentAction($this->_pro->getConfig()->getValue('paymentAction'))
				->setNotifyUrl($this->_urlBuilder->getUrl('paypal/ipn/'))
				->setInvNum($order->getIncrementId())
				->setCurrencyCode($order->getBaseCurrencyCode())
				->setPaypalCart($cart)
				->setIsLineItemsEnabled($this->_pro->getConfig()->getValue('lineItemsEnabled'));
				if ($order->getIsVirtual()) {
					$api->setAddress($order->getBillingAddress())->setSuppressShipping(true);
				} else {
					$api->setAddress($order->getShippingAddress());
					$api->setBillingAddress($order->getBillingAddress());
				}

				// call api and get details from it
				$api->callDoExpressCheckoutPayment();
			}
				$this->_importToPayment($api, $payment);
					return $this;
    }
		/**
     * This function is run when subscription is created and new order creates
     * @param \Milople\Recurringandsubscriptionpayments\Model\Subscription  $Subscription
     * @param \Magento\Sales\Model\Order   $Order
     * @param \Magento\Quote\Model\Quote   $Quote
     * @return \Milople\Recurringandsubscriptionpayments\Model\Payment\Method\Recurringabstract
     */
	   public function onSubscriptionCreate(\Milople\Recurringandsubscriptionpayments\Model\Subscription $Subscription, 
										 \Magento\Sales\Model\Order $Order, 
										 \Magento\Quote\Model\Quote $Quote)
    {
				$this->createSubscription($Subscription, $Order, $Quote);
        return $this;
    }
		# Create subscription once after order
		public function createSubscription($Subscription, $Order, $Quote)
    {
				$billingid = $this->_checkoutSession->getBillingAgreementId();
				$Subscription->setTransactionId($billingid)->save();
    }
 
}
