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
namespace Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Subscription;
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
     /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    protected $_gridFactory;
    protected $_status;
 
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Webkul\Grid\Model\GridFactory $gridFactory
     * @param \Webkul\Grid\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Milople\Recurringandsubscriptionpayments\Model\SubscriptionFactory $subscriptionFactory,
				\Milople\Recurringandsubscriptionpayments\Model\Subscriptionstatus $status,
	   		\Milople\Recurringandsubscriptionpayments\Model\Termtitle $terms,
        \Magento\Framework\Module\Manager $moduleManager,
	    array $data = []
    ) {
        $this->_gridFactory = $subscriptionFactory;
        $this->_status = $status;
				$this->_terms = $terms;
        $this->moduleManager = $moduleManager;
	    	parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('grid_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
	    	$collection = $this->_gridFactory->create()->getCollection();
				$collection->setOrder('parent_order_id','DSC');
        $this->setCollection($collection);
 				parent::_prepareCollection();
        return $this;
    }
 
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
   		  $this->addColumn(
            'parent_order_id',
            [
                'header' => __('Order Id'),
                'index' => 'parent_order_id',
                'class' => 'xxx'
            ]
        );
		 $this->addColumn(
            'products_text',
            [
                'header' => __('Subscribed Products'),
                'index' => 'products_text',
                'class' => 'xxx'
            ]
        );
		$this->addColumn(
            'customer_name',
            [
                'header' => __('Customer Name'),
                'index' => 'customer_name',
                'class' => 'xxx'
            ]
        );
		
		$this->addColumn(
            'plan_name',
            [
                'header' => __('Subscribed Plan'),
                'index' => 'term_type',
                'class' => 'xxx',
				'renderer' => 'Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Subscription\Renderer\Planname',
            ]
        );
		$this->addColumn(
            'term_type',
            [
                'header' => __('Subscribed Term'),
                'index' => 'term_type',
								'type' => 'options',
			   			 'options' => $this->_terms->getAllOptions(),

            ]
        );
		$this->addColumn(
            'next_payment_date',
            [
                'header' => __('Upcoming Payment Date'),
                'index' => 'next_payment_date',
				'type' => 'date',
                'class' => 'xxx'
            ]
        );
		$this->addColumn(
            'date_expire',
            [
                'header' => __('Subscription Expiry Date'),
                'index' => 'date_expire',
				'type' => 'date',
                'class' => 'xxx'
            ]
        );
		$this->addColumn(
            'status',
            [
                'header' => __('Subscription Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArray()
            ]
        );
		
        $this->addColumn(
            'edit',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit'
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
 
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
 
        return parent::_prepareColumns();
    }
 
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('subscriptions');
        $this->getMassactionBlock()->setFormFieldName('subscriptions');
 
		return $this;  
	  }
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('subscription/*/grid', ['_current' => true]);
    }
 
    
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            ['id' => $row->getId()]
        );
    }
}
