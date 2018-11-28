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
namespace Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Plans;
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
 
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
	    parent::__construct($context, $data);
    }
 
    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_objectId = 'plan_id';
        $this->_blockGroup = 'Milople_Recurringandsubscriptionpayments';
        $this->_controller = 'adminhtml_plans';
 
        parent::_construct();
 
  
		 $this->buttonList->update(
            'save',
            'data_attribute',
			      ['mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']]]
        );
			$this->buttonList->update('save', 'onclick', 'saveOptionsForm()');
		  $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
								'onclick' => 'saveAndContinueEdit()',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'save', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );
		
 		$this->_formScripts[] = "
            function saveOptionsForm() {
                applySelectedProducts('save')
            }
            function saveAndContinueEdit() {
                applySelectedProducts('saveandcontinue')
            }
        ";
        
    }
 
    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('recurringandsubscriptionpayments_data')->getId()) {
            return __("Edit Plan '%1'", $this->escapeHtml($this->_coreRegistry->registry('recurringandsubscriptionpayments_data')->getPlanName()));
        } else {
            return __('Add New Plan');
        }
    }
 
    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('milople/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
 
    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
 
}