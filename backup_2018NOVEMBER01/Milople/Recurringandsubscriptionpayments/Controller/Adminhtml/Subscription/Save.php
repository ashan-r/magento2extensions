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
namespace Milople\Recurringandsubscriptionpayments\Controller\Adminhtml\Subscription;
use Magento\Backend\App\Action;
 
class Save extends \Magento\Backend\App\Action
{
	 public function __construct(
     Action\Context $context,
		 \Psr\Log\LoggerInterface $log,
		 \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		 \Milople\Recurringandsubscriptionpayments\Helper\EmailSender $emailSender,
		 \Milople\Recurringandsubscriptionpayments\Model\Subscription $subscription
    ) {
		  $this->logger=$log;
		 	$this->scopeConfig= $scopeConfig;
		  $this->subscription=$subscription;
		 	$this->emailSender=$emailSender;
      parent::__construct($context);
    }
	 /**
	 * @return void
	 */
   public function execute()
   {
    if ($id=$this->getRequest()->getParam('id')) 
	  {
		  $data = $this->getRequest()->getPost();
		  $model = $this->subscription->load($id);
		  $status = $model->getStatus();
		  try
		  {
				
			  $model->setStatus($data['lastorder']['status']);
			  $model->save();
				$admin_status = $this->getConfig('recurringandsubscription/subscription_status_change_email/subscriptionstatus');
				$admin_status_array = explode(',',$admin_status);
					switch ($data['lastorder']['status'])
					{
						
						case 1:
								if(in_array('active', $admin_status_array))
								{
										$this->emailSender->processStatusChangeEmails($model,'Subscription Active Status');
								}
								break;
						case 2:
								if(in_array('suspended', $admin_status_array))
								{
										$this->emailSender->processStatusChangeEmails($model,'Subscription Suspend Status');
								}
								break;
						case 3:
								if(in_array('suspended', $admin_status_array))
								{
										$this->emailSender->processStatusChangeEmails($model,'Subscription Suspend Status');
								}
								break;
						case 0:
								if(in_array('expired', $admin_status_array))
								{
										$this->emailSender->processStatusChangeEmails($model,'Subscription Expire Status');
								}
								break;
						case -1:
								if(in_array('cancelled', $admin_status_array))
								{
										$this->emailSender->processStatusChangeEmails($model,'Subscription Cancel Status');
								}
								break;
				}
			  $this->_redirect('*/*/');
			  return;
		  }
		  catch (\Exception $e)
		  {
				 $this->logger->addDebug("Exception". $e); 
		  }
	  
	  } 
   }
	/**
   * Get Recurring Cofing
   * @return true/false
   */
	 public function getConfig ($config){
		return $this->scopeConfig ->getValue($config,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
}
