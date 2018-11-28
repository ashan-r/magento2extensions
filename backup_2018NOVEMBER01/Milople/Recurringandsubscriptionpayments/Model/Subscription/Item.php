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
namespace Milople\Recurringandsubscriptionpayments\Model\Subscription;
use Magento\Framework\Model\AbstractModel;
class Item extends AbstractModel
{
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
		$this->_init('Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Subscription\Item');
	}
	/**
	* Get order item.
  */
	public function getOrderItem()
  {
		if (!$this->getData('order_item'))
		{
      $this->setOrderItem($this->_objectManager->get('Magento\Sales\Model\Order\Item')->load($this->getPrimaryOrderItemId()));
	 	}
    return $this->getData('order_item');
  }
}