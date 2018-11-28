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
namespace Milople\Recurringandsubscriptionpayments\Observer\Admin;
class Create extends \Magento\Sales\Model\AdminOrder\Create {

	  public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Framework\Message\ManagerInterface $messageManager,
      	\Magento\Sales\Model\AdminOrder\Product\Quote\Initializer $quoteInitializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Model\Metadata\FormFactory $metadataFormFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\AdminOrder\EmailSender $emailSender,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Quote\Model\Quote\Item\Updater $quoteItemUpdater,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        array $data = []
    ) {
        $this->logger=$logger;
        parent::__construct($objectManager,$eventManager,$coreRegistry,$salesConfig,$quoteSession,$logger,$objectCopyService,$messageManager,$quoteInitializer,$customerRepository,$addressRepository,$addressFactory,$metadataFormFactory,$groupRepository,$scopeConfig,$emailSender,$stockRegistry, $quoteItemUpdater,$objectFactory,$quoteRepository,$accountManagement,$customerFactory,$customerMapper,$quoteManagement,$dataObjectHelper,$orderManagement,$quoteFactory,$data);
    }
    public function updateQuoteItems($items)
    {
				$this->logger->addDebug('New file for updating');
  	    if (!is_array($items)) {
            return $this;
        }

        try {
            foreach ($items as $itemId => $info) {
                if (!empty($info['configured'])) {
                    $item = $this->getQuote()->updateItem($itemId, $this->objectFactory->create($info));
                    $info['qty'] = (double)$item->getQty();
			    } else {
                    $item = $this->getQuote()->getItemById($itemId);
                    if (!$item) {
                        continue;
                    }
                    $info['qty'] = (double)$info['qty'];
                }
								$item->save();
                $this->recollectCart();
                $this->quoteItemUpdater->update($item, $info);
                if ($item && !empty($info['action'])) {
                    $this->moveQuoteItem($item, $info['action'], $item->getQty());
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->recollectCart();
            throw $e;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        $this->recollectCart();
				$this->logger->addDebug('End of new file for updating the quote');
        return $this;
    }
}