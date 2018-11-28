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
namespace Milople\Recurringandsubscriptionpayments\Block\Sales\Order;



class Fee extends \Magento\Framework\View\Element\Template
{
    /**
     * Tax configuration model
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $helper,
        \Magento\Customer\Model\Session $session,
				\Milople\Recurringandsubscriptionpayments\Model\Subscription $subscription,
				\Magento\Framework\Stdlib\DateTime\DateTime $date,
				\Magento\Checkout\Model\Session $checkoutSession,
				\Milople\Recurringandsubscriptionpayments\Model\SubscriptionFactory $subscriptionFactory,
				\Milople\Recurringandsubscriptionpayments\Model\SequenceFactory $sequenceFactory,
				\Magento\Framework\ObjectManagerInterface $objectManager,
				//\Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
				//$this->logger = $logger;
        $this->helper = $helper;
        $this->session=$session;
        $this->_config = $taxConfig;
				$this->subscription = $subscription;
				$this->date = $date;
				$this->checkoutSession = $checkoutSession;
				$this->subscriptionFactory=$subscriptionFactory;
				$this->sequenceFactory=$sequenceFactory;
				$this->_objectManager=$objectManager;
        //parent::__construct($context, $data);
    }

	protected function _construct()
	{
		$this->_init('Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Subscription');
	}
	
    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    } 
    public function getStore()
    {
        return $this->_order->getStore();
    }

      /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
     public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
				$items = $this->checkoutSession->getQuote();
				$discountamount = 0;
				$price_for_discount = 0;
        $store = $this->getStore();
				$order_id = $this->_order->getId();
				$slider = $this->sequenceFactory->create();
				$coll = $slider->load($order_id,'order_id');
				$subscription_id = $coll->getSubscriptionId();
        if($subscription_id){
				 $subscription_table  =  $this->_objectManager->create('Milople\Recurringandsubscriptionpayments\Model\Subscription')->load($subscription_id);
				 $subscription_discount_amount = $subscription_table->getDiscountAmount();
				 $subscription_apply_on = $subscription_table->getApplyDiscountOn();
				 $parent_order_id = $subscription_table->getParentOrderId();
				 $present_order_id = $this->_order->getIncrementId();
				 if($present_order_id == $parent_order_id)
				 {
					 if($subscription_apply_on == 3){
						 $discountamount = 0;
					 }
					 else{
						 	$no_of_items = 0;
						 	foreach($this->_order->getAllVisibleItems() as $item){
									 $buyInfo = $item->getBuyRequest();
									 $period_type = $buyInfo->getMilopleSubscriptionType();
									 if($period_type > 0){
										 $price_for_discount += $item->getPrice() * $item->getQtyOrdered();
										 $no_of_items += $item->getQtyOrdered();
									 }
							}
							if(is_numeric($subscription_discount_amount) == 1){
								$discountamount = $subscription_discount_amount * $no_of_items;
							}
							else{
								$discountamount = $price_for_discount * $subscription_discount_amount / 100;
							}
					 }
				 }
				 else{
					 if($subscription_apply_on == 2){
						 $discountamount = 0;
					 }
					 else{
						 $no_of_items = 0;
						 foreach($this->_order->getAllVisibleItems() as $item){
							 $no_of_items += $item->getQtyOrdered();
						 }
						 $order_subtotal = $this->_order->getSubtotal();
						 $discountamount = $subscription_discount_amount * $order_subtotal / 100;
						 if(is_numeric($subscription_discount_amount) == 1) 
								$discountamount = $subscription_discount_amount * $no_of_items;
					 }
				 }
		}
		$discountamount *= -1;
		 if($discountamount < 0){
			$fee = new \Magento\Framework\DataObject(
			[
                    'code' => 'fee',
                    'strong' => false,
                    'value' => $discountamount,
                    //'value' => $this->_source->getFee(),
                    'label' => __('Subscription Discount'),
             ]
        );
        $parent->addTotal($fee, 'fee');
        // $this->_addTax('grand_total');
        $parent->addTotal($fee, 'fee');
			}
      return $this;
    }

}