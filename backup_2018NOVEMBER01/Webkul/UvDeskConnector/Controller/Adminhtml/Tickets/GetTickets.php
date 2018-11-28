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
 * GetTickets class
 */
class GetTickets extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $_resultPageFactory;

    /** @var \UvDeskConnector\Model\TicketManager */
    protected $_ticketManager;

    /** @var \Webkul\UvDeskConnector\Helper\Tickets */
    protected $_ticketsHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonResultFactory;

    /**
     * __construct function
     *
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\View\Result\PageFactory       $resultPageFactory
     * @param \Webkul\UvDeskConnector\Model\TicketManager      $ticketManager
     * @param \Webkul\UvDeskConnector\Helper\Tickets           $ticketsHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\UvDeskConnector\Model\TicketManager $ticketManager,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketsHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
    
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_ticketManager = $ticketManager;
        $this->_ticketsHelper = $ticketsHelper;
        $this->_jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        $result = $this->_jsonResultFactory->create();
        $page = $this->checkStatus('pageNo');
        $label = $this->checkStatus('labels');
        $labelId = $labelId = $this->checkStatus('labelsId');
        $tab = $this->checkStatus('tab');
        $agent = $this->checkStatus('agent');
        $customer = $this->checkStatus('customer');
        $group = $this->checkStatus('group');
        $team = $this->checkStatus('team');
        $priority = $this->checkStatus('priority');
        $type = $this->checkStatus('type');
        $tag = $this->checkStatus('tag');
        $mailbox =$this->checkStatus('mailbox');
        $tickets = $this->_ticketManager->getAllTickets($page, $label, $labelId, $tab, $agent, $customer, $group, $team, $priority, $type, $tag, $mailbox);
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
