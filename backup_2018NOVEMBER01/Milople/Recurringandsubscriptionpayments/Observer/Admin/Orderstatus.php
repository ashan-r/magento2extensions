<?php
namespace  Milople\Recurringandsubscriptionpayments\Observer\Admin;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
class Orderstatus implements ObserverInterface
{
    public function __construct(
			RequestInterface $request,
      \Psr\Log\LoggerInterface $logger,
      \Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $recurringHelper,
       \Milople\Recurringandsubscriptionpayments\Model\SubscriptionFactory $subscription,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
		  \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->recurringHelper=$recurringHelper;
      	$this->scopeConfig = $scopeConfig;
        $this->subscription=$subscription;
        $this->logger = $logger;
        $this->cartRepository=$cartRepository;
		    $this->storeManager=$storeManager;
				$this->_request = $request;
    }
    /* Observer the event of order save.
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
         $this->logger->addDebug('Inside the observer of the Sales Order Save After');
         $order = $observer->getOrder();
         $status = $order->getStatus();
        //check for admin side
        //$store_id = $this->storeManager->getStore()->getId();
        //$quoteRepository = $this->cartRepository;
             /** @var \Magento\Quote\Model\Quote $quote */
        //$quote = $quoteRepository->get($order->getQuoteId());
				
        /* Create Subscription */
        //$this->recurringHelper->backedndassignSubscriptionToCustomer($quote,$order);
        //end of admin side check
			/*	
			//Setting additional Options
			$this->logger->addDebug('Setting the addtional options..');
      $this->logger->addDebug($this->_request->getFullActionName());
        $postdata = $this->_request->getPost();
        if (1) {
            //checking when product is adding to cart
          $this->logger->addDebug('Additional option from orderstatus observer..');
          $this->logger->addDebug(json_encode($postdata));
           $additionalOptions = [];
            if(isset($postdata['milople_subscription_type']) && $postdata['milople_subscription_type']>=0){
              $this->logger->addDebug('Additional option level 2..');
                $additionalOptions[] = array(
                    'label' => __('Subscription Type'),
                    'value' => $postdata['milople_subscription_type_label'],
                );
                $additionalOptions[] = array(
                    'label' => __('Subscription Start'),
                    'value' => $postdata['milople_subscription_start_date'],
                );
							foreach ($allItems as $item) {
                	$item->addCustomOption('additional_options', serialize($additionalOptions));
							}
            }    
           
        }
			//ed of setting additional oprions
      */
         $subscription=$this->subscription->create();
         $subscription=$subscription->load($order->getIncrementId(),'parent_order_id');
         $isOrderValidForActivation=$this->recurringHelper->isOrderStatusValidForActivation($status);
         if($isOrderValidForActivation && $subscription->getStatus()==\Milople\Recurringandsubscriptionpayments\Model\Subscription::STATUS_SUSPENDED){
           if (($order->hasInvoices()  &&  ($this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS) == 'processing')) 
              || ($order->hasShipments() &&  ($this->scopeConfig->getValue(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS) == 'complete')) ) {
              $this->logger->addDebug('Order status changed');
             $subscription->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Subscription::STATUS_ENABLED);
             $subscription->setLastOrderStatus($status);
             $subscription->save();
          }
         
         }elseif($subscription->isActive() && !$isOrderValidForActivation){
           $subscription->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Subscription::STATUS_SUSPENDED);
           $subscription->setLastOrderStatus($status);
           $subscription->save();
         }
    }
}