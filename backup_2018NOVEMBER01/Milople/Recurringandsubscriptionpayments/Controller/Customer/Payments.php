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
namespace Milople\Recurringandsubscriptionpayments\Controller\Customer;
use Magento\Sales\Controller\OrderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class Payments extends \Magento\Framework\App\Action\Action implements OrderInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
	 /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Milople\Recurringandsubscriptionpayments\Model\SubscriptionFactory $subscription,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->subscription= $subscription;
        parent::__construct($context);
    }

    /**
     * Customer order history
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
				$block = $resultPage->getLayout()->getBlock('recurringandsubscriptionpayments.customer.subscription.payments');
        if ($block) {
            $subscription=$this->subscription->create();
            $block->setRefererUrl($this->_redirect->getRefererUrl())
				->setSubscription($subscription->load($this->getRequest()->getParam('id')));
        }
	    return $resultPage;
    }
}
