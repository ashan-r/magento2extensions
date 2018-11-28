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

namespace Milople\Recurringandsubscriptionpayments\Model\Config\Backend;
class Validation extends \Magento\Framework\App\Config\Value
{
	public static $discountamount;
	public function __construct(
				\Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
       	array $data = []	 //log injection        
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    } 
	 public function beforeSave()
   {
    	 $amount = $this->getValue();
    	 $data = $this->getData();
  		if($amount != '')
			{
				if($data['groups']['discount_group']['fields']['cal_type']['value'] !=1) {
					self::$discountamount = $amount;

					if (self::$discountamount < 0 || self::$discountamount > 99 ) {
						throw new \Magento\Framework\Exception\LocalizedException(__('Discount amount value must be between 0 to 99.'));
					}
				}
			}
			else
			{
				throw new \Magento\Framework\Exception\LocalizedException(__('Please Enter Discount Amount.'));
			}
	  }
}