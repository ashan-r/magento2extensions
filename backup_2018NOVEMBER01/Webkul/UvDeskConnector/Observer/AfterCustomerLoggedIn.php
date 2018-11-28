<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_UvDeskConnector
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\UvDeskConnector\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
 
class AfterCustomerLoggedIn implements ObserverInterface
{
    /** @var Magento\Framework\App\RequestInterface */
    protected $_request;

    /** @var \Magento\Customer\Model\Session */
    protected $_customerSession;

    /** @var \Webkul\UvDeskConnector\Model\TicketManager */
    protected $_ticketManager;

    /**
     * __construct function
     *
     * @param \Psr\Log\LoggerInterface                    $loggerInterface
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Webkul\UvDeskConnector\Model\TicketManager $ticketManager
     * @param RequestInterface                            $requestInterface
     */
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\UvDeskConnector\Model\TicketManager $ticketManager,
        RequestInterface $requestInterface
    ) {
        $this->_logger = $loggerInterface;
        $this->_customerSession = $customerSession;
        $this->_ticketManager = $ticketManager;
        $this->_request = $requestInterface;
    }
    
    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */

    public function execute(Observer $observer)
    {
        $customerUvDeskId = null;
        $customerData = $observer->getCustomer()->getData();
        $customerEmail = $customerData['email'];
        // $customerId = $observer->getCustomerDataObject()->getId();
        $controller = $this->_request->getControllerName();
        $customerDataUvDesk = $this->_ticketManager->getCustomerFromEmail($customerEmail);
        if (!empty($customerDataUvDesk['customers'])) {
            $customerUvDeskId = $customerDataUvDesk['customers'][0]['id'];
        }
        // if (in_array($controller, array('index', 'account')) && $customerId) {
        if (in_array($controller, ['account']) && $customerUvDeskId) {
            $this->_customerSession->setCustomerUvdeskId($customerUvDeskId);
        }
        return true;
    }
}
