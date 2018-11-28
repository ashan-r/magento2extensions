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
namespace Milople\Recurringandsubscriptionpayments\Controller\Adminhtml\Plans;
use Magento\Backend\App\Action;
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
     /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Milople\Recurringandsubscriptionpayments\Model\Plans $plans,
        \Milople\Recurringandsubscriptionpayments\Model\Plans\Product $planProduct
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->plans=$plans;
        $this->session=$context->getSession();
        $this->planProduct=$planProduct;
		 parent::__construct($context);
    }
 		/**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
 	 	/**
     * Init actions
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Milople_Recurringandsubscriptionpayments::plans')
            ->addBreadcrumb(__('Grid'), __('Grid'))
            ->addBreadcrumb(__('Manage Grid'), __('Manage Grid'));
        return $resultPage;
    }
 
    /**
     * Edit grid record
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
         // 1. Get ID and create model
        $id = $this->getRequest()->getParam('plan_id');
        $model = $this->plans->load($id);
 	 	   $data_detail =  $this->planProduct->getCollection()
				  ->addFieldToFilter('plan_id',$id);
				// 2. Initial checking
				if (sizeof($model)> 0 || $id == 0) 
				{
					$data = $this->session->getFormData(true);
					if (!empty($data)) {
						$model->setData($data);
					}
          
       	   // 4. Register model to use later in blocks
					$this->_coreRegistry->register('recurringandsubscriptionpayments_data', $model);// here product_plan table data is pass
					$this->_coreRegistry->register('recurringandsubscriptionpayments_data_detail', $data_detail);// here plan details is passed
			
					 // 5. Build edit form
      		 /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
					$resultPage = $this->_initAction();
					$resultPage->addBreadcrumb(
					$id ? __('Edit Post') : __('New Condition'),
					$id ? __('Edit Post') : __('New Condition')
				 );
					$resultPage->getConfig()->getTitle()->prepend(__('Manage Plan'));
       		$resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? 'Edit Plan "' .$model->getPlanName().'"' : __('Add New Plan'));
  		 	return $resultPage;
			}
			else
			{
				 	$this->messageManager->addError(__('This grid record no longer exists.'));
           /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
           $resultRedirect = $this->resultRedirectFactory->create();
      		 return $resultRedirect->setPath('*/*/');
			}
    }
}