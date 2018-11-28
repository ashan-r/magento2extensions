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
use Magento\Sales\Model\Order\Address\Renderer;
class Payments extends \Magento\Framework\View\Element\Template
{
	 /**
     * @var string
     */
    protected $_template = 'customer/subscription/payments.phtml';

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
				\Milople\Recurringandsubscriptionpayments\Model\Plans $plans,
				\Magento\Framework\Pricing\Helper\Data $currency,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Group $group,
        \Magento\Sales\Model\Order $order,
        \Milople\Recurringandsubscriptionpayments\Model\Terms $terms,
      	\Milople\Recurringandsubscriptionpayments\Model\Subscription\Item $item,
       	\Milople\Recurringandsubscriptionpayments\Model\Sequence $sequence,
				Renderer $addressRenderer,
        array $data = []
    	) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
				$this->terms=$terms;
        $this->plans=$plans;
        $this->item=$item;
        $this->order=$order;
        $this->session=$session;
				$this->sequence=$sequence;
        $this->group=$group;
				$this->_currency = $currency;
				$this->addressRenderer = $addressRenderer;
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
	 	/**
    * Get Subscription Term
    */
		public function getTerm()
		{
			return $this->terms->load($this->getSubscription()->getTermType());
		}
		/**
    * Get Order of subscription
    */
		public function getOrder()
		{
				if (!$this->getData('order')) {
           $this->setOrder($this->getSubscription()->getOrder());
        }
        return $this->getData('order');
		}
		/**
    * Get plan of subscription
    */
		public function getPlan()
		{
			$plan = $this->getTerm()->getPlanId();
			return $this->plans->load($plan);
		}
	 /**
    * Get customer from session
    */  
		public function getCustomer()
    {
        return $this->session->getCustomer();
    }
	 /**
    * Get customer group name
    */  
		public function getCustomerGroupName()
		{
				if ($this->getOrder()) 
				{
            return $this->group->load((int)$this->getOrder()->getCustomerGroupId())->getCode();
        }
        return null;
		}
		/**
    * Get order data of subscription
    */
		public function getOrderData()
		{
			$id = $this->getSubscription()->getId();
			$orderid= $this->item->load($id,'subscription_id');
			$order_collection = $this->order->load($orderid->getPrimaryOrderId());
			return $order_collection;
		}
		/**
    * Get item collection from order
    */
		public function getItemsCollection()
    {
        return $this->getOrderData()->getItemsCollection();
    }
		/**
    * Get formatted billings address of customer
    */
		public function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }
	 /**
    * Get collection
    */
		public function getCollection()
    {
			if (!$this->getData('collection')) {
		  		 $this->setCollection($this->sequence
                                  ->getCollection()
                                  ->addSubscriptionFilter($this->getSubscription())
                                  ->setOrder('date', 'asc')
            );
		     }
        return $this->getData('collection');
    }
}