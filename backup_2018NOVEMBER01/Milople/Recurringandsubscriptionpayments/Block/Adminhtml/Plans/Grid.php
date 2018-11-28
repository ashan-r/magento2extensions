<?php
namespace Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Plans;
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
     /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
 
    /**
     * @var \Webkul\Grid\Model\GridFactory
     */
    protected $_gridFactory;
 
    /**
     * @var \Webkul\Grid\Model\Status
     */
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
        \Milople\Recurringandsubscriptionpayments\Model\PlansFactory $plansFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_gridFactory = $plansFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('plan_id');
        $this->setDefaultSort('plan_id');
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
            'plan_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'plan_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'plan_name',
            [
                'header' => __('Plan Name'),
                'index' => 'plan_name',
                'class' => 'xxx'
            ]
        );
 
        $this->addColumn(
            'is_normal',
            [
                'header' => __('Allow Purchase as Normal Product'),
                'index' => 'is_normal',
				'type' => 'options',
				'options'   => array(
					  1 => 'Yes',
					  0 => 'No',
					),
            ]
        );
		 $this->addColumn(
            'creation_time',
            [
                'header' => __('Created At'),
                'index' => 'creation_time'
            ]
        );
		 $this->addColumn(
            'plan_status',
            [
                'header' => __('Status'),
                'index' => 'plan_status',
				'type' => 'options',
				'options'   => array(
					  1 => 'Enable',
					  2 => 'Disable',
				  ),
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
                        'field' => 'plan_id'
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
 
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('plan_id');
        $this->getMassactionBlock()->setFormFieldName('plan_id');
 
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('recurringandsubscriptionpayments/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
 
        return $this;
    }
 
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('plans/*/grid', ['_current' => true]);
    }
 
    
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            ['plan_id' => $row->getId()]
        );
    }
}
