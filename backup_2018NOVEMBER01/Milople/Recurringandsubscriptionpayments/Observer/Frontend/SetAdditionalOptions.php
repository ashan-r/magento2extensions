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
use Magento\Framework\App\RequestInterface;
class SetAdditionalOptions implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
    * @param RequestInterface $request
    */
    public function __construct(
        RequestInterface $request,
		\Milople\Recurringandsubscriptionpayments\Helper\Data $_helper,
        \Psr\Log\LoggerInterface $logger
    ) {
         $this->_request = $request;
         $this->_helper = $_helper;
         $this->logger = $logger;
    }
    /**
    * @param \Magento\Framework\Event\Observer $observer
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // Check and set information according to your need
       $postdata = $this->_request->getPost();
        if ($this->_request->getFullActionName() == 'checkout_cart_add' || $this->_request->getFullActionName() == 'checkout_cart_updateItemOptions' || $this->_request->getFullActionName() == 'sales_order_create_loadBlock' || $this->_request->getFullActionName() == 'sales_order_create_save') { 
            //checking when product is adding to cart
           $additionalOptions = [];
            if(isset($postdata['milople_subscription_type']) && $postdata['milople_subscription_type']>=0){
                $additionalOptions[] = array(
                    'label' => __('Subscription Type'),
                    'value' => $postdata['milople_subscription_type_label'],
                );
                $additionalOptions[] = array(
                    'label' => __('Subscription Start'),
                    'value' => $postdata['milople_subscription_start_date'],
                );
               $observer->getProduct()->addCustomOption('additional_options',$this->_helper->getSerializeData($additionalOptions));
            }    
           
        }
    }
}