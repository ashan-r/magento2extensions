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
namespace Milople\Recurringandsubscriptionpayments\Model\Payment\Method\Paypal\Api;
use Magento\Payment\Model\Method\Logger;
class Nvp extends \Magento\Paypal\Model\Api\Nvp
{
	
	public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        \Psr\Log\LoggerInterface $logger,
        Logger $customLogger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Paypal\Model\Api\ProcessableExceptionFactory $processableExceptionFactory,
        \Magento\Framework\Exception\LocalizedExceptionFactory $frameworkExceptionFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
				\Milople\Recurringandsubscriptionpayments\Model\Total\Fee $fee,
				\Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($customerAddress, $logger, $customLogger, $localeResolver, $regionFactory, $countryFactory, $processableExceptionFactory, $frameworkExceptionFactory, $curlFactory, $data);
        $this->_countryFactory = $countryFactory;
        $this->_processableExceptionFactory = $processableExceptionFactory;
        $this->_frameworkExceptionFactory = $frameworkExceptionFactory;
        $this->_curlFactory = $curlFactory;
				$this->fee = $fee;
				$this->checkoutSession = $checkoutSession;
    }
     /**
     * DoExpressCheckout call
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoExpressCheckoutPayment
     */
		//code for setting discouted price in paypal
		public function callSetExpressCheckout()
    {
				//$discount = $this->fee->getDiscountAmount();
				$discount = $this->checkoutSession->getCustomDiscountFee();
        $this->_prepareExpressCheckoutCallRequest($this->_setExpressCheckoutRequest);
        $request = $this->_exportToRequest($this->_setExpressCheckoutRequest);
        $this->_exportLineItems($request);

        // import/suppress shipping address, if any
        $options = $this->getShippingOptions();
        if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 1;
        } elseif ($options && count($options) <= 10) {
            // doesn't support more than 10 shipping options
            $request['CALLBACK'] = $this->getShippingOptionsCallbackUrl();
            $request['CALLBACKTIMEOUT'] = 6;
            // max value
            $request['MAXAMT'] = $request['AMT'] + 999.00;
            // it is impossible to calculate max amount
            $this->_exportShippingOptions($request);
        }
				$request['AMT'] -= $discount;
				$request['ITEMAMT'] += $discount;
        $response = $this->call(self::SET_EXPRESS_CHECKOUT, $request);
        $this->_importFromResponse($this->_setExpressCheckoutResponse, $response);
    }
		//end of the code for etting discount in paypal
    public function callDoExpressCheckoutPayment()
    {
				//$discount = $this->fee->getDiscountAmount();
				$discount = $this->checkoutSession->getCustomDiscountFee();
        $this->_prepareExpressCheckoutCallRequest($this->_doExpressCheckoutPaymentRequest);
        $request = $this->_exportToRequest($this->_doExpressCheckoutPaymentRequest);
        $this->_exportLineItems($request);
				$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
				$checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');
			   if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 1;
        }
				$request['ITEMAMT'] += $discount;
			  $response = $this->call(self::DO_EXPRESS_CHECKOUT_PAYMENT, $request);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_importFromResponse($this->_doExpressCheckoutPaymentResponse, $response);
				$this->_importFromResponse($this->_createBillingAgreementResponse, $response);
				
				if (isset($response['BILLINGAGREEMENTID']))
				{
					$billing_agreement_id = $response['BILLINGAGREEMENTID'];
					//$request['TOKEN'] = $response['BILLINGAGREEMENTID'];
					//$request['PAYERID'] = $response['PAYERID'];
				}
				else
				{
						$customerId = $objectManager->get('Magento\Customer\Model\Session')->getCustomerId(); 
						$active_agrrement = $objectManager->get('Magento\Paypal\Model\Billing\Agreement')->getCollection()
							->addFieldToFilter('customer_id',$customerId)
							->addFieldToFilter('status','active')
							->getFirstItem();

						if(count($active_agrrement->getData()) > 0)
						{
							$billing_agreement_id = $active_agrrement->getReferenceId();		
						}
						else
						{
							$billing_agreement_id = null;
						}
				}
				$checkoutSession->setBillingAgreementId($billing_agreement_id);
    }
}
