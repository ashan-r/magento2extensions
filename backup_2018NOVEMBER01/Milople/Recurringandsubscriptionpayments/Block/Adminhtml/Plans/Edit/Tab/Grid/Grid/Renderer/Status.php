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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Renderer;
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * Constructor for Grid Renderer Status
     *
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = [
            \Magento\Newsletter\Model\Queue::STATUS_SENT => __('Sent'),
            \Magento\Newsletter\Model\Queue::STATUS_CANCEL => __('Cancel'),
            \Magento\Newsletter\Model\Queue::STATUS_NEVER => __('Not Sent'),
            \Magento\Newsletter\Model\Queue::STATUS_SENDING => __('Sending'),
            \Magento\Newsletter\Model\Queue::STATUS_PAUSE => __('Paused'),
        ];
        parent::_construct();
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return __($this->getStatus($row->getQueueStatus()));
    }

    /**
     * @param string $status
     * @return \Magento\Framework\Phrase
     */
    public static function getStatus($status)
    {
        if (isset(self::$_statuses[$status])) {
            return self::$_statuses[$status];
        }

        return __('Unknown');
    }
}
