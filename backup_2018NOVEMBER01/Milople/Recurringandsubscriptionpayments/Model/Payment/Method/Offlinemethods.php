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
namespace Milople\Recurringandsubscriptionpayments\Model\Payment\Method;
use Milople\Recurringandsubscriptionpayments\Model\Payment\Method\Recurringabstract;
class Offlinemethods extends Recurringabstract
{
	 	/**
    * Processes payment for specified order
    * @param \Magento\Sale\Model\Order $Order
    * @return
    */
    public function processOrder(\Magento\Sale\Model\Order $PrimaryOrder, \Magento\Sale\Model\Order $Order = null)
    {
        // Set order as pending
        $Order->addStatusToHistory('pending', '', false)->save();
    }
		/**
    * Returns service subscription service id for specified quote
    * @param mixed $quoteId
    * @return int
    */
    public function getSubscriptionId($OrderItem)
    {
        return 1;
    }

}