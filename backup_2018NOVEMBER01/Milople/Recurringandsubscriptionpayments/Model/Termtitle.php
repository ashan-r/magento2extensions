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
namespace Milople\Recurringandsubscriptionpayments\Model;
class Termtitle extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
		public static $options;
    public static $options_by_id;
    protected $_collection = false;
	   public function __construct(
			\Magento\Framework\ObjectManagerInterface $objectManager
			)
    {
				$this->_objectManager = $objectManager;
  	}
	  # Get terms collection
		public function getCollection()
		{
			if (!$this->_collection) {
							$this->_collection = $this->_objectManager->get('\Milople\Recurringandsubscriptionpayments\Model\Terms')->getCollection();
					}
					return $this->_collection;
		}
	 # Get all options
    public function getAllOptions()
    {
				if (!self::$options)
				{
						self::$options_by_id = array();
									$_options = array();
						$periods = $this->getCollection();
						if (false)
						{
								$periods->addItem($this->_objectManager->get('\Milople\Recurringandsubscriptionpayments\Model\Terms')->setId('-1')->setName(__('No subscription')));
						 }
						foreach ($periods as $Period) 
						{
										$_options[] = ['value' => $Period->getId(), 'label' => $Period->getLabel()]; 
										self::$options_by_id[$Period->getId()] = $Period->getLabel();
						}
						self::$options = $_options;
				}
						$out = array();
						foreach (self::$options as $item) {
								$out[$item['value']] = $item['label'];
						}
					return $out ;
			}	
}
