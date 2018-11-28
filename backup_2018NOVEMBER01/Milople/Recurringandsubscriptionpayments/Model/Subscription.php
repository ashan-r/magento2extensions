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
namespace Milople\Recurringandsubscriptionpayments\Model;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ObjectManager;
class Subscription extends AbstractModel
{
	const STATUS_ENABLED = 1;
  const STATUS_SUSPENDED = 2;
  const STATUS_CANCELED = -1;
  const STATUS_SUSPENDED_BY_CUSTOMER = 3;
  const STATUS_EXPIRED = 0;
  const STATUS_SKIPPED = 5;
  const STATUS_DISABLED = 0;
	const DB_DELIMITER = "\r\n";
	const ITERATE_STATUS_RUNNING = 2;
	const ITERATE_STATUS_REGISTRY_NAME = 'MILOPLE_RECURRINGANDSUBSCRIPTIONPAYMENTS_PAYMENT_STATUS';
	const DB_DATE_FORMAT = 'yyyy-MM-dd'; // DON'T use Y(uppercase here)
	const LOG_SEVERITY_WARNING = 4;

	protected $_virtualItems = array();
  protected static $_subscription;

	 public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Milople\Recurringandsubscriptionpayments\Helper\Config $confighelper,
		\Milople\Recurringandsubscriptionpayments\Helper\Data $dataHelper,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localedate,
		\Magento\Framework\ObjectManagerInterface $objectManager, 
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Milople\Recurringandsubscriptionpayments\Model\TermsFactory $termsFactory,
		\Milople\Recurringandsubscriptionpayments\Model\SubscriptionFactory $subscriptionFactory,
		 \Milople\Recurringandsubscriptionpayments\Model\Subscriptionstatus $subscriptionStatus,
		\Milople\Recurringandsubscriptionpayments\Model\Plans\ProductFactory $planProductFactory,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Milople\Recurringandsubscriptionpayments\Model\PlansFactory $plan,
	  \Magento\Framework\Model\Context $context,
		 \Milople\Recurringandsubscriptionpayments\Helper\EmailSender $emailSender,
    \Magento\Framework\Registry $registry,
    \Milople\Recurringandsubscriptionpayments\Model\SequenceFactory $sequenceFactory,
    \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory $valueCollectionFactory,
    \Magento\Sales\Model\OrderFactory $orderFactory,
    \Magento\Sales\Model\Order\Payment $payment,
    \Magento\Sales\Model\Order\AddressFactory $address,
		//\Psr\Log\LoggerInterface $logger,
		\Milople\Recurringandsubscriptionpayments\Model\AuthorizeCim $AuthorizeCim,
    \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
    \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
    array $data = []
    ) {
    $this->_valueCollectionFactory = $valueCollectionFactory;
		$this->scopeConfig = $scopeConfig;
		$this->dataHelper = $dataHelper;
		$this->storeManager = $storeManager;
		$this->termsFactory=$termsFactory;
		$this->subscriptionStatus=$subscriptionStatus;
		$this->planFactory=$plan;
		$this->configHelper = $confighelper;
		$this->_localeDate = $localedate;
		$this->_objectManager=$objectManager;
		$this->orderFactory=$orderFactory;
		$this->emailSender=$emailSender;
		$this->_product = $productFactory;
		$this->planProductFactory=$planProductFactory;
		$this->payment=$payment;
		$this->address=$address;
		$this->sequenceFactory=$sequenceFactory;
		$this->subscriptionFactory=$subscriptionFactory;
		$this->storedate = $date; 
		//$this->logger=$logger;
		$this->authorizeCim = $AuthorizeCim;
		$this->registry = $registry;
		$this->dataHelper = $dataHelper;
	    parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
	}
	/**
	 * Define resource model
	 */
	protected function _construct()
	{
		$this->_init('Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Subscription');
	}
	/**
  * Check if payment method exists
  * @param string $method
  * @return bool
  */
  public function hasMethodInstance($method)
  {
			 try
			 {
					$method = ucfirst($method);
					$offline = array ('Checkmo','Free','Purchaseorder','Banktransfer','Cashondelivery','Authorizenet_directpost');
					$is_offline = in_array($method,$offline);
					if($is_offline)
					{
						return $is_offline;
					}
					else
					{
						if ($method == "Paypal_express")
							@$methodAvailable = $this->_objectManager->get('Milople\Recurringandsubscriptionpayments\Model\Payment\Method\Express');
						else
							@$methodAvailable = $this->_objectManager->get('Milople\Recurringandsubscriptionpayments\Model\Payment\Method\\'.$method);
					}
			}
			catch (\Exception $ex)
			{
				 return false;
			}
			return $methodAvailable;
   }
   /**
   * Check method is offline
   * @param string $method
   * @return bool
   */
  public function methodIsOffline($method)
	{

		$method = ucfirst($method);
		$offline = array ('Checkmo','Free','Purchaseorder','Banktransfer','Cashondelivery','Authorizenet_directpost');
		$is_offline = in_array($method,$offline);
		return $is_offline;
	}
	# Check subscription in iterating
	public static function isIterating()
  {
		return $this->_coreRegistry(self::ITERATE_STATUS_REGISTRY_NAME) == self::ITERATE_STATUS_RUNNING;
  }
	
	/**
    * Easy way to set customer as objectreturn parent::_afterSave();
    */
  public function setCustomer(\Magento\Customer\Model\Customer $Customer)
  {
        $this->setCustomerId($Customer->getId());
        return $this;
  }
	/**
    * Initiates subscription items from order items
    * @param object $OrderItems
    */
  public function initFromOrderItems($OrderItems, Order $Order)
  {	
		$this->_virtualItems = array();
        foreach ($OrderItems as $OrderItem) {
				$this->_virtualItems[] = $this->_objectManager->create('Milople\Recurringandsubscriptionpayments\Model\Subscription\Item')
                    ->setPrimaryOrderId($Order->getIncrementId())
                    ->setPrimaryOrderItemId($OrderItem->getId());
        }
        return $this;
  }
  # Get Quote
	public function getQuote()
  {
		if (!$this->getData('quote')) {
			$this->setQuote($this->quoteRepository->get($this->getPrimaryQuoteId()));
        }
        return $this->getData('quote');
    }
	# Create New subscription
	public function creteSubscription($isNew)
	{
			if($isNew == true)   // For New Subscription
			{
		  	 $this->getQuote()->setUpdatedAt($this->_localeDate->formatdate($this->_localeDate->date(),\IntlDateFormatter::LONG))->save();
           $this->setIsNew(true);
      }
	    if (is_null($this->getStoreId()))
			{
            $storeId = ($this->_objectManager->get('Magento\Backend\Model\Session\Quote')->getStoreId())
                    ? $this->_objectManager->get('Magento\Backend\Model\Session\Quote')->getStoreId() : $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            $this->setStoreId($storeId);
      }
			if (!empty($this->_virtualItems))
			{
            // Save virtual items
					foreach ($this->_virtualItems as $Item) 
					{
						$Item->setSubscriptionId($this->getId())->save();
          }
       }
			/* create sequences on placing an order */
	  	if (!$this->getFlagNoSequenceUpdate()) 
			{
            $this->_generateSubscriptionEvents();
			}
			$subscription  =  $this->_objectManager->create('Milople\Recurringandsubscriptionpayments\Model\Subscription')->load($this->getId());
		  $this->setSubscription($subscription)->save();
			if($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_SEND_ORDER_CONFORMATION_EMAIL)){
				$this->emailSender->processConfirmationMails($subscription,'Order Confirmation');
			}
	}
	# Set data to newly create subscription
	public function setSubscription($subscription)
	{
			$virtual = 0;
			$isInfinite=$this->getTerm()->isInfinite();
    	if (!$isInfinite)
			{
            $expireDate = $this->getSubExpiryDate()->toString(self::DB_DATE_FORMAT);
						$check = (int)$this->getTerm()->getPaymentBeforeDays() - 1;  
						$expireDate = date('Y-m-d', strtotime("+$check day", strtotime($expireDate)));
	    } 
			else 
			{
            $expireDate = NULL;
			}
			if ($this->getIsNew()) 
			{
        	$lastOrderAmount = 0;
          $quote = clone $this->getQuote();
			    foreach ($quote->getItemsCollection() as $Item) {
						$buyInfo = $Item->getBuyRequest();
						$period_type = $buyInfo->getMilopleSubscriptionType();
						$Options =  $this->_objectManager->get('\Milople\Recurringandsubscriptionpayments\Model\Terms')->load($period_type);
            if ($Options) {
                    $quote->removeItem($Item->getId());
            }
            $quote->getShippingAddress()->setCollectShippingRates(true);//->collectTotals();
            $quote->collectTotals();
						$virtual = $Item->getIsVirtual();
          }
          $lastOrderAmount = $this->getQuote()->getGrandTotal();
					$virtual = $quote->getIsVirtual();
          unset($quote);
		
      }
			else
			{
          $lastOrderAmount = $this->getLastOrder()->getGrandTotal();
          $virtual = $this->getLastOrder()->getIsVirtual();
			}
			$paymentOffset = $this->getTerm()->getPaymentBeforeDays();

		// Get next payment date
		if(!$this->getLastPaidDate() && $this->getIsNew())
		{     // Come here on placing an order
			$nextPaymentDate = $this->getLastOrder()->getCreatedAtStoreDate();
			$nextPaymentDate = $this->getNextSubscriptionEventDate($this->getDateStart());
    } 
		else 
		{
			$paidDate = new \Zend_Date($this->getLastPaidDate(), self::DB_DATE_FORMAT);
			$nextPaymentDate = $this->getNextSubscriptionEventDate($paidDate);
		}
		if ($paymentOffset) 
		{       
				// $paymentOffset used for Payment before days
        if (!$this->getLastPaidDate()) 
				{
            // No payments made yet
        	  $lastOrderDate = clone $this->getDateStart();
            $lastOrderDate->addDayOfYear(0 - floatval($paymentOffset));
		    }
				else 
				{
            $lastOrderDate = $this->getLastOrder()->getCreatedAtStoreDate();
        }
        $nextPaymentDate->addDayOfYear(0 - floatval($paymentOffset));
    }
            if (!isset($nextDeliveryDate)) {
            }
		$nextPaymentDate = $nextPaymentDate->toString(self::DB_DATE_FORMAT);
		$nextDeliveryDate = $nextPaymentDate;
		if($subscription->getParentOrderId() == '')
		{
			   $order_id = $this->getOrder()->getIncrementId();
		}
		else
		{
			   $order_id = $subscription->getParentOrderId();
		}
		$magento_date = $this->storedate->date('Y-m-d');
		if($magento_date == $expireDate)
		{
			$nextPaymentDate = $expireDate ;
			$nextDeliveryDate = $expireDate;
		}
		
		$subscription->setLastOrderCurrencyCode($this->getLastOrder()->getOrderCurrencyCode());
		$subscription->setLastOrderStatus($this->getLastOrder()->getStatus());
		$subscription->setLastOrderAmount($lastOrderAmount);
		$subscription->setParentOrderId($order_id);
		$subscription->setProductsText($this->_convertProductsText());
		$subscription->setDateExpire($expireDate);
		$subscription->setHasShipping(strval(1 - $virtual));
		$subscription->setNextPaymentDate($nextPaymentDate);
		return $subscription;
	}
	# Check subscriptin in infinite
	public function isInfinite()
	{
	
		if($this->getDateExpire()==null)
		{
			return true;
		}else{
			return false;
		}
  }
	# Get term data
	public function getTerm()
	{
		if (!$this->getData('terms')) 
		{
			$this->setTerms($this->termsFactory->create()->load($this->getTermType()));
    }
    return $this->getData('terms');
	}
	/**
  * Returns probably expire date
  * @return Zend_Date
  */
  public function getSubExpiryDate()
  {
		if (!$this->getData('date_expire'))
		{
			$isInfinite=$this->getTerm()->isInfinite();
			if (!$isInfinite) 
			{
      	  foreach ($this->_objectManager->get('Milople\Recurringandsubscriptionpayments\Model\Sequence')
						 ->getCollection()
						 ->addSubscriptionFilter($this)->setOrder('date', 'desc') as $SequenceItem) 
					{
						$offset = $this->getTerm()->getPaymentBeforeDays();
				    $expirydate =  new \Zend_Date($SequenceItem->getDate(), self::DB_DATE_FORMAT);
         		\Zend_Date::setOptions(array('extend_month' => true));
						switch ($this->getTerm()->getTermsper()) {
							case 'day':
								$method = 'addDayOfYear' ;
								break;
							case 'month':
								$method = 'addMonth';
								break;
							case 'week':
								$method = 'addWeek';
								break;
							case 'year':
								$method = 'addYear';
								break;
							default:
								throw new LocalizedException(__('Unknown subscription Term #" . $this->getTerm()->getId() . " for subscription #{$this->getId()}'));
						}
      			$expirydate = call_user_func(array($expirydate, $method), $this->getTerm()->getRepeateach());
						return $expirydate;
				  }
      }
			else
			{
         return $this->getNextSubscriptionEventDate(new \Zend_Date);
      }
    }
    return new \Zend_Date;
  }
  # Get Last Date of paid subscription
	public function getLastPaidDate()
  {
      foreach ($this->_objectManager->get('Milople\Recurringandsubscriptionpayments\Model\Sequence')->getCollection()
                ->addStatusFilter(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_PAYED)
                ->addSubscriptionFilter($this)->setOrder('date', 'desc')
            as $SequenceItem) {
						return new \Zend_Date($SequenceItem->getDate(), self::DB_DATE_FORMAT);
      }
      return null;
  }
  # Get Last Order data
	public function getLastOrder()
  {
        if (!$this->getData('last_order')) {
            $coll = $this->_objectManager->get('Milople\Recurringandsubscriptionpayments\Model\Sequence')
                    ->getCollection()
                    ->addSubscriptionFilter($this)
                    ->addStatusFilter(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_PAYED)
                    ->setOrder('date','asc');

            foreach ($coll as $SequenceItem) {
                if (!$SequenceItem->getOrderId()) {
									throw new LocalizedException(__("Subscription record marked as paid but no order found: #{$SequenceItem->getId()}, suscription #{$SequenceItem->getSubscriptionId()}"));
                }
                $orderId = $SequenceItem->getOrderId();
            }
            if (isset($orderId)) {
                $order = $this->_objectManager->get('Magento\Sales\Model\Order')->load($orderId);
            } else {
                $order = $this->getOrder(); // Primary order
            }
            $this->setData('last_order', $order);
        }
        return $this->getData('last_order');
  }
  # Get Start data of subscription
	public function getDateStart()
  {
     return new \Zend_Date($this->getData('date_start'), self::DB_DATE_FORMAT);
  }
  # Get Next date of subscription event
	public function getNextSubscriptionEventDate($CurrentDate = null)
  {		
		\Zend_Date::setOptions(array('extend_month' => true)); // Fix Zend_Date::addMonth unexpected result
        if (!($CurrentDate instanceof \Zend_Date)) {
            if (is_null($CurrentDate)) {
                if (!($CurrentDate = $this->getLastPaidDate())) {
                    throw new \Milople\Recurringandsubscriptionpayment\Exception("Failed to detect last paid date");
                }
            }else{
                throw new \Milople\Recurringandsubscriptionpayment\Exception("getNextSubscriptionEventDate accepts only Zend_Date or null");
            }
        }
       \Zend_Date::setOptions(array('extend_month' => true));
		   switch ($this->getTerm()->getTermsper()) {
            case 'day':
                $method = 'addDayOfYear';
                break;
            case 'month':
                $method = 'addMonth';
                break;
            case 'week':
                $method = 'addWeek';
                break;
            case 'year':
                $method = 'addYear';
                break;
            default:
                throw new \Magento\Framework\Exception\LocalizedException("Unknown subscription Term #" . $this->getTerm()->getId() . " for subscription #{$this->getId()}");
        }
			  $CurrentDate = call_user_func(array($CurrentDate, $method), $this->getTerm()->getRepeateach());
	 			return $CurrentDate;
  }
  # Get Order detail
	public function getOrder()
  {
		if (!$this->getData('order')) {
    	  foreach ($this->getItems() as $Item) {
			  	$this->setOrder($this->_objectManager->get('Magento\Sales\Model\Order')->load($Item->getPrimaryOrderId(),'increment_id'));
					break;
        }
		}
    return $this->getData('order');
  }
  # Convert product text with number if same product available multiple time in susbcription
	public function _convertProductsText()
	{
			$out = array();
      foreach ($this->getItems() as $Item) {
		  	 $out[] = $Item->getOrderItem()->getName() . " (" . intval($Item->getOrderItem()->getQtyOrdered()) . ")";
      }
		  return implode(self::DB_DELIMITER, $out);
	}
	# Get all items
	public function getItems()
  {
	  if (!$this->getData('items')) {
			$this->setItems($this->_objectManager->get('Milople\Recurringandsubscriptionpayments\Model\Subscription\Item')->getCollection()->addSubscriptionFilter($this));
    }
    return $this->getData('items');
  }
  # Get subscription label
	public function getSubscriptionStatusLabel()
  {
    return $this->_objectManager->get('Milople\Recurringandsubscriptionpayments\Model\Subscriptionstatus')->getLabel($this->getStatus());
  }
	/**
  * Returns payment model instance by code
  * @param string $method
  * @return object
  */
  public function getMethodInstance($method)
  {
    return $this->_getMethodInstance($method);
  }
	/**
  * Returns payment model instance by code
  * @param string $method
  * @return object
  */
  protected function _getMethodInstance($method = null)
  {
	    if (!$method && $this->getOrder()) {
         try {
            $method = $this->getOrder()->getPayment()->getMethod();
         } catch (\Exception $e) {
         }
     	}
			$offline = array ('checkmo','purchaseorder','banktransfer','cashondelivery','Authorizenet_directpost');
			$is_offline = in_array($method,$offline);
			if(($is_offline) && $is_offline = $this->_objectManager->get('Milople\Recurringandsubscriptionpayments\Model\Payment\Method\Offlinemethods'))
			{
				return $is_offline->setSubscription($this);
			}
			if ($method == "paypal_express")
			{
				$model = $this->_objectManager->get('Milople\Recurringandsubscriptionpayments\Model\Payment\Method\Express');
				return $model->setSubscription($this);
			}
  }
  # Generate subscription's sequence
	public function _generateSubscriptionEvents()
	{
			// Delete all sequencies
      if ($this->_origData['date_start'] != $this->_data['date_start'] ||
            $this->_origData['term_type'] != $this->_data['term_type'] ||
            (!$this->getIsNew() && $this->getIsReactivated())
      )
			{
			$this->_objectManager->create('Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Sequence')->deleteBySubscriptionId($this->getId());
			$year = substr($this->getDateStart()->toString(self::DB_DATE_FORMAT),0,4) ;
			$month = substr($this->getDateStart()->toString(self::DB_DATE_FORMAT),5,2) ;
			$day = substr($this->getDateStart()->toString(self::DB_DATE_FORMAT),8,2) ;
			$datearray = array('year' => $year, 'month' => $month, 'day' => $day);
			$Date = new \Zend_Date($datearray);
			switch ($this->getTerm()->getTermsper())
			{
          case 'day':
               $method = 'addDayOfYear';
               break;
          case 'month':
               $method = 'addMonth';
               break;
          case 'week':
                $method = 'addWeek';
                break;
          case 'year':
                $method = 'addYear';
                break;
          default:
					throw new LocalizedException(__('Unknown subscription Term type for #' . $this->getTerm()->getId()));
        }
				switch ($this->getTerm()->getTermsper()) 
				{
          case 'day':
                $method_expire = 'addDayOfYear';
                break;
          case 'month':
               $method_expire = 'addMonth';
               break;
          case 'week':
               $method_expire = 'addWeek';
               break;
          case 'year':
               $method_expire = 'addYear';
               break;
          default:
          	   throw new LocalizedException(__("Unknown subscription expire Term type for #" . $this->getTerm()->getId()));
        }
				$ExpireDate = clone $Date; 
        $expireMultiplier = $this->getTerm()->getNoofterms();
        if (!$expireMultiplier)
				{
           // 0 means infinite expiration date
           $expireMultiplier = 1;//3;
        }
				else
				{
					$expireMultiplier =  ($expireMultiplier - 1) * $this->getTerm()->getRepeateach() ; 
				}
        $ExpireDate = call_user_func(array($ExpireDate, $method_expire), $expireMultiplier);
        // Substract delivery offset. This is
				$requirepayement = $this->getTerm()->getPaymentBeforeDays(); //
				$datefrm_paymentbeforedays = $Date->addDayOfYear(0 - $requirepayement);
        $ExpireDate->addDayOfYear(0 - $requirepayement);
				try 
				{
				   $this->getTerm()->validate();
					 $i = 1;
           while ($Date->compare($ExpireDate) == -1) 
					 {
							/*start : when plan has a value of 'payment before days' that time control is going to IF cond */
							if($requirepayement > 0)
							{
								$year = substr($datefrm_paymentbeforedays->toString(self::DB_DATE_FORMAT),0,4) ;
								$month = substr($datefrm_paymentbeforedays->toString(self::DB_DATE_FORMAT),5,2) ;
								$day = substr($datefrm_paymentbeforedays->toString(self::DB_DATE_FORMAT),8,2) ;
							}
							else
							{
								$year = substr($this->getDateStart()->toString(self::DB_DATE_FORMAT),0,4) ;
								$month = substr($this->getDateStart()->toString(self::DB_DATE_FORMAT),5,2) ;
								$day = substr($this->getDateStart()->toString(self::DB_DATE_FORMAT),8,2) ;
							}
							/*end : when plan has a value of 'payment before days' that time control is going to IF cond */	
							$datearray = array('year' => $year, 'month' => $month, 'day' => $day);
							$Date = new \Zend_Date($datearray);
							switch ($this->getTerm()->getTermsper()) 
							{
								case 'day':
									$Date->addDay($i * $this->getTerm()->getRepeateach());
									break;
								case 'month':
									$Date->addMonth($i * $this->getTerm()->getRepeateach());
									break;
								case 'week':
									$Date->addWeek($i * $this->getTerm()->getRepeateach());	
									break;
								case 'year':
									$Date->addYear($i * $this->getTerm()->getRepeateach());	
									break;
								default:
									throw new LocalizedException(__("Unknown subscription Term type for #" . $this->getTerm()->getId()));
							}
				   		$this->_objectManager->create('Milople\Recurringandsubscriptionpayments\Model\Sequence')
                            ->setSubscriptionId($this->getId())
                            ->setDate($Date->toString(self::DB_DATE_FORMAT))
                            ->save();
							$i++;
          }
        }
				catch (\Exception $e) 
				{
           	throw new LocalizedException(__("Unable create sequences to subscription #" . $this->getId().' '. $e->getMessage()));
				}
		}
		return $this;
	}
	# Check subscription statu is suspended
 	public function getIsSuspending()
  {
     return (($this->_origData['status'] != $this->_data['status']) && ($this->_data['status'] == self::STATUS_SUSPENDED || $this->_data['status'] == self::STATUS_SUSPENDED_BY_CUSTOMER));
  }
  /**
  * Determines wether subscription is set back to active
	* @return bool
  */
  public function getIsReactivated()
  {
     return (($this->_origData['status'] != $this->_data['status']) && ($this->_data['status'] == self::STATUS_ENABLED));
  }
	/**
  * Determines wether subscription is set to expired
  * @return bool
  */
  public function getIsExpiring()
  {
    return (($this->_origData['status'] != $this->_data['status']) && ($this->_data['status'] == self::STATUS_EXPIRED));
  }
 	/**
  * Says wether subscription is active
  * @return bool
  */
  public function isActive()
  {
		return $this->getStatus() == self::STATUS_ENABLED;
  }
	/**
  * Processes payment for date
  * @param object $date
  * @return Milople_Recurringandrentalpayments_Model_Subscription
  */
  public function payForDate($date)
  {
    	$this->updateSequences();
			$date = $this->_localeDate->date($date);
      $sequenceItems=$this->sequenceFactory->create()->getCollection()
                ->addSubscriptionFilter($this)
                ->prepareForPayment()
                ->addDateFilter($date);
      foreach ($sequenceItems as $item)
      {
            $this->payBySequence($item);
            break;
      }
      return $this;
   }
   # Update the sequences of susbcription
	 public function updateSequences()
   {
			//checks if this is a last item of the infinite subscription
			$isInfinite = $this->termsFactory->create()->load($this->getTermType());
	    if ($isInfinite->getNoofterms()==0) 
			{
					 $coll = $this->sequenceFactory->create()
                    ->getCollection()
                    ->addSubscriptionFilter($this)
                    ->addStatusFilter(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_PENDING);
		      if ($coll->count() == 1) //this is a last pending sequence, we need a new one
          {
								$_seq = $coll->getFirstItem();
                $_aDate = new \Zend_Date($_seq->getDate(),self::DB_DATE_FORMAT);
                $nextDate = $this->getNextSubscriptionEventDate($_aDate);
                $newSeq = $this->sequenceFactory->create()
                       ->setSubscriptionId($this->getId())
                       ->setstatus(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_PENDING)
                       ->setDate($nextDate->toString(self::DB_DATE_FORMAT))
                       ->save();
           }
        }
    }
		/**
     * Processes subscription by sequence
     * @param object $Item
     * @return bool
     */
    public function payBySequence($item)
    {
				self::$_subscription = $this;
				$subscription=$this->subscriptionFactory->create()->load($this->getId());
				$getParentOrderId=$subscription->getParentOrderId();
				$order = $this->orderFactory->create()->loadByIncrementId($getParentOrderId);
				$payment = $order->getPayment();
				$paymentMethodCode=$payment->getMethodInstance()->getCode();
				$isMethod=$this->hasMethodInstance($paymentMethodCode);
				$isOffline=$this->methodIsOffline($paymentMethodCode);
				$objectManager = ObjectManager::getInstance();
				$checkoutSession=$objectManager->create('\Magento\Checkout\Model\Session');
				$checkoutSession->setSubscriptionIdData($this->getId());
				if($isMethod && $isOffline){
					try{
						$newOrder=$this->generateOrder($order);
							if($newOrder['order_id']){
								 if($paymentMethodCode == 'authorizenet_directpost')
								 {
									 $transcation_id = $subscription->getTransactionId();
									 $transcation_id = explode(",",$transcation_id);
									 $customer_profile_id = $transcation_id[0];
									 $payment_profile_id = $transcation_id[1];
									 $new_order = $objectManager->create('\Magento\Sales\Model\Order')->load($newOrder['order_id']);
									 $order_grand_total = $new_order->getGrandTotal();
									 $result_authorize_auto = $this->authorizeCim->autoCaptureAuthorizenet($customer_profile_id,$payment_profile_id,$order_grand_total);
								 }
								 $item->setOrderId($newOrder['order_id']);
								 $item->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_PAYED);
								 $item->setTransactionStatus('Success');
								 if($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_CONFORMATION)){	
									$this->emailSender->processNextPaymentConfirmationEmails($subscription);
									$item->setMailsent(1);
								 }
							}
							else{
								$item->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_FAILED);	
								$item->setTransactionStatus('Order Generation Failed without Exception');
							}
						$item->save();
					}catch(\Exception $e){
						$item->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_FAILED);	
						$item->setTransactionStatus($e->getMessage());
						$item->save();
					}
				}
				else
				{
					 $objectManager = ObjectManager::getInstance();
					 $agreement_id=$subscription->getTransactionId();
					 $checkoutSession=$objectManager->create('\Magento\Checkout\Model\Session');
					 $checkoutSession->setBillingAgreementData($agreement_id);
						try{
							$newOrder=$this->generateOrder($order);
							
							
							if($newOrder['order_id'])
							{
								 $item->setOrderId($newOrder['order_id']);
								 $item->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_PAYED);
								 $item->setTransactionStatus('Success');
								 if($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_NEXT_PAYMNET_CONFORMATION)){	
							   	$this->emailSender->processNextPaymentConfirmationEmails($subscription);
									$item->setMailsent(1); 
								 }
						 }
					  	else
							{
								$item->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_FAILED);	
								$item->setTransactionStatus('Order Generation Failed without Exception');
							}
							$item->save();
						}
						catch(\Exception $e)
						{
							$item->setStatus(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_FAILED);	
							$item->setTransactionStatus($e->getMessage());
							$item->save();
						}
				}
				 $coll = $this->sequenceFactory->create()
                 ->getCollection()
                 ->addSubscriptionFilter($this)
                 ->addStatusFilter(\Milople\Recurringandsubscriptionpayments\Model\Sequence::STATUS_PENDING);
								$_seq = $coll->getFirstItem();
                $_aDate = new \Zend_Date($_seq->getDate(),self::DB_DATE_FORMAT);
				if($this->getConfig(\Milople\Recurringandsubscriptionpayments\Helper\Config::XML_PATH_ACTIVE_ORDER_STATUS) != 'pending')
				{
					$subscription->setStatus(2);
				}
        $subscription->setNextPaymentDate($_aDate);
			  $subscription->save();
	  }
    /**
    * Generate order for Payment Methods
    * @param object $order
    * @return array 
    */
    public function generateOrder($order) 
		{
    	$objectManager = ObjectManager::getInstance();
    	$quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
			$quoteManagement=$objectManager->create('\Magento\Quote\Model\QuoteManagement');
			$customerFactory=$objectManager->create('\Magento\Customer\Model\CustomerFactory');
     	$customerRepositoryInterface=$objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface');
     	$shippingRate=$objectManager->create('\Magento\Quote\Model\Quote\Address\Rate');
     	$orderService=$objectManager->create('\Magento\Sales\Model\Service\OrderService');
     	$cartRepositoryInterface=$objectManager->create('\Magento\Quote\Api\CartRepositoryInterface');
     	$cartManagementInterface=$objectManager->create('\Magento\Quote\Api\CartManagementInterface');
			$store = $this->storeManager->getStore();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        //init the customer
        $customer=$customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($order->getCustomerEmail());// load customet by email address
				//init the quote
        $cart_id = $cartManagementInterface->createEmptyCart();
        $cart = $cartRepositoryInterface->get($cart_id);
        $cart->setStore($store);
        // if you have already buyer id then you can load customer directly
        $customer= $customerRepositoryInterface->getById($customer->getEntityId());
        $cart->setCurrency();
        $cart->assignCustomer($customer); //Assign quote to customer
        //add items in quote
        $items = $order->getAllVisibleItems();
        foreach( $items as $item){
					  $data=$item->getData();
				    $product = $this->_product->create()->load($item->getProduct()->getId());
					  $params = array('product' => $item->getProduct()->getId(),'price'=>$item->getPrice(), 'qty' => $item->getQty());
            if($this->isHaveSubscriptionOptions($product->getId())){
            	 	$options = $item->getProductOptions();
          	  	$additionalOptions[] = array(
                    'label' => __('Subscription Type'),
                    'value' => $options['info_buyRequest']['milople_subscription_type_label'],
                );
                $additionalOptions[] = array(
                    'label' => __('Subscription Start'),
                    'value' => $options['info_buyRequest']['milople_subscription_start_date'],
                );
							if ($product->getTypeId() == 'configurable') {
                 $params['super_attribute'] = $data['product_options']['info_buyRequest']['super_attribute'];
							}
           		$product->addCustomOption('additional_options', $this->dataHelper->getSerializeData($additionalOptions));
							$objParam = new \Magento\Framework\DataObject();
              $objParam->setData($params);
	            $quoteItem=$cart->addProduct(
	                $product,
	                $objParam
	            );
							$milople_subscription_type = $options['info_buyRequest']['milople_subscription_type'];
							$buyInfo = $quoteItem->getBuyRequest();
							$buyInfo->setMilopleSubscriptionType($milople_subscription_type);
							$quoteItem->setQty($item->getQtyOrdered());
							$quoteItem->setCustomPrice($item->getPrice());
							$quoteItem->setOriginalCustomPrice($item->getPrice());
							$quoteItem->save();
	        }
        }
				$checkoutSession=$objectManager->create('\Magento\Checkout\Model\Session');
				$checkoutSession->setSubtotalForDiscount($order->getSubtotal());
        //Set Address to quote @todo add section in order data for seperate billing and handle it
        $paymentMethodCode = $order->getPayment()->getMethod();
        $shipping_method = $order->getShippingMethod();
        //Set Address to quote
        $addressInfo =$order->getBillingAddress()->getData();
        $addressData = array(
		    'firstname' => $addressInfo['firstname'],
		    'lastname' => $addressInfo['lastname'],
		    'street' => $addressInfo['street'],
		    'city' => $addressInfo['city'],
		    'postcode' => $addressInfo['postcode'],
		    'telephone' => $addressInfo['telephone'],
		    'country_id' => $addressInfo['country_id'],
		    'region_id' => $addressInfo['region_id'], // id from directory_country_region table
				);
        $cart->getBillingAddress()->addData($addressData);
        $cart->getShippingAddress()->addData($addressData);
        // Collect Rates and Set Shipping & Payment Method
        $shippingRate
            ->setCode($shipping_method)
            ->getPrice(1);
        $shippingAddress = $cart->getShippingAddress();
				//@todo set in order data
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($shipping_method); //shipping method
				$cart->getShippingAddress()->addShippingRate($shippingRate);
        $cart->setPaymentMethod($paymentMethodCode); //payment method
				//@todo insert a variable to affect the invetory
        $cart->setInventoryProcessed(false);
		 		// Set sales order payment
        $cart->getPayment()->importData(['method' => $paymentMethodCode]);
			  // Collect total and saeve
        $cart->collectTotals();
			  // Submit the quote and create the order
        $cart->save();
        $cart = $cartRepositoryInterface->get($cart->getId());
        $order_id = $cartManagementInterface->placeOrder($cart->getId());
      	if($order_id){
            $result['order_id']= $order_id;
        }else{
            $result=['error'=>1];
        }
        return $result;
    }
    /*
		*	Check subscription option is there on given product id.
		*/
		public function isHaveSubscriptionOptions($productId){
			$plans = $this->planProductFactory->create()->load($productId,'product_id');
			if($plans->getProductId())
			{		   
				return true;
			}
			return false;
		}
	 /**
    * Return product's name of current subscription`
	  * @return string
   */
   public function getOrderedItems($primaryOrderId)
   {
      $string = array();
      $products = '';
		  $orderObject = $this->_objectManager->get('\Magento\Sales\Model\Order'); 
			$order = $orderObject->loadByIncrementId($primaryOrderId);
			$items = $order->getAllVisibleItems();
			foreach ($items as $item){
					 //$options = $item->getProductOptions();  
					 $product= $item->getProductId();
					 if($this->isHaveSubscriptionOptions($product)) {
						 $string[] = $item->getName();
						 /*$addOptions = $options['additional_options'];
						 $sub_type = $addOptions[0]['label'];
						 if (isset($sub_type) && $sub_type =='Subscription Type')
						 {
								$string[] = $item->getName();
						 }*/
					}	 
		  }
      if (count($string) == 1)
      {
         $products = implode('', $string);
      }
      else
      {
         $products = implode(',', $string);
      }
      return $products;
   }
	/**
    * Return product's price of current subscription
	  * @return string
   */
   public function getOrderedItemsPrice($primaryOrderId)
   {
      $string = array();
      $products = '';
		  $orderObject = $this->_objectManager->get('\Magento\Sales\Model\Order'); 
			$order = $orderObject->loadByIncrementId($primaryOrderId);
			$items = $order->getAllVisibleItems();
			foreach ($items as $item){
					 //$options = $item->getProductOptions(); 
					 $product= $item->getProductId();
					 if($this->isHaveSubscriptionOptions($product)) {
						 $string[] = $this->getCurrencySymbol() . number_format($item->getPrice(), 2);
					 /*$addOptions = $options['additional_options'];
					 $sub_type = $addOptions[0]['label'];
					 if (isset($sub_type) && $sub_type =='Subscription Type')
					 {
							$string[] = $this->getCurrencySymbol() . number_format($item->getPrice(), 2);
					 }*/
					}
		  }
      if (count($string) == 1)
      {
         $products = implode('', $string);
      }
      else
      {
         $products = implode(',', $string);
      }
      return $products;
   }
	 # Get Plan Name
	 public function getPlanName($termType) {
		$termId=$this->termsFactory->create()->load($termType)->getPlanId();
		$plan=$this->planFactory->create()->load($termId);
		return $plan->getPlanName();
	 }
	
	 # Get Term Name
	 public function getTermName($termType) {
		return $this->termsFactory->create()->load($termType)->getLabel();
	 }
	# Get Subscription Label Status
	 public function getSubscriptionStatusLabelForEmail ($labelId) {
		 return $this->subscriptionStatus->getLabel($labelId);
	 }
	 /** Return currency symbol based on currency code
   * @return $currency_symbol 
   */
   public function getCurrencySymbol() {
      return $this ->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
   }
	/**
   * Get Recurring Cofing
   * @return true/false
   */
	 public function getConfig ($config){
		return $this->scopeConfig->getValue($config,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
}
