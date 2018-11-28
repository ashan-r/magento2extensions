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
namespace Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Subscription;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	
	 public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory $optionValueCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
  			\Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Sequence\CollectionFactory $sequenceFactory,
 				\Magento\Framework\ObjectManagerInterface $objectManager, 
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    		) {
        $this->_optionValueCollectionFactory = $optionValueCollectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct(
					$entityFactory, 
					$logger, 
					$fetchStrategy, 
					$eventManager, 
					$connection, 
					$resource
				);
				$this->logger = $logger; 
				$this->sequenceFactory = $sequenceFactory;
				$this->_objectManager = $objectManager;
	 }
	/**
	* Define model & resource model
 	*/
	protected function _construct()
	{
		$this->_init('Milople\Recurringandsubscriptionpayments\Model\Subscription','Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Subscription');
	}	
	/**
   * Adds filter for all subscriptions that matches the customer
   */
	public function addCustomerFilter($customer)
  {
        if (!is_int($customer)) {
            $id = $customer->getId();
        } else {
            $id = $customer;
        }
        $this->getSelect()->where('customer_id=?', $id);
        return $this;
  }
 	/**
  * Adds only active subscriptions filter to collection
  */
  public function addActiveFilter()
  {
     $this->getSelect()->where('status=?', \Milople\Recurringandsubscriptionpayments\Model\Subscription::STATUS_ENABLED)->limit(1);
     return $this;
  }
	/**
   * Adds filter for all subscriptions that matching today
   */
   public function addTodayFilter()
   {
      return $this->addDateFilter();
   }
	 /**
    * Adds filter for all subscriptions that matching $date
    * @param object $date [optional]
   */
    public function addDateFilter($date = null)
    {
			$sequence = $this->sequenceFactory->create()->prepareForPayment()->addDateFilter($date);
			$in = array();
						foreach ($sequence as $record) {
							$in[] = $record->getSubscriptionId();
					}
					if (!sizeof($in)) {
							// No subscriptions are present
							$in[] = -1;
					}
					$this->getSelect()->where('id IN (' . implode(',', $in) . ')');
					return $this;
		}
}
