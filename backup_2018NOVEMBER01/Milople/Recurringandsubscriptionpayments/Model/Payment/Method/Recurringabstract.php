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
use Magento\Framework\DataObject;


abstract class Recurringabstract extends DataObject implements \Milople\Recurringandsubscriptionpayments\Model\Payment\Method\Recurringinterface
{

    /**
     * This function is run when subscription is created and new order creates
     * @param \Milople\Recurringandsubscriptionpayments\Model $Subscription
     * @param \Magento\Sales\Model\Orde   $Order
     * @param \Magento\Quote\Api\CartRepositoryInterface    $Quote
     * @return Indies_Recurringandrentalpayments_Model_Payment_Method_Abstract
     */
    public function onSubscriptionCreate(\Milople\Recurringandsubscriptionpayments\Model\Subscription $Subscription, \Magento\Sales\Model\Order $Order, \Magento\Quote\Model\Quote $Quote)
    {
        return $this;
    }
}