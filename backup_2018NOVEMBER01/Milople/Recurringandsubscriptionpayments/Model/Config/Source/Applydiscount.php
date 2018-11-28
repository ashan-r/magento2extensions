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
class Applydiscount implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    
    public function toOptionArray()
    {
  	return array(
            array(
                'value' => '1',
                'label' => __('All Terms')
            ),
            array(
                'value' => '2',
                'label' => __('First Term')
            ),
			array(
                'value' => '3',
                'label' => __('All Except First Term')
            )
			
        );

    }
}
