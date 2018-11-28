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
namespace Milople\Recurringandsubscriptionpayments\Controller\Adminhtml\Plans;
class Grid extends \Milople\Recurringandsubscriptionpayments\Controller\Adminhtml\Plans
{
         /**
         * @var \Magento\Framework\View\Result\LayoutFactory
         */
        protected $resultLayoutFactory;
 
        /**
         * @param \Magento\Backend\App\Action\Context $context
         * @param \Webkul\Hello\Controller\Adminhtml\Hello\Builder $HelloBuilder
         * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
         */
        public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
        ) {
            parent::__construct($context);
            $this->resultLayoutFactory = $resultLayoutFactory;
        }
 
        /**
         * @return \Magento\Framework\View\Result\Layout
         */
        public function execute()
        {
            $resultLayout = $this->resultLayoutFactory->create();
            $resultLayout->getLayout()->getBlock('recurringandsubscriptionpayments.plans.edit.tab.grid');
            return $resultLayout;
        }
 
    }