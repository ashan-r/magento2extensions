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
* @url         https://www.milople.com/magento2-extensions/partial-payment-m2.html
*
**/
namespace Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Order\Create\Items;

/**
 * Adminhtml sales order create items grid block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * This is overriden to display partial payment setting Admin Order create
 */
class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid
{
    public function __construct(
       	\Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\GiftMessage\Model\Save $giftMessageSave,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\GiftMessage\Helper\Message $messageHelper,
				\Magento\Catalog\Model\Product $productModel,
				\Magento\Checkout\Model\Session $checkoutSession,
				\Magento\Customer\Model\CustomerFactory $customerFactory,
				\Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
				\Milople\Recurringandsubscriptionpayments\Helper\Data $data_helper,
				\Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $recurring_helper,
				\Milople\Recurringandsubscriptionpayments\Block\Adminhtml\Order\Create\Items\Recurringoptions $recurringoptions,
				//\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localedate,
				\Magento\Framework\App\Request\Http $request,
				//\Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
		$this->request = $request;
		$this->localeDate = $context->getLocaleDate();
		//$this->logger = $logger;
		$this->helper = $data_helper;
		$this->recurring_helper = $recurring_helper;
		$this->productModel = $productModel;
		$this->checkoutSession = $checkoutSession;
		$this->customerFactory = $customerFactory;
		$this->objectManager = $objectManager;
		$this->recurringoptions = $recurringoptions;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency,$wishlistFactory, $giftMessageSave, $taxConfig, $taxData, $messageHelper, $stockRegistry, $stockState, $data);
		
		#partial payment settings
		$this->isValid = $this->helper->canRun();
		$this->status = $this->recurring_helper->isEnabled();
    }
	
	public function getRecurringData($isProduct,$item=NULL)
	{
		$product_id = $item->getProductId();
		$plans_product = $this->recurringoptions->getPlanProducts()->load($product_id,'product_id');
		$plans = $this->recurringoptions->getPlan()->getCollection()->addFieldToFilter('plan_id',$plans_product->getPlanId());
		$planid=0;
		if ($plans->count()>0)
			$planid = 0;
		foreach($plans as $plan)
		{
			$planid=$plan->getPlanId();
			$isnormal=$plan->getIsNormal();
		}
		$type = $this->recurringoptions->getTerms()->getCollection()->addFieldToFilter('plan_id',$planid);
		$html='<select class="recurring admin__control-select" id="milople_select_subscription_type_'.$product_id.'" name=item['.$product_id.'][milople_select_subscription_type]>
					<option value="0">'.__('None').'</option>';
		foreach($type as $plan_item)
		{
			$html .= '<option value="'.$plan_item->getId().'">'.__(''.$plan_item->getLabel().'').'</option>';
		}
		$html .= '</select>';
		return $html;
	}
	public function getCalendaradminhtml($_item)
	{
		return $this->recurringoptions->getCalendarHtml($_item);
	}
	public function ifrecurringproduct($item){
		$product_id = $item->getProductId();
		$plans_product = $this->recurringoptions->getPlanProducts()->load($product_id,'product_id');
		$plans = $this->recurringoptions->getPlan()->getCollection()->addFieldToFilter('plan_id',$plans_product->getPlanId());
		$planid=0;
		if ($plans->count()>0)
			$planid = 0;
		foreach($plans as $plan)
		{
			$planid=$plan->getPlanId();
		}
		return $planid;
	}
	public function get_select_subs_type($item){
		if($item == NULL){
			return '';
		}
		$subscription_type='';
		$product_id = $item->getProductId();
		$buyRequest = $item->getBuyRequest();
		$options = $item->getOptions();
		$postdata = $this->request->getPost();
		$items = $postdata['item'];
		foreach ($options as $option) 
		{
			if($option->getCode() == 'info_buyRequest')
			{
				if(isset($items[$product_id]['milople_select_subscription_type'])){
					$subscription_type = $items[$product_id]['milople_select_subscription_type'];
				}
			}
		}
		return $subscription_type;
	}
	public function get_subs_label($item){
		if($item == NULL){
			return '';
		}
		$subscription_label='';
		$product_id = $item->getProductId();
		$buyRequest = $item->getBuyRequest();
		$options = $item->getOptions();
		$postdata = $this->request->getPost();
		$items = $postdata['item'];
		foreach ($options as $option) 
		{
			if($option->getCode() == 'info_buyRequest')
			{
				if(isset($items[$product_id]['milople_subscription_type_label'])){
				$subscription_label = $items[$product_id]['milople_subscription_type_label'];
				}
			}
		}
		return $subscription_label;
	}
	public function get_subs_start_date($item){
		if($item == NULL){
			return '';
		}
		$subscription_start_date = '';
		$product_id = $item->getProductId();
		$buyRequest = $item->getBuyRequest();
		$options = $item->getOptions();
		$postdata = $this->request->getPost();
		foreach ($options as $option) 
		{
			if($option->getCode() == 'info_buyRequest')
			{
				if(isset($postdata['milople_subscription_start_date_'.$product_id]))
				{
					$subscription_start_date = $postdata['milople_subscription_start_date_'.$product_id];
				}
			}
		}
		return $subscription_start_date;
	}
	
	public function get_subs_term_price($item)
	{
		if($item == NULL){
			return '';
		}
		$price_array = NULL;
		$product_id = $item->getProductId();
		$buyRequest = $item->getBuyRequest();
		$options = $item->getOptions();
		$postdata = $this->request->getPost();
		$plans_product = $this->recurringoptions->getPlanProducts()->load($product_id,'product_id');
		$plans = $this->recurringoptions->getPlan()->getCollection()->addFieldToFilter('plan_id',$plans_product->getPlanId());
		$planid = 0;
		if ($plans->count()>0)
			$planid = 0;
		foreach($plans as $plan)
		{
			$planid=$plan->getPlanId();
			$isnormal=$plan->getIsNormal();
		}
		$type = $this->recurringoptions->getTerms()->getCollection()->addFieldToFilter('plan_id',$planid);
		foreach($type as $id => $type_entry)
		{
			if($type_entry->getPriceCalculationType() == 1){
				$term_subs_price = $item->getProduct()->getPrice() * $type_entry->getPrice() / 100;
			}
			else{
				$term_subs_price = $type_entry->getPrice();
			}
			$price_array[$item->getId()][$id] = $term_subs_price;
		}
		return $price_array;
	}
}
