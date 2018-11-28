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
namespace Milople\Recurringandsubscriptionpayments\Model\Total;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
   /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;

    public function __construct(
      \Magento\Quote\Model\QuoteValidator $quoteValidator,
      \Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $helper,
			\Psr\Log\LoggerInterface $logger,
			\Milople\Recurringandsubscriptionpayments\Model\Subscription $subscription,
			\Magento\Framework\Stdlib\DateTime\DateTime $date,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Milople\Recurringandsubscriptionpayments\Model\SubscriptionFactory $subscriptionFactory,
			\Magento\Framework\ObjectManagerInterface $objectManager,
			\Magento\Backend\Model\Session\Quote $sessionQuote,
			\Milople\Recurringandsubscriptionpayments\Model\Plans\ProductFactory $planProductFactory,
			\Milople\Recurringandsubscriptionpayments\Model\PlansFactory $planFactory,
			\Milople\Recurringandsubscriptionpayments\Model\TermsFactory $termsFactory,
			\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
			\Magento\Framework\App\State $areacode,
      \Magento\Customer\Model\Session $session)
    {
        $this->helper = $helper;
        $this->session=$session;
				$this->logger = $logger;
        $this->quoteValidator = $quoteValidator;
				$this->subscription = $subscription;
				$this->date = $date;
				$this->checkoutSession = $checkoutSession;
				$this->subscriptionFactory=$subscriptionFactory;
				$this->_objectManager=$objectManager;
				$this->sessionQuote=$sessionQuote;
				$this->termFactory=$termsFactory;
				$this->planProductFactory=$planProductFactory;
				$this->planFactory=$planFactory;
			  $this->quoteRepository = $quoteRepository;
				$this->areacode = $areacode;
    }
		
	protected function _construct()
	{
		$this->_init('Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Subscription');
	}
	
  public function getDiscountAmount()
  {
    //code for calculating Discount
				//loading tabel
				$subscription_id = $this->checkoutSession->getSubscriptionIdData();
				$subscription_table  =  $this->_objectManager->create('Milople\Recurringandsubscriptionpayments\Model\Subscription')->load($subscription_id);
				$sub_start_date = $subscription_table->getDateStart();
				$sub_start_date = date( "Y-m-d", strtotime($sub_start_date));
				//end of loading table
        $discountamount = 'null';
				$applydiscounton = 0;
				$gmtdate = $this->date->gmtDate();
				$gmtdate = date( "Y-m-d", strtotime($gmtdate));
				$aplly_discounts_on = $subscription_table->getApplyDiscountOn();
				if($this->helper->isApplyDiscount())   // (enable/disable)
				{
					$amount = $this->helper->discountAmount();
					$calculation_type = $this->helper->applyDiscountType();
					if($this->helper->discountAvailableTo() == 3 )   // Specific customer group
					{
						$customer_group = explode(',',$this->helper->selectedCustomerGroup());
						$groupId = $this->session->getCustomerGroupId();
						if(in_array($groupId,$customer_group))
						{
							$add_discount = 1;
						}
						else
						{
							$add_discount = 0;
						}
					 }
					 else
					 { 
					    $add_discount = 1;
					 }
					 
					 if($add_discount == 1)
					 {
							if($this->helper->applyDiscountOn()!= 3) 
							{
								if($this->helper->applyDiscountOn() == 1)
								{
									//Discount for All terms
									if($calculation_type == 1)  //Fixed
										$discountamount = $amount;
									else
										$discountamount = $amount/100;

									$applydiscounton = $this->helper->applyDiscountOn();
								}
								else{
									//Discount for 1st term only
									$discountamount = 0;
									$applydiscounton = $this->helper->applyDiscountOn();
									if($gmtdate == $sub_start_date)
									{
										if($calculation_type == 1)  //Fixed
											$discountamount = $amount;
										else
											$discountamount = $amount/100;
									}
								}
							}
							else
							{
								if($gmtdate == $sub_start_date){
                	$amount = 0;
								}
								if($calculation_type == 1)  //Fixed
										$discountamount = $amount;
								else
									$discountamount = $amount/100;

								$applydiscounton = 3;
							}
					}
				}
        //end of calculating discount
        return $discountamount * -1;
  }
	
  public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $exist_amount = 0; //$quote->getFee(); 
        $fee = 0; //Excellence_Fee_Model_Fee::getFee();
				//for percentage discount
						$items = $quote->getAllVisibleItems();
						$overall_price_for_percentage_discount = 0;
						$discount_percentage_flag = 0;
						//$address = $shippingAssignment->getShipping()->getAddress();
						if($this->checkoutSession->getSubscriptionIdData()){
							$discount_percentage_flag = 1;
						}
						$subscription_id_auto_order_generate = $this->checkoutSession->getSubscriptionIdData();
						foreach($items as $item){
							$buyInfo = $item->getBuyRequest();
							$period_type = $buyInfo->getMilopleSubscriptionType();
							$product = $item->getProduct();
							/*if($product->getTypeId() == 'configurable')
							{
								continue;
							}*/
							$product_id = $product->getId();
							$plans_product = $this->getPlanProducts()->load($product_id,'product_id');
							$plan_data = $this->getPlan()->load($plans_product->getPlanId(),'plan_id');
							$types =$this->termFactory->create()->load($period_type);
							$price = $types->getPrice();
							$checking_subs_product = $this->helper->isSubscriptionType($item);
							if($period_type > 0){
								$discount_percentage_flag += $item->getQty();
								$overall_price_for_percentage_discount += $price * $item->getQty();
							}
						}
						if($subscription_id_auto_order_generate > 0){
							$overall_price_for_percentage_discount = $this->checkoutSession->getSubtotalForDiscount();
						}
						if($this->helper->applyDiscountType() == 1){
							$fee = 0;
							$fee = $discount_percentage_flag * $this->getDiscountAmount();
						}
						else{
							$fee = 0;
							$fee = ($overall_price_for_percentage_discount * $this->getDiscountAmount());
						}
				//end of percentage discount
				$balance = 0;
				$this->checkoutSession->setCustomDiscountFee($fee);
        $balance = $fee - $exist_amount;
        $total->setTotalAmount('fee', $balance);
        $total->setBaseTotalAmount('fee', $balance);

        $total->setFee($balance);
        $total->setBaseFee($balance);
		
        $total->setGrandTotal($total->getGrandTotal() + $balance);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $balance);
        return $this;
    }

    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     */
    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
			try {
            //Trigger to re-calculate totals
            //$payment = $this->_helper->jsonDecode($this->getRequest()->getContent());
            //$this->checkoutSession->getQuote()->getPayment()->setMethod($payment['payment']);
            $this->checkoutSession->getQuote()->collectTotals()->save();

        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
				}
				$fee = 0;
				$items = $quote->getAllVisibleItems();
						$overall_price_for_percentage_discount = 0;
						$discount_percentage_flag = 0;
						if($this->checkoutSession->getSubscriptionIdData()){
							$discount_percentage_flag = 1;
						}
						foreach($items as $item){
							$buyInfo = $item->getBuyRequest();
							$period_type = $buyInfo->getMilopleSubscriptionType();
							$product = $item->getProduct();
							/*if($product->getTypeId() == 'configurable')
							{
								continue;
							}*/
							$product_id = $product->getId();
							$plans_product = $this->getPlanProducts()->load($product_id,'product_id');
							$plan_data = $this->getPlan()->load($plans_product->getPlanId(),'plan_id');
							$types =$this->termFactory->create()->load($period_type);
							$price = $types->getPrice();
							$checking_subs_product = $this->helper->isSubscriptionType($item);
							if($period_type > 0){
								$discount_percentage_flag += $item->getQty();
								$overall_price_for_percentage_discount += $price * $item->getQty();
							}
						}
						if($this->helper->applyDiscountType() == 1){
							$fee = $discount_percentage_flag * $this->getDiscountAmount();
						}
						else{
							$fee = ($overall_price_for_percentage_discount * $this->getDiscountAmount());
						}
				$finaldiscountamount = $fee;
        return [
            'code' => 'fee',
            'title' => 'Subscription Discount',
            'value' => $finaldiscountamount
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Fee');
    }
		/* 
	*  Return same object where we need model
	*  return planProductFactory object
	*/
	public function getPlanProducts(){
		return $this->planProductFactory->create();
	}

	/* 
	*  Return same object where we need model
	*  return planProductFactory object
	*/
	public function getPlan(){
		return $this->planFactory->create();
	}
	
}