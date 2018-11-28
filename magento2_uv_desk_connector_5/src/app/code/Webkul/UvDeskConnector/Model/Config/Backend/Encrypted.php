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

namespace Webkul\UvDeskConnector\Model\Config\Backend;


class Encrypted extends \Magento\Config\Model\Config\Backend\Encrypted
{


    /**
     * Encrypt value before saving
     *
     * @return void
     */
    public function beforeSave()
    {
        $uvdeskToken = $this->getFieldsetDataValue("uvdesk_accesstoken");
        $uvdeskDomainName = $this->getFieldsetDataValue("uvdesk_companydomain");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ticketManager = $objectManager->create('Webkul\UvDeskConnector\Model\TicketManager');
        $bool = $ticketManager->checkCredentials($uvdeskToken, $uvdeskDomainName);
        if ($bool) {
            parent::beforeSave();
        } else {
            $this->_dataSaveAllowed = false;
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Credentials.'));
        }
    }

}
