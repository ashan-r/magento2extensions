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
namespace Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Subscription\Edit\Tab;
use Magento\Sales\Model\Order\Address\Renderer;
class Summary extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
    protected $_template = 'summary.phtml';
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
		\Milople\Recurringandsubscriptionpayments\Model\Subscription\Item $item,
		Renderer $addressRenderer,
        \Milople\Recurringandsubscriptionpayments\Model\Terms $terms,
        \Magento\Customer\Model\Group $group,
        \Milople\Recurringandsubscriptionpayments\Model\Plans $plans,
        \Milople\Recurringandsubscriptionpayments\Model\Subscription $subscription,
        \Magento\Sales\Model\Order $order,
		\Magento\Framework\Pricing\Helper\Data $currency,
			//\Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
				//$this->logger = $logger;
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
		$this->order=$order;
        $this->item=$item;
        $this->terms=$terms;
        $this->plans=$plans;
        $this->group=$group;
        $this->subscription=$subscription;
		$this->addressRenderer = $addressRenderer;
		$this->_currency = $currency;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
 
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Subscription Information');
    }
 
    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Subscription Information');
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
     * Get Order detail of subscription item
     *
     * @param order id
     */
	public function getOrder()
	{
		$id = $this->getRequest()->getParam('id');
		$orderid = $this->item->load($id,'subscription_id');
		$order_collection = $this->order->load($orderid->getPrimaryOrderId(),'increment_id');
		return $order_collection;
		
	}
     /**
     * Get plan detail of specific subscription
     *
     * @param suscription id
     */
	public function getPlans()
	{
		$termid= $this->subscription->load($this->getRequest()->getParam('id'))->getTermType();
		$plan =$this->terms->load($termid)->getPlanId();
		return $this->plans->load($plan);
	}
     /**
     * Get detail of specific subscription
     *
     * @param suscription id
     */
	public function getSubscription()
	{
		return $this->subscription->load($this->getRequest()->getParam('id'));	
	}
     /**
     * Get terms detail of specific subscription
     *
     * @param suscription id
     */
	public function getTerms()
	{
		$termid= $this->subscription->load($this->getRequest()->getParam('id'))->getTermType();
		return $this->terms->load($termid);
	}
    /**
    * Get customer groupe code
    *
    */
	public function getCustomerGroupName()
    {
        if ($this->getOrder()) {
	      return $this->group->load((int)$this->getOrder()->getCustomerGroupId())->getCode();
        }
        return null;
    }
    /**
    * Get items collection of order
    */
	public function getItemsCollection()
    {
        return $this->getOrder()->getItemsCollection();
    }
}