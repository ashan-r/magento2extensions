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
namespace Milople\Recurringandsubscriptionpayments\Block\Customer;
class Subscription extends \Magento\Framework\View\Element\Template
{
	 /**
     * @var string
     */
    protected $_template = 'customer/subscription/list.phtml';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $orders;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
	    	\Magento\Sales\Model\Order $order,
        \Magento\Customer\Model\Session $session,
        \Milople\Recurringandsubscriptionpayments\Model\Subscription $subscription,
        \Milople\Recurringandsubscriptionpayments\Model\Subscription\Item $item,
        \Milople\Recurringandsubscriptionpayments\Model\Terms $terms,
        \Milople\Recurringandsubscriptionpayments\Model\Subscriptionstatus $subscriptionStatus,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
	   		$this->subscription=$subscription;
        $this->item=$item;
        $this->terms=$terms;
        $this->order=$order;
        $this->session=$session;
        $this->subscriptionStatus=$subscriptionStatus;
       parent::__construct($context, $data);
    }
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Subscriptions'));
    }
		public function getCustomer()
    {
        return $this->session->getCustomer();
    }
		/**
    * Get subscription collection
    */
		public function getCollection()
    {
        if (!$this->getData('collection')) {
            $this->setCollection(
               $this->subscription->getCollection()->addCustomerFilter($this->getCustomer())->setOrder('id', 'DESC')
            );
        }
        return $this->getData('collection');
    }
	 /**
    * Get Subscription Term Label
    */
		public function gettermlabel($id)
		{
			$terms = $this->terms->load($id);
			return $terms->getLabel();
		}
		/**
    * Get Subscription Status Label
    */
		public function getSubscriptionStatusLabel(\Milople\Recurringandsubscriptionpayments\Model\Subscription $subscription)
    {
	 	  return $this->subscriptionStatus->getLabel($subscription->getStatus());
    }
}