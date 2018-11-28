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
class UpdateCart implements ObserverInterface
{
	public function __construct(
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Directory\Helper\Data $data,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
		\Milople\Recurringandsubscriptionpayments\Model\Plans\ProductFactory $planProductFactory,
		\Milople\Recurringandsubscriptionpayments\Model\PlansFactory $planFactory,
		\Magento\Catalog\Model\Product $product,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Checkout\Helper\Cart $cart,
		\Milople\Recurringandsubscriptionpayments\Model\Terms $terms,
		\Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
		\Psr\Log\LoggerInterface $logger) {
    $this->request = $request;
 		$this->currencyConvertHelper = $data;
		$this->messageManager = $messageManager;
		$this->_storeManager = $storeManager;
		$this->_product = $product;
		$this->planProductFactory=$planProductFactory;
		$this->planFactory=$planFactory;
		$this->cart=$cart;
		$this->checkoutSession = $checkoutSession;
		$this->quoteRepository = $quoteRepository;
		$this->terms=$terms;
		$this->resultRedirectFactory=$resultRedirectFactory;
		$this->logger = $logger;
  }
	public function execute(\Magento\Framework\Event\Observer $observer)
  {
		$postdata = $this->request->getPost();
		$cartItem = $observer->getEvent()->getItem();
		$buyInfo = $cartItem->getBuyRequest();
		$product  = $cartItem->getProduct();
		$plans_product = $this->getPlanProducts()->load($buyInfo->getProduct(),'product_id');
    $plan_data =$this->getPlan()->load($plans_product->getPlanId(),'plan_id');
   	$cartId = $this->checkoutSession->getQuoteId();
	  $quote = $this->quoteRepository->getActive($cartId);
		/*fatch value of attribute to get price of attribute to add in final product price*/
		$allAttributes= array();
		try
		{
			if(isset($postdata['options']))
			{
				$allAttributes=$postdata['options'];
			}
		}
		catch(\Exception $e)
		{
            throw $e;
        }
		$productID = $buyInfo->getProduct();
		$product = $this->_product->load($productID);
		$attributePrice=0;
		$original_qty=0;
		$original_qty+=$buyInfo->getQty();//$postdata['qty'];
		if($product->getTypeID()=='configurable')
		{		
			$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
			$attributeOptions = array();
			$attributePrice=0;//this variable hold value of custom option to add in price
			foreach ($productAttributeOptions as $productAttribute) 
			{
				foreach ($productAttribute['values'] as $attribute) 
				{
					if(in_array($attribute['value_index'],$allAttributes))
					{
						$attributePrice+=$attribute['pricing_value'];
					}
				}
			} 
		}
		$quote = $this->cart->getCart()->getQuote();
		$attributePrice = 0;
		foreach ($quote->getItemsCollection() as $item)
		{
			if ($optionIds = $item->getProduct()->getCustomOption('option_ids')) 
			{
				$attributePrice = 0;
				foreach (explode(',', $optionIds->getValue()) as $optionId) 
				{
					if ($option = $item->getProduct()->getOptionById($optionId)) 
					{
						$confItemOption = $item->getProduct()->getCustomOption('option_'.$option->getId());
							$group = $option->groupFactory($option->getType())
							->setOption($option)
							->setConfigurationItemOption($confItemOption);
						$attributePrice += $group->getOptionPrice($confItemOption->getValue(), 0);
					}
				}
			}
		}
		/* pricing of attribue finish */
		/* Set updated subscription options to the item */
		 $buyInfo = $item->getProduct()->getCustomOption('info_buyRequest');
		 $buyRequestArr = unserialize($buyInfo->getValue());
		 $buyRequestArr['milople_subscription_type'] = $postdata['milople_subscription_type'];
		 $buyRequestArr['milople_subscription_start_date'] = $postdata['milople_subscription_start_date'];
     $buyInfo->setValue(serialize($buyRequestArr))->save();
		 $buyInfo = $cartItem->getBuyRequest();
		 if($plan_data->getData())
		 {
			if($buyInfo->getMilopleSubscriptionType()) 
			{
				if($buyInfo->getMilopleSubscriptionType()>=0)
				{
					if($product->getFirstPeriodPrice() > 0 ){
						$price = $product->getFirstPeriodPrice() ;
					}
					else{
						$termid = $buyInfo->getMilopleSubscriptionType();
						$qty = $buyInfo->getQty();
						$productId = $buyInfo->getProduct();
						$types = $this->terms->load($termid);
						$termprice = $types->getPrice();
						if($types->getPriceCalculationType() == 1){
							$termprice = $product->getPrice() * $types->getPrice()/100;
						}
						$price = $termprice + $attributePrice;
					}
					$price = $this->currencyConvertHelper->currencyConvert($price, $this->_storeManager->getStore()->getBaseCurrencyCode(), $this->_storeManager->getStore()->getCurrentCurrencyCode());
					// Get the quote item
					$item = $observer->getItem();
					// Ensure we have the parent item, if it has one
					$item = ( $item->getParentItem() ? $item->getParentItem() : $item );
			   		// Set the custom price
					$item->setQty($qty);
					$item->setCustomPrice($price);
					$item->setOriginalCustomPrice($price);
					$item->save();
					// Enable super mode on the product.
					$item->getProduct()->setIsSuperMode(true);
					if($plan_data->getStartDate() == 1)
					{
						if($buyInfo->getMilopleSubscriptionStartDate())
						{
							if(strtotime($buyInfo->getMilopleSubscriptionStartDate()) < time())
							{
								$buyInfo->setMilopleSubscriptionStartDate(date('m-d-Y'));
							}
						}
					}
					
				}else{
					// Get the quote item
					$item = $observer->getItem();
					// Ensure we have the parent item, if it has one
					$item = ( $item->getParentItem() ? $item->getParentItem() : $item );
					$product = $this->_product->load($item->getProduct()->getId());
					// Set the custom price
					$item->setQty($postdata['qty']);
					$item->setCustomPrice(($product->getPrice()+$attributePrice));//added value of custom option
					$item->setOriginalCustomPrice(($product->getPrice()+$attributePrice));//added value of custom option
					$item->save();
					// Enable super mode on the product.
					$item->getProduct()->setIsSuperMode(true);
				}
			}else{		
				if($plan_data->getPlanStatus() != 2){
					$this->messageManager->addNoticeMessage('Please specify the products option(s)');
					$resultRedirect = $this->resultRedirectFactory->create(); 
					return $resultRedirect->setPath($product->getProductUrl());
				}else{
					return ;
				}
			}
			
		}
	}
	/*  Return same object where we need model
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