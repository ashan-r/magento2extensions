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
class Sequence extends AbstractModel
{
	const TERMSPER_DAY  = 'day';
	const TERMSPER_WEEK  = 'week';
	const TERMSPER_MONTH  = 'month';
	const TERMSPER_YEAR  = 'year';
	const PERIOD_TYPE_NONE = -1;
	const PRICE_CALC_TYPE_FIXED = 0;
	const PRICE_CALC_TYPE_PER = 1;
	const STATUS_PENDING = 'pending';
  /** Status 'pending for payment' */
  const STATUS_PENDING_PAYMENT = 'pending_payment';
  /** Status 'paid' */
  const STATUS_PAYED = 'paid';
  /** Status 'failed' */
  const STATUS_FAILED = 'failed';
	 public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager, 
		\Magento\Framework\Model\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
    \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	 )
	 {
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
		$this->_init('Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Sequence');
	}
	/**
   * Returns assigned order instance
   */
   public function getOrder()
   {
        if (!$this->getData('order') && $this->getOrderId()) {
            $this->setOrder($this->_objectManager->get('Magento\Sales\Model\Order')->load($this->getOrderId()));
        } elseif (!$this->getData('order')) {
            $this->setOrder($this->_objectManager->create('Magento\Sales\Model\Order'));
        }
        return $this->getData('order');
    }
}
