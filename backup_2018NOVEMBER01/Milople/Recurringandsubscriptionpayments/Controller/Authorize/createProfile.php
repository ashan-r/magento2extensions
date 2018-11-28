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
namespace Milople\Recurringandsubscriptionpayments\Controller\Authorize;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session;
use Magento\Authorizenet\Controller\Directpost\Payment;
use Magento\Framework\App\Response\Http;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartManagementInterface;
use Milople\Recurringandsubscriptionpayments\Model\AuthorizeCim;
use Magento\Sales\Model\OrderFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;

class createProfile extends Action
{
    protected $_logger;
    protected $_coreRegistry;
    protected $_checkoutSession;
    protected $_encryptor;
    protected $cartManagement;
    protected $_orderFactory;
    protected $_authorizeModel;
    protected $_quoteManagement;
    protected $_customerSession;
    protected $_helperData;

    public function __construct(
        Context $context,
        LoggerInterface $loggerInterface,
        Registry $registry,
        Session $checkoutSession,
        CartManagementInterface $cartManagementInterface,
        OrderFactory $orderFactory,
        AuthorizeCim $_authorizeModel,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        CustomerSession $customerSession,
        \Magento\Checkout\Helper\Data $helperData
    ) {
        $this->_logger = $loggerInterface;
        $this->_coreRegistry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->cartManagement = $cartManagementInterface;
        $this->_orderFactory = $orderFactory;
        $this->_authorizeModel = $_authorizeModel;
        $this->_quoteManagement = $quoteManagement;
        $this->_customerSession = $customerSession;
        $this->_helperData = $helperData;        
        parent::__construct($context);
    }
  
    public function execute()
    {	
        $sessionId = $this->_checkoutSession->getSessionId();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $ccDetail = $this->getRequest()->getParams();		
        $ccDetail = $ccDetail['result'];

        $data = [
            'session_id' => $sessionId,
            'cc_number' => $ccDetail['ccNumber'],
            'cc_exp_month' => $ccDetail['expMonth'],
            'cc_exp_year' => $ccDetail['expYear']
        ];

        if (array_key_exists('ccId', $ccDetail)) {
            $data['cc_id'] = $ccDetail['ccId'];
        } else {
            $data['cc_id'] = 0;
        }

        $quote = $this->_checkoutSession->getQuote();		
        try {
            $response = $this->_authorizeModel->createCustomerProfile($data, $quote);
						if($response['result']){
							$this->_checkoutSession->setCustomerProfileId($response['customerProfileId']);
							$this->_checkoutSession->setPaymentProfileId($response['paymentProfileId']);
						}
						$resultJson->setData($response);
						
            return $resultJson;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while trying to process creating Authorize profile request.')
            );
        }
    }
}