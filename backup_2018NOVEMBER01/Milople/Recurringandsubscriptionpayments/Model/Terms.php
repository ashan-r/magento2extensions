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
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
class Terms extends AbstractModel
{
	const TERMSPER_DAY  = 'day';
	const TERMSPER_WEEK  = 'week';
	const TERMSPER_MONTH  = 'month';
	const TERMSPER_YEAR  = 'year';
	const PERIOD_TYPE_NONE = -1;
	const PRICE_CALC_TYPE_FIXED = 0;
	const PRICE_CALC_TYPE_PER = 1;
	 public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager, 
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory $valueCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_valueCollectionFactory = $valueCollectionFactory;
				$this->_objectManager = $objectManager;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }
	/**
	 * Define resource model
	 */
	protected function _construct()
	{
		$this->_init('Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Terms');
	}
	# Is subscription infinite
	public function isInfinite()
  {
      if($this->getNoofterms()==0)
			{	
					return true;
			}
			else
			{
				return  false;	
			}
   }
	# Is term ks valid
	 public function validate()
   {
		if (((int)$this->getRepeateach()) < 1) {
			throw new LocalizedException(__("Terms must be more 0"));
        }
        return $this;
    }
	
}
