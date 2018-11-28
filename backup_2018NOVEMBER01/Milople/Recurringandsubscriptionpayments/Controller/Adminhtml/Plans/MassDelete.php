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
use Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Plans\CollectionFactory;
class MassDelete extends \Magento\Backend\App\Action
{
      /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
      \Magento\Backend\App\Action\Context $context,
      \Magento\Ui\Component\MassAction\Filter $filter, 
      \Milople\Recurringandsubscriptionpayments\Model\Plans $plans,
      CollectionFactory $collectionFactory) {
        $this->filter = $filter;
        $this->plans=$plans;
      
        parent::__construct($context);
    }
   /**
    * @return void
    */
   public function execute()
   {
      // Get IDs of the selected news
      $plan_ids = $this->getRequest()->getParam('plan_id');
      foreach ($plan_ids as $plan_id) {
            try {
               /** @var $newsModel \Mageworld\SimpleNews\Model\News */
                $plan = $this->plans;
                $plan->load($plan_id)->delete();
            }catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        if (count($plan_ids)) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were deleted.', count($plan_ids))
            );
        }
 
        $this->_redirect('*/*/index');
   }
}