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
namespace Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Plans\Edit\Tab;
class Planinfo extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
 
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
 
    protected $_status;
 
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
		array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
		parent::__construct($context, $registry, $formFactory, $data);
    }
 
    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
	    $model = $this->_coreRegistry->registry('recurringandsubscriptionpayments_data');
        $isElementDisabled = false;
       /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Plan Information')]);
        if ($model->getId()) {
            $fieldset->addField('plan_id', 'hidden', ['name' => 'plan_id']);
        }
        $fieldset->addField(
            'plan_name',
            'text',
            [
                'name' => 'plan_name',
                'label' => __('Plan Name'),
                'title' => __('Plan Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
    	 $fieldset->addField(
            'is_normal',
            'select',
            [
                'name' => 'is_normal',
                'label' => __('Allow Purchase as Normal Product'),
                'title' => __('Allow Purchase as Normal Product'),
                'required' => true,
				'values'    => array(
					array(
					  'value'     => '1',
					  'label'     => __('Yes'),
				  ),
					array(
						  'value'     => '0',
						  'label'     => __('No'),
					  ),
          		),
            ]
        );
		$fieldset->addField(
            'start_date',
            'select',
            [
                'name' => 'start_date',
                'label' => __('Subscription Start Date'),
                'title' => __('Subscription Start Date'),
                'required' => true,             
				'values'   =>  array(
					  array(
						  'value'     => '1',
						  'label'     => __('Selected by Customer'),
					  ),
					array(
						  'value'     => '2',
						  'label'     => __('Moment of Purchase'),
					  ),
					  array(
						  'value'     => '3',
						  'label'     => __('First Day of Next Month'),
					  ),
         		 ),
            ]
        );
		$fieldset->addField(
            'plan_status',
            'select',
            [
                'name' => 'plan_status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
				'values'    => array(
					  array(
						  'value'     => '1',
						  'label'     => __('Enable'),
					  ),
					array(
						  'value'     => '2',
						  'label'     => __('Disable'),
					  ),
        		  ),
            ]
        );

 
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }
 
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
 
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Plan Information');
    }
 
    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Plan Information');
    }
 
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }
 
    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
 
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}