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
 use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
/**
 * Blog post edit form main tab
 */
class Grid extends  \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
 
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
	protected $_template = 'widget-grid.phtml';
    protected $_status;
 	protected $plan_productFactory;
    
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
		\Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Plans\Product\CollectionFactory $plan_productFactory,
        \Magento\Catalog\Model\Product $product,
        AccountManagementInterface $customerAccountManagement,
		array $data = []
    ) {
        $this->_subscriberFactory = $subscriberFactory;
        $this->customerAccountManagement = $customerAccountManagement;
		$this->plan_productFactory = $plan_productFactory;
        $this->product=$product;
		parent::__construct($context, $registry, $formFactory, $data);
    }
	
	public function getPlansProducts()
	{	
		 $collection = $this->plan_productFactory->create();
		 $collection->addFieldToFilter(
            'plan_id',
            array('eq' => $this->getRequest()->getParam('plan_id'))
        );
		return $collection;
	}
	 protected function _prepareCollection() 
	 {
        $this->logger->addDebug('_prepareCollection'); 
	    $collection = $this->_productFactory->create()->getCollection();
		 
        $this->grid->setCollection($collection);
 
        return parent::_prepareCollection();
    
	  
    }
	 protected function _prepareColumns() {
	 $this->addColumn(
            'name',
            [
                'header' => __('Product'),
                'renderer' => 'Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product',
                'index' => 'name'
            ]
        );

	 }
  
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Products');
    }
 
    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Products');
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
	
	
	  /**
     * Initialize the form.
     *
     * @return $this
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_newsletter');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Newsletter Information')]);
        $this->setForm($form);
        return $this;
    }
	/**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Plans\Edit\Tab\Grid\Grid',
                'plans.grid'
            )
        );
        parent::_prepareLayout();
        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }

}