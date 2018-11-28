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
namespace Milople\Recurringandsubscriptionpayments\Plugin;
use Magento\Framework\App\RequestInterface;
class QuoteItemToOrderItemPlugin
{
  public function __construct(
        RequestInterface $request,
		\Milople\Recurringandsubscriptionpayments\Helper\Data $_helper,
        \Psr\Log\LoggerInterface $logger
    ) {
         $this->_request = $request;
         $this->logger = $logger;
		 $this->_helper = $_helper;
    }
    // Convert quote item's options to order item's option.
    public function aroundConvert(\Magento\Quote\Model\Quote\Item\ToOrderItem $subject, callable $proceed, $quoteItem, $data)
    {
        // get order item
        $orderItem = $proceed($quoteItem, $data);
        if(!$orderItem->getParentItemId()){
            if ($additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options')) {
                 if($additionalOptionsOrder = $orderItem->getProductOptionByCode('additional_options')){
                    $additionalOptions = array_merge($additionalOptionsQuote, $additionalOptionsOrder);
                 }
                 else{
                    $additionalOptions = $additionalOptionsQuote;
                 }
                if(count($additionalOptions) > 0){
                    $options = $orderItem->getProductOptions();
                    $options['additional_options'] = $this->_helper->getUnserializeData($additionalOptions->getValue());
                    $orderItem->setProductOptions($options);
                }
            }
        }
        return $orderItem;
    }
}