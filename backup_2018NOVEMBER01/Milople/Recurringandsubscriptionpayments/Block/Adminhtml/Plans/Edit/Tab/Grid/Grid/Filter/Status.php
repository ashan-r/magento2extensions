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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Filter;
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = [
            null => null,
            Queue::STATUS_SENT => __('Sent'),
            Queue::STATUS_CANCEL => __('Cancel'),
            Queue::STATUS_NEVER => __('Not Sent'),
            Queue::STATUS_SENDING => __('Sending'),
            Queue::STATUS_PAUSE => __('Paused'),
        ];
        parent::_construct();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [];
        foreach (self::$_statuses as $status => $label) {
            $options[] = ['value' => $status, 'label' => __($label)];
        }

        return $options;
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        return $this->getValue() === null ? null : ['eq' => $this->getValue()];
    }
}
