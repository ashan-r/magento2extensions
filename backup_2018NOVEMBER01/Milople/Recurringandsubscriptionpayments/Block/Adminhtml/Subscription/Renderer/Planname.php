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
namespace Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Subscription\Renderer;
class Planname extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	 public function __construct(
        \Magento\Backend\Block\Context $context,
        \Milople\Recurringandsubscriptionpayments\Model\Terms $terms,
        \Milople\Recurringandsubscriptionpayments\Model\Plans $plans,
         array $data = []
    ) {
        $this->terms = $terms;
        $this->plans=$plans;
        parent::__construct($context, $data);
    }
    /**
    * It will render the Plan Name
    */
	public function render(\Magento\Framework\DataObject $row) {
		$plan_id = $this->terms->load($row->getData($this->getColumn()->getIndex()))->getPlanId();
		return  $this->plans->load($plan_id)->getPlanName();
	}
	
}