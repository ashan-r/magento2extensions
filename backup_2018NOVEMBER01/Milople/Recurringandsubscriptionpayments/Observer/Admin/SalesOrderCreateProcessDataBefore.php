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
namespace Milople\Recurringandsubscriptionpayments\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\CartRepositoryInterface;

class SalesOrderCreateProcessDataBefore implements ObserverInterface
{ 
	protected $logger;
	protected $messageManager;
	protected $_responseFactory;
  protected $_url;
	const HASH_SEPARATOR = ":::";
	const DB_DELIMITER = "\r\n";
	
    public function __construct (
		\Magento\Framework\AuthorizationInterface $authorization,
		\Magento\Customer\Model\Session $customersession,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Backend\Model\Session\Quote $sessionQuote,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localedate,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Catalog\Model\Product $productModel,
		\Magento\Framework\App\Request\Http $request,
		\Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $recurringhelper,
		\Milople\Recurringandsubscriptionpayments\Helper\Data $dataHelper,
		\Milople\Recurringandsubscriptionpayments\Model\Plans\ProductFactory $planProductFactory,
		\Milople\Recurringandsubscriptionpayments\Model\PlansFactory $planFactory,
		\Milople\Recurringandsubscriptionpayments\Model\TermsFactory $termsFactory,
		\Magento\Catalog\Model\Product $product,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Directory\Helper\Data $data,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Sales\Model\AdminOrder\Create $create,
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
		\Magento\Checkout\Helper\Cart $cart,
		\Magento\Framework\UrlInterface $urlInterface,
		\Psr\Log\LoggerInterface $logger
	 ) {
      $this->_authorization = $authorization;
			$this->customersession = $customersession; 
			$this->checkoutSession = $checkoutSession;
			$this->localeDate = $localedate; 
			$this->storedate = $date;
			$this->products = $productModel;
			$this->request = $request;
			$this->logger=$logger;
			$this->sessionQuote=$sessionQuote;
			$this->recurringhelper = $recurringhelper;
			$this->dataHelper = $dataHelper;
			$this->termFactory=$termsFactory;
			$this->planProductFactory=$planProductFactory;
			$this->planFactory=$planFactory;
			$this->_product = $product;
			$this->messageManager = $messageManager;
			$this->currencyConvertHelper = $data;
			$this->_storeManager = $storeManager;
			$this->_create = $create;
			$this->quoteRepository = $quoteRepository;
			$this->cart=$cart;
			$this->_urlInterface = $urlInterface;
		}
 
   public function execute(\Magento\Framework\Event\Observer $observer)
   {
		 $observeer_name = $observer->getData('param');
		 $quote = $this->sessionQuote->getQuote();
		 $postdata = $this->request->getPost();
		 $items = $postdata['item'];
		 $child = array();
		foreach ($quote->getAllItems() as $id => $item) 
		{
					$buyRequest = $item->getBuyRequest();
					$options = $item->getOptions();
					$product = $item->getProduct();
					$checking = $buyRequest->getProduct();
					$product_id = $product->getId();
					$plans_product = $this->getPlanProducts()->load($product_id,'product_id');
					$plan_data = $this->getPlan()->load($plans_product->getPlanId(),'plan_id');
					foreach ($options as $option)
						{
							if($option->getCode() == 'info_buyRequest' && isset($items[$product_id]['milople_select_subscription_type']))
							{
								$unserialized = $this->dataHelper->getUnserializeData($option->getValue());
								$unserialized['milople_subscription_type'] = $items[$product_id]['milople_select_subscription_type'];
								$unserialized['milople_subscription_type_label'] = $items[$product_id]['milople_subscription_type_label'];
								$unserialized['milople_subscription_start_date'] = $postdata['milople_subscription_start_date_'.$product_id.''];
								$option->setValue($this->dataHelper->getSerializeData($unserialized))->save();
							}
						}
					$child[]=$item->getItemId();
					$item->setOptions($options)->save();
					if(isset($items[$product_id]['milople_select_subscription_type']))
					{
						if($items[$product_id]['milople_select_subscription_type']) 
						{
							if($items[$product_id]['milople_select_subscription_type'] > 0)
							{
								$termid = $items[$product_id]['milople_select_subscription_type'];
								$qty = $buyRequest->getQty();
								$productId = $buyRequest->getProduct();
								$types =$this->termFactory->create()->load($termid);
								$price = $types->getPrice();
								if($types->getPriceCalculationType() == 1)
								{
									$price = ($price * $product->getPrice()) / 100;
								}
								//setting additional options from backend
								$additionalOptions = [];
								$additionalOptions[] = array(
											'label' => __('Subscription Type'),
											'value' => $items[$product_id]['milople_subscription_type_label'],
									);
									$additionalOptions[] = array(
											'label' => __('Subscription Start'),
											'value' => $postdata['milople_subscription_start_date_'.$product_id.''],
									);
									$product->addCustomOption('additional_options', $this->dataHelper->getSerializeData($additionalOptions));
								$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
								$customOptions = $_objectManager->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
								$quote->setIsActive(true);
							}
						}
						if($postdata['milople_subscription_start_date_'.$product_id.''])
						{
								if(strtotime($postdata['milople_subscription_start_date_'.$product_id.'']) < time())
								{
									$buyRequest->setMilopleSubscriptionType($items[$product_id]['milople_select_subscription_type']);
									$buyRequest->setMilopleSubscriptionStartDate(date('m-d-Y'));
								}
						}
					}
					$this->sessionQuote->setProductItem($child);
		}
   }
  /* 
	*  Return same object where we need model
	*  return planProductFactory object
	*/
	public function getPlanProducts(){
		return $this->planProductFactory->create();
	}

	/* 
	*  Return same object where we need model
	*  return planProductFactory object
	*/
	public function getPlan(){
		return $this->planFactory->create();
	}
	
}