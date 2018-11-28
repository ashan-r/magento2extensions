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
namespace Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Plans\Edit\Tab\Grid;
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;
	
	 /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory
     */
   // protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Backend\Helper\Data $backendHelper,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
    \Magento\Framework\Registry $coreRegistry,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Magento\Catalog\Model\Product\Type $type,
		\Magento\Catalog\Model\Product\Attribute\Source\Status $status,
		\Magento\Catalog\Model\Product\Visibility $visibility,
	    \Milople\Recurringandsubscriptionpayments\Model\Plans\Product $planProduct,
				
        array $data = []
    ) {
		$this->productFactory = $productFactory;
    $this->_coreRegistry = $coreRegistry;
		$this->_setsFactory = $setsFactory;
		$this->_type = $type;
		$this->_visibility = $visibility;
		$this->storeManager = $context->getStoreManager();
    $this->session=$context->getSession();
		$this->_status = $status;
    $this->planProduct=$planProduct;
		 parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

    }

    /**
     * @return string
     */

	public function getGridUrl() {
	    $url = $this->getUrl('*/*/productGrid',['_current' => true]);
	    if (strpos($url, 'internal_massaction') !== false) {
			$url = substr($url, 0, strpos($url, 'internal_massaction'));
        }
        return $url;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
		$this->setDefaultFilter(array('massaction' => 1));
        $store = $this->storeManager->getStore();
       
		/* Start : Not display products in grid which are assigned in other plans */
		$plan_id = $this->getRequest()->getParam('plan_id') ;
		if(!isset($plan_id))   // If already any plan is created
		{
			$product_ids = $this->planProduct->getCollection()
			->addFieldToFilter('plan_id', array('neq' => $plan_id));
		
			$items=array();
			
			if (sizeof($product_ids))
			{
				foreach ($product_ids as $product_id)
				{
					$items[] = $product_id['product_id'];	
				}
			}
			if(sizeof($items)==0)
			{
					$items[0]=0;
			}

			/* End : Not display products in grid which are assigned in other plans */
			$collection =$this->productFactory->create()->getCollection()
					->addAttributeToSelect('sku')
					->addAttributeToSelect('name')
					->addAttributeToSelect('attribute_set_id')
					->addAttributeToSelect('type_id')
					->addFieldToFilter('entity_id',array('nin' => $items))
					->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
		}
		else  // If any plan is yet not create
		{
			$collection = $this->productFactory->create()->getCollection()
			->addAttributeToSelect('sku')
			->addAttributeToSelect('name')
			->addAttributeToSelect('attribute_set_id')
			->addAttributeToSelect('type_id')
			->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
		}
		

		if ($store->getId()) {
            $collection->addStoreFilter($store);
            $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        
        $collection->getSelect()
            ->joinLeft(array('category' => $collection->getTable('catalog_category_product')),
            'e.entity_id = category.product_id',
            array('cat_ids' => 'GROUP_CONCAT(category.category_id)'))
            ->group('e.entity_id');
        
		 	$this->setCollection($collection);  
            return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
		   $this->addColumn(
                'entity_id',
                [
                    'header' => __('ID'),
                    'sortable' => true,
                    'index' => 'entity_id',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id'
                ]
            );
            $this->addColumn(
                'name',
                [
                    'header' => __('Name'),
                    'index' => 'name'
                ]
            );
			$this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->_type->getOptionArray()
            ]
       		 );
		    $sets = $this->_setsFactory->create()->setEntityTypeFilter(
            $this->productFactory->create()->getResource()->getTypeId()
       		 )->load()->toOptionHash();
		
			 $this->addColumn(
				'set_name',
				[
					'header' => __('Attribute Set'),
					'index' => 'attribute_set_id',
					'type' => 'options',
					'options' => $sets,
					'header_css_class' => 'col-attr-name',
					'column_css_class' => 'col-attr-name'
				]
        	);
				
            $this->addColumn(
                'sku',
                [
                    'header' => __('Sku'),
                    'index' => 'sku'
                ]
            );
		  	  $store = $this->storeManager->getStore();
			
       		 $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        	);
            $this->addColumn(
                'qty',
                [
                    'header' => __('Quantity'),
                    'type' => 'number',
                    'index' => 'qty'
                ]
            );
		$this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->_visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArray()
            ]
        );

        return parent::_prepareColumns();
    }
	   /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
		$this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Milople_Recurringandsubscriptionpayments::widget-grid-massaction.phtml');
		$this->getMassactionBlock()->addItem(null, array());
		
		$productIds = $this->_getSelectedProducts();
		
       if ($productIds) {
            $this->getMassactionBlock()->getRequest()->setParam($this->getMassactionBlock()->getFormFieldNameInternal(), $productIds);
        }
        return $this;
    }
	public function _getSelectedProducts()
	{
		$productIds = '';
        $session =$this->session;		
       if ($data =   $session->getData('recurringandsubscriptionpayments_data')) {
            if (isset($data['in_products'])) {
                $productIds = $data['in_products'];
            }
            $this->_coreRegistry->setData('recurringandsubscriptionpayments_data', null);
        } elseif ($this->_coreRegistry->registry('recurringandsubscriptionpayments_data_detail')) {
            $productIds = $this->_coreRegistry->registry('recurringandsubscriptionpayments_data_detail')->getData('product_ids');
        }
				
		$items = array();
		if($this->getRequest()->getParam('plan_id') > 0)
		{
			$id = $this->getRequest()->getParam('plan_id') ;
			$collection = $this->planProduct->getCollection()
				  ->addFieldToFilter('plan_id',$id);
			
			foreach ($collection as $product)
			{
				$items[] = $product['product_id'];	
			}
		}
		
		$productIds = implode(",", $items);
        return $productIds;
	}
	
}
