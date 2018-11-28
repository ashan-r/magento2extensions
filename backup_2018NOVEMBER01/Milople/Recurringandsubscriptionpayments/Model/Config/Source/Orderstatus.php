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
namespace Milople\Recurringandsubscriptionpayments\Model\Config\Source;
class Orderstatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */

    public function toOptionArray()
    {
  	return array(
            array(
                'value' => 'pending',
                'label' => __('Order Placement')
            ),
            array(
                'value' => 'processing',
                'label' => __('Invoice Generation')
            ),
	    array(
                'value' => 'complete',
                'label' => __('Order Completion')
            ),
	    array(
                'value' => 'manuallybyadmin',
                'label' => __('Manually by Admin')
            )
        );

    }
}
