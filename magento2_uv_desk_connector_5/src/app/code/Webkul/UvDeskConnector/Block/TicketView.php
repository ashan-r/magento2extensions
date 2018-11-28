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

namespace Webkul\UvDeskConnector\Block;

class TicketView extends \Magento\Framework\View\Element\Template
{
    /**
     * __construct description
     * @param \Magento\Framework\View\Element\Template\Context $context       [description]
     * @param \Webkul\UvDeskConnector\Helper\Tickets           $ticketHelper  [description]
     * @param \Webkul\UvDeskConnector\Model\TicketManagerCustomer      $ticketManagerCustomer [description]
     * @param array                                            $data          [description]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketHelper,
        \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ticketHelper = $ticketHelper;
        $this->_ticketManagerCustomer = $ticketManagerCustomer;
        $this->_wysiwygConfig = $wysiwygConfig;
    }

    /**
     * getWysiwygConfig function get the configuration of wyswyg editor
     *
     * @return json
     */
    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData(), true);
        return $config;
    }

    /**
     * getErrorMessage function get the error message acc to theresponse comes from Uvdesk  end
     *
     * @param array $tickets
     * @return string
     */
    public function getErrorMessage($tickets = [])
    {
        if (isset($tickets['error_description'])) {
            return $tickets['error_description']." Please contact administration.";
        }
        if (isset($tickets['error']) && $tickets['error']!==1 && $tickets['error']!==0) {
            return $tickets['error']." Please contact administration.";
        }
        return __("Some thing went wrong in the Uvdesk configuration. Please contact the administration");
    }

    /**
     * getLoggedInUserDetail function get the logged in user details
     *
     * @return array
     */
    public function getLoggedInUserDetail()
    {
        $customerDetal = $this->_ticketHelper->getLoggedInUserDetail();
        return $customerDetal;
    }

    /**
     * getTicketThread function get the ticket thread depend upon ticket id
     *
     * @return array
     */
    public function getTicketThread()
    {
        $ticketId = $this->getRequest()->getParam('id');
        $threads = $this->_ticketManagerCustomer->getTicketThread($ticketId, null);
        return $threads;
    }

    /**
     * isCustomer function check the value is equal to customer or not?
     *
     * @param string $name
     * @return string
     */
    public function isCustomer($name)
    {
        if ($name == 'customer') {
            return 'customer';
        }
        return '';
    }

    /**
     * getSingleTicketData function get detail of single ticke based on ticket's increment id
     *
     * @return array
     */
    public function getSingleTicketData()
    {
        $ticketIncrementId = $this->getRequest()->getParam('increment_id');
        $ticketData = $this->_ticketManagerCustomer->getSingleTicketData($ticketIncrementId);
        return $ticketData ;
    }

    /**
     * getCollaboratorImage function get the added collaborator image
     *
     * @param string $smallThumbnail
     * @return string
     */
    public function getCollaboratorImage($smallThumbnail)
    {
        if (empty($smallThumbnail)) {
            return "https://cdn.uvdesk.com/uvdesk/images/d94332c.png";
        }
        return $smallThumbnail;
    }
}
