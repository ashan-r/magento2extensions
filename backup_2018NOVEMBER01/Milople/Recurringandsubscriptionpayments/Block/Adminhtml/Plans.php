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
namespace Milople\Recurringandsubscriptionpayments\Block\Adminhtml;
class Plans extends \Magento\Backend\Block\Widget\Container
{
    /**
    * @var string
    */
    protected $_template = 'plans.phtml';
    /**
    * @param \Magento\Backend\Block\Widget\Context $context
    * @param array $data
    */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and gridCreate Grid , edit/add grid row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
       $addButtonProps = [
            'id' => 'add_new_grid',
            'label' => __('Add Plan'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);
        $this->setChild(
            'grid',
           $this->getLayout()->createBlock('Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Plans\Grid', 'grid.view.grid')
        );
        return parent::_prepareLayout();
    }
    /**
    * @return array
    */
    protected function _getAddButtonOptions()
    {
        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
        return $splitButtonOptions;
    }
    /**
    * @param string $type
    * @return string
    */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'recurringandsubscriptionpayments/*/new'
        );
    }
    /**
    * Render grid
    * @return string
    */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
