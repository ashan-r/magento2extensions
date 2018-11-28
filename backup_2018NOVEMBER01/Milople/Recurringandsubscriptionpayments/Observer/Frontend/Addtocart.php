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
namespace Milople\Recurringandsubscriptionpayments\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
class Addtocart implements ObserverInterface
{
	private $logger;
	protected $messageManager;
	public function __construct(
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Directory\Helper\Data $data,
		\Milople\Recurringandsubscriptionpayments\Model\Plans\ProductFactory $planProductFactory,
		\Milople\Recurringandsubscriptionpayments\Model\PlansFactory $planFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Milople\Recurringandsubscriptionpayments\Model\TermsFactory $termsFactory,
		\Magento\Catalog\Model\Product $product,
		\Psr\Log\LoggerInterface $logger) {
    $this->request = $request;
 		$this->messageManager = $messageManager;
		$this->currencyConvertHelper = $data;
		$this->_storeManager = $storeManager;
		$this->_product = $product;
		$this->termFactory=$termsFactory;
		$this->planProductFactory=$planProductFactory;
		$this->planFactory=$planFactory;
		$this->logger = $logger;
    }
	
	 public function execute(\Magento\Framework\Event\Observer $observer)
   {
		 $item = $observer->getQuoteItem();
		 if ($item->getParentItem()) {$item = $item->getParentItem();}
		 $postdata = $this->request->getPost();
		 $cartItem = $observer->getEvent()->getQuoteItem();
		 $buyInfo = $cartItem ->getBuyRequest();
		 $product  = $cartItem->getProduct();
		 $idForgroup = $buyInfo->getProduct();
		 if(empty($idForgroup))  // This is for grouped product
		 {
			$product_id = $product->getId();
	   }
		 else
		 {
			$product_id = $buyInfo->getProduct() ;
		 }
		 $plans_product = $this->getPlanProducts()->load($product_id,'product_id');
		 $plan_data = $this->getPlan()->load($plans_product->getPlanId(),'plan_id');
		 if($plan_data->getData())
		 {
			if($postdata['milople_subscription_type']) 
			{
				if($postdata['milople_subscription_type'] >=0)
				{
					if($item->getProduct()->getFirstPeriodPrice() > 0 )
					{
						$price = $item->getProduct()->getFirstPeriodPrice() ;
					}
					else
					{
						$termid = $postdata['milople_subscription_type'];
						$qty = $buyInfo->getQty();
						$productId = $buyInfo->getProduct();
						$types =$this->termFactory->create()->load($termid);
						$price = $types->getPrice();
						
						$custom_option_price = $item->getProduct()->getFinalPrice() - $item->getProduct()->getPrice();
						/* Put condition for a case when special price is applied to product. */
						if($custom_option_price < 0)  
						{
							$custom_option_price = 0;
						}
						if($types->getPriceCalculationType() == 1)
						{
							$price = $item->getProduct()->getPrice() * $types->getPrice()/100 + $custom_option_price;
							
						}
						else
						{
							$price = $types->getPrice() + $custom_option_price;
						}
					}
					$price = $this->currencyConvertHelper->currencyConvert($price, $this->_storeManager->getStore()->getBaseCurrencyCode(), $this->_storeManager->getStore()->getCurrentCurrencyCode());
					if($item->getProductType() == 'configurable')
					{
						$item->setCustomPrice($price);
						$item->setOriginalCustomPrice($price);
						
						$item = $observer->getQuoteItem();
						$item->setQty($postdata['qty']);
						
					}
					else
					{
						$item->setQty($qty);
						$item->setCustomPrice($price);
						$item->setOriginalCustomPrice($price);
					}
					// Enable super mode on the product.
					$item->getProduct()->setIsSuperMode(true);
					
					if($plan_data->getStartDate() == 1)
					{
						if($postdata['milople_subscription_start_date'])
						{
							if(strtotime($postdata['milople_subscription_start_date']) < time())
							{
								$buyInfo->setMilopleSubscriptionStartDate(date('m-d-Y'));
							}
						}
					}
				}
				else
				{
					if($item->getProductType() == 'configurable')
					{
						$item->setCustomPrice($item->getProduct()->getFinalPrice());
						$item->setOriginalCustomPrice($item->getProduct()->getFinalPrice());
						
						$item = $observer->getQuoteItem();
						$item->setQty($postdata['qty']);
					}
					else
					{
						// Get the quote item
						$item = $observer->getQuoteItem();
						// Ensure we have the parent item, if it has one
						//$item = ( $item->getParentItem() ? $item->getParentItem() : $item );
						$product = $this->_product->load($item->getProduct()->getId());
						// Set the custom price
						$item->setQty($buyInfo->getQty());
						$item->setCustomPrice($item->getProduct()->getFinalPrice());
						$item->setOriginalCustomPrice($item->getProduct()->getFinalPrice());
						// Enable super mode on the product.
						$item->getProduct()->setIsSuperMode(true);
					}
				}
			}
			else
			{
				if($plan_data->getPlanStatus() != 2) 
				{
					throw new \Magento\Framework\Exception\LocalizedException(__('Please specify product option(s).'));
					
				}
				else  // If plan is Disable 
				{
					return ;
				}		
			}
		 } 
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
