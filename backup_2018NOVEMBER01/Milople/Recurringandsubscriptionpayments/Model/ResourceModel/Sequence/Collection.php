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
namespace Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Sequence;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	 public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory $optionValueCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
 				\Magento\Framework\ObjectManagerInterface $objectManager, 
 				\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localedate,
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
 			$this->_objectManager = $objectManager;
  	  $this->localeDate = $localedate;
	 }
	/**
	 * Define model & resource model
 	*/
	protected function _construct()
	{
			$this->_init(
		'Milople\Recurringandsubscriptionpayments\Model\Sequence','Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Sequence'
			);
	}	
	/**
  * Adds filter by subscription
	 */
  public function addSubscriptionFilter(\Milople\Recurringandsubscriptionpayments\Model\Subscription $subscription)
  {
     $this->getSelect()->where('subscription_id=?', $subscription->getId());
     return $this;
  }
	/**
  * Adds status filter by subscription
	*/
	public function addStatusFilter($status)
  {
				$this->getSelect()->where('status=?', $status);
        return $this;
  }
 	/**
  * Prepares collection for payment selection
  */
  public function prepareForPayment()
  {
		return $this->addStatusFilter(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_PENDING);
  }
	/**
  * Adds filter by date
  * @param mixed $date [optional]
  */
  public function addDateFilter($date = null)
  {	
		if ($date) {
      $date = $this->localeDate ->formatdate($this->localeDate->date($date),\IntlDateFormatter::SHORT);
     } else {
      $date = $this->localeDate ->formatdate($this->localeDate->date(),\IntlDateFormatter::SHORT);
     }
				$date = date("Y-m-d", strtotime($date));
        $this->logger->addDebug($date);
        $this->getSelect()
        ->where('date=?', $date);
        return $this;
    }
}
