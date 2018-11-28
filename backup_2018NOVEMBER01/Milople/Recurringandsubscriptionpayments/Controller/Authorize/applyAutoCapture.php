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

use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface as Logger;
use Magento\Checkout\Model\Session as CheckoutSession;
//use Milople\Recurringandsubscriptionpayments\Model\Calculation as calculationModel;
use Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments as partialHelper;
use Magento\Framework\App\Action\Context;

    class applyAutoCapture extends \Magento\Framework\App\Action\Action
    {
        protected $_checkoutSession;

        public function __construct(
            Context $context,
        //calculationModel $calculationModel,
            CheckoutSession $checkoutSession,
        partialHelper $partialHelper
        ) {
            $this->_checkoutSession = $checkoutSession;
            //$this->_calculationModel = $calculationModel;
            $this->partialHelper = $partialHelper;
            parent::__construct($context);
        }

        public function execute()
        {
            $quote = $this->_checkoutSession->getQuote();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result = 0;
        if(1){
          $result = 1;
        }
            $resultJson->setData($result);
            return $resultJson;
        }
    }