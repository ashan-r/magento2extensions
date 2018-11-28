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

namespace Webkul\UvDeskConnector\Controller\Adminhtml\Tickets;

/**
 * GetTicketThread class
 */
class GetTicketThread extends \Magento\Backend\App\Action
{

    /**
     * @var \UvDeskConnector\Model\TicketManager
     */
    protected $_ticketManager;

    /**
     * @var \Webkul\UvDeskConnector\Helper\Tickets
     */
    protected $_ticketsHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonResultFactory;

    /**
     * __construct function
     *
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Webkul\UvDeskConnector\Model\TicketManager      $ticketManager
     * @param \Webkul\UvDeskConnector\Helper\Tickets           $ticketsHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\UvDeskConnector\Model\TicketManager $ticketManager,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketsHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
    
        parent::__construct($context);
        $this->_ticketManager = $ticketManager;
        $this->_ticketsHelper = $ticketsHelper;
        $this->_jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        $result = $this->_jsonResultFactory->create();
        $page = $this->checkStatus('pageNo');
        $ticketId = $this->checkStatus('ticketId');
        $tickets = $this->_ticketManager->getTicketThread($ticketId, $page);
        $formatedTickets = $this->_ticketsHelper->formatData($tickets);
        return $result->setData($formatedTickets);
    }

    /**
     * checkStatus function check the particular field is set in params array or not ?
     *
     * @param string $code
     * @return string|null
     */
    public function checkStatus($code)
    {
        $flag = $this->getRequest()->getParam($code);
        if (isset($flag)) {
            return $this->getRequest()->getParam($code);
        } else {
            return null;
        }
    }
    
    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_UvDeskConnector::tickets');
    }
}
