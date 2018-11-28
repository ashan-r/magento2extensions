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
namespace Webkul\UvDeskConnector\Model;

use Webkul\UvDeskConnector\Logger\UvdeskLogger;

/**
 * TicketManager class
 */
class TicketManager
{

    /**
     * @var \Webkul\UvDeskConnector\Helper\Data
     */
    protected $_helperData;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Webkul\UvDeskConnector\Logger\UvdeskLogger
     */
    protected $_logger;

    /**
     * __construct function
     *
     * @param \Webkul\UvDeskConnector\Helper\Data         $helperData
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Framework\Json\Helper\Data         $jsonHelper
     * @param UvdeskLogger                                $uvdeskLogger
     */
    public function __construct(
        \Webkul\UvDeskConnector\Helper\Data $helperData,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        UvdeskLogger $uvdeskLogger
    ) {
    
        $this->_helperData = $helperData;
        $this->_messageManager = $messageManager;
        $this->_customerSession = $customerSession;
        $this->_jsonHelper = $jsonHelper;
        $this->_logger = $uvdeskLogger;
    }

    /**
     * Curl request to get all tickets of UvDesk.
     *
     * @return json.
     */
    public function getAllTicketsAccToLabel($label, $isLabelId = false)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // Return  tickets
        if (!$isLabelId) {
            $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json?'.$label;
        } else {
            $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json?label='.$label;
        }
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200) {
            return $response;
        } else {
            $this->log('', ['response'=>$response, 'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
        }
        curl_close($ch);
    }

    /**
     * Curl request to get all tickets of UvDesk.
     *
     * @return json.
     */
    public function getAllTickets($page = null, $labels = null, $labelId = null, $tab = null, $agent = null, $customer = null, $group = null, $team = null, $priority = null, $type = null, $tag = null, $mailbox = null, $status = null, $sort = null)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $str = '';
        if (isset($agent)) {
            $str.='&agent='.$agent;
        }
        if (isset($tab)) {
            $str.='&status='.$tab;
        }
        if (isset($customer)) {
            $str.='&customer='.$customer;
        }
        if (isset($page)) {
            $str.='&page='.$page;
        }
        if (isset($group)) {
            $str.='&group='.$group;
        }
        if (isset($team)) {
            $str.='&team='.$team;
        }
        if (isset($priority)) {
            $str.='&priority='.$priority;
        }
        if (isset($type)) {
            $str.='&type='.$type;
        }
        if (isset($tag)) {
            $str.='&tag='.$tag;
        }
        if (isset($mailbox)) {
            $str.='&mailbox='.$mailbox;
        }
        if (isset($status)) {
            $str.='&status='.$status;
        }
        if (isset($sort)) {
            $str.='&sort='.$sort.'&direction=asc';
        }
        if (isset($labels)) {
            $str.="&".$labels;
        } else {
            if (isset($labelId)) {
                $str.="&label=".$labelId;
            }
        }
        // Return  tickets
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json?'.$str;
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200) {
            return $response;
        } else {
            $this->log('', ['response'=>$response, 'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
        }
        curl_close($ch);
    }

    /**
     * Curl request to get all data for filters of UvDesk.
     *
     * @return Json.
     */
    public function getFilterDataFor($filterType)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // Return  tickets
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/filters.json?'.$filterType.'=1';
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        ];
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200) {
            return $response;
        } else {
            $this->log('', ['response'=>$response, 'info'=>$info]);
            return false;
        }
        curl_close($ch);
    }

    /**
     * Curl request to create ticket in UvDesk.
     *
     * @return String.
     */
    /*
    public function createTicket($ticketData)
    {
        $access_token = $this->_helperData->getAccessToken();
        $company_domain = $this->_helperData->getCompanyDomainName();
        // ticket url
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json';
        $data = json_encode($ticketData);
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer '.$access_token,
            'Content-type: application/json'
        ];
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($server_output, 0, $header_size);
        $response = substr($server_output, $header_size);
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            $this->_messageManager->addSuccess(__(' Success ! Ticket has been created successfully.'));
            $customerUvdeskId  = $this->_customerSession->getCustomerUvdeskId();
            if (!isset($customerUvdeskId)) {
                $customerEmail = $this->_customerSession->getCustomer()->getEmail();
                $customerUvDeskData = $this->getCustomerFromEmail($customerEmail);
                if (!empty($customerUvDeskData['customers'])) {
                    $customerUvDeskId = $customerUvDeskData['customers'][0]['id'];
                    $this->_customerSession->setCustomerUvdeskId($customerUvDeskId);
                }
            }
            return true;
        } elseif ($info['http_code'] == 400) {
            $this->_messageManager->addError(__(' Error, request data not valid. (http-code: 400).'));
        } elseif ($info['http_code'] == 404) {
            $this->_messageManager->addError(__('Error, resource not found (http-code: 404)'));
        } else {
            $this->_messageManager->addError(__('Error, HTTP Status Code :%1', $info['http_code']));
        }
        curl_close($ch);
    }
    */

    /**
     * Curl request to create ticket in UvDesk.
     *
     * @return String.
     */
    public function createTicket($data, $mime_boundary)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // ticket url
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json';
        $data = $data;
        $headers = [
        "Authorization: Bearer ".$access_token,
        "Content-type: multipart/form-data; boundary=" .$mime_boundary,
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $server_output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($server_output, 0, $header_size);
        $response = substr($server_output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            $this->_messageManager->addSuccess(__(' Success ! Ticket has been created successfully.'));
            $customerUvdeskId  = $this->_customerSession->getCustomerUvdeskId();
            if (!isset($customerUvdeskId)) {
                $customerEmail = $this->_customerSession->getCustomer()->getEmail();
                $customerUvDeskData = $this->getCustomerFromEmail($customerEmail);
                if (!empty($customerUvDeskData['customers'])) {
                    $customerUvDeskId = $customerUvDeskData['customers'][0]['id'];
                    $this->_customerSession->setCustomerUvdeskId($customerUvDeskId);
                }
            }
            return true;
        } else {
            $this->log('', ['response'=>$response, 'info'=>$info]);
            $this->_messageManager->addError(__('We are not able to proceed your request. Please contact administration'));
        }
        curl_close($ch);
    }

    /**
     * Curl request to get the customer detail via email from UvDesk.
     *
     * @return  Json.
     */
    public function getCustomerFromEmail($customerEmail = null)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // ticket url
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/customers.json?email='.$customerEmail;
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer '.$access_token
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($server_output, 0, $header_size);
        $response = substr($server_output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            return $response;
        } else {
            $this->log('', ['response'=>$response, 'info'=>$info]);
            return false;
        }
        curl_close($ch);
    }

    /**
     * Curl request to create customer in UvDesk.
     *
     * @return  Json.
     */
    public function createCustomer()
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // ticket url
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/customer.json';
        $data = json_encode($ticketData);
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer '.$access_token,
            'Content-type: application/json'
        ];
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($server_output, 0, $header_size);
        $response = substr($server_output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            $this->_messageManager->addSuccess(__(' Success ! Ticket has been created successfully.'));
            return true;
        } else {
            $this->log('', ['response'=>$response, 'info'=>$info]);
            $message = $this->_helperData->getErrorMessage($response);
            $this->_messageManager->addError(__($message));
            return false;
        }
        curl_close($ch);
    }

    /**
     * Curl request to get the ticket types in UvDesk.
     *
     * @return  Json.
     */
    public function getTicketTypes()
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // ticket url
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket-types.json?';
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer '.$access_token
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($server_output, 0, $header_size);
        $response = substr($server_output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            return $response;
        } else {
            $this->log('', ['response'=>$response, 'info'=>$info]);
            return "";
        }
        curl_close($ch);
    }

    /**
     * Curl request to get the ticket types in UvDesk.
     *
     * @return  JSON.
     */
    public function getTicketThread($ticketId = 0, $pageNo)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // Return  tickets
        $str = "";
        if (isset($pageNo)) {
            $str.='page='.$pageNo;
        }
        if ($pageNo == null) {
            $pageNo = "";
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/threads.json?'.$str;
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200) {
            return $response;
        } else {
            if (isset($response['error'])) {
                $this->log('', ['response'=>$response, 'info'=>$info]);
                return $response;
            }
            return ['error'=>true, 'error_description'=> __('There is some error in getting the thread. Please check log.')];
        }
        curl_close($ch);
    }

    /**
     * Curl request to get the information of single tickets in UvDesk.
     *
     * @return  JSON.
     */
    public function getSingleTicketData($ticketIncrementId)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $str = '';
        // Return  tickets
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketIncrementId.'.json';
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200) {
            return $response;
        } else {
                $this->log('', ['response'=>$response, 'info'=>$info]);
                return false;
        }
        curl_close($ch);
    }

    /**
     * Curl request to get the ticket types in UvDesk.
     *
     * @return  JSON.
     */
    public function addReplyToTickett($ticketId, $ticketIncrementId, $data)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // ticket url
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/threads.json';
        $data = json_encode($data);
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer '.$access_token,
            'Content-type: application/json'
        ];
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($server_output, 0, $header_size);
        $response = substr($server_output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            $this->_messageManager->addSuccess(__('Success ! Reply added successfully.'));
            return true;
        } else {
            $this->log('', ['response'=>$response, 'info'=>$info]);
            $message = $this->_helperData->getErrorMessage($response);
            $this->_messageManager->addError(__($message));
            return false;
        }
        curl_close($ch);
    }

    /**
     * Curl request to add a reply to a tickets in UvDesk.
     *
     * @return  String.
     */
    public function addReplyToTicket($ticketId, $ticketIncrementId, $data, $mime_boundary)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // ticket url
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/threads.json';
        $data = $data;
         $headers = [
        "Authorization: Bearer ".$access_token,
        "Content-type: multipart/form-data; boundary=" .$mime_boundary,
         ];
         $ch = curl_init($url);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         curl_setopt($ch, CURLOPT_HEADER, true);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
         curl_setopt($ch, CURLOPT_SSLVERSION, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         $server_output = curl_exec($ch);
         $info = curl_getinfo($ch);
         $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
         $headers = substr($server_output, 0, $header_size);
         $response = substr($server_output, $header_size);
         $response = $this->getJsonDecodeResponse($response);
         $err = curl_error($ch);
         if ($info['http_code'] == 200 || $info['http_code'] == 201) {
             $this->_messageManager->addSuccess(__('Success ! Reply added successfully.'));
             return true;
            } else {
                $this->log('', ['response'=>$response, 'info'=>$info]);
                $message = $this->_helperData->getErrorMessage($response);
                $this->_messageManager->addError(__($message));
                return false;
            }
            curl_close($ch);
    }

    /**
     * Curl request to download the attachment of a ticket in UvDesk.
     *
     * @return  Json.
     */
    public function downloadAttachment($attachmenId)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // Return  tickets
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/attachment/'.$attachmenId.'.json';
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        ];
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        if ($info['http_code'] == 200) {
            return ['response'=>$response,'info'=>$info];
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('', ['response'=>$response, 'info'=>$info]);
            return false;
        }
        curl_close($ch);
    }

    /**
     * Curl request to delete the tickets in UvDesk.
     *
     * @return  String.
     */
    public function trashTicket()
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        // Return  tickets
        $url = 'https://'.$company_domain.'.uvdesk.com/en/ /api/ticket/4802/trash.json';
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        ];
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        if ($info['http_code'] == 200) {
            return ['response'=>$response,'info'=>$info]    ;
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('', ['response'=>$response, 'info'=>$info]);
            return false;
        }
        curl_close($ch);
    }

    /**
     * Curl request to change the agent of a ticket in UvDesk.
     *
     * @return  String.
     */
    public function assignAgentToTicket($ticketId, $agentId)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $data = '{"id": "'.$agentId.'"}';
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/agent.json';
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        ];
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        if ($info['http_code'] == 200) {
            return ['response'=>$response,'info'=>$info]    ;
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('', ['response'=>$response, 'info'=>$info]);
            return false;
        }
        curl_close($ch);
    }

    /**
     * Curl request to delete the tickets in UvDesk.
     *
     * @return  String.
     */
    public function deleteTicket($ticketIds)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $ids['ids'] = $ticketIds;
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json';
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        "content-type: application/json"
        ];
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ids));
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        curl_close($ch);
        if ($info['http_code'] == 200) {
            return ['response'=>true];
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('', ['response'=>$response, 'info'=>$info]);
            return ['response'=>false];
        }
    }

    /**
     * Curl request to get all members of UvDesk.
     *
     * @return  String.
     */
    public function getAllMembers()
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/members.json?fullList=name';
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        "content-type: application/json"
        ];
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        $response = $this->getJsonDecodeResponse($response);
        curl_close($ch);
        if ($info['http_code'] == 200) {
            return $response;
        } else {
            $this->log('', ['response'=>$response, 'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
            $this->log('', ['response'=>$response, 'info'=>$info]);
            $message = $this->_helperData->getErrorMessage($response);
            $this->_messageManager->addError(__($message));
            return ['error'=>'true'];
        }
    }

    /**
     * checkCredentials function check the credentials are correct or not before save in configuration
     *
     * @param string $access_token
     * @param string $company_domain
     * @return bool
     */
    public function checkCredentials($access_token = "", $company_domain = "") {
        if (preg_match('/^\*+$/', $access_token)) {
            $access_token = $this->_helperData->getAccessToken();
        }
        // Return  tickets 
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json';
        $ch = curl_init($url);
        $headers = array(
            'Authorization: Bearer '.$access_token,
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        curl_close($ch);
        if($info['http_code'] == 200) {
            return true;
        } else  {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('', ['response'=>$response, 'info'=>$info]);
            return false;
        }
    }

    /**
     * get the json decoded data of reponse
     *
     * @param string $reponse
     * @return array
     */
    public function getJsonDecodeResponse($reponse = "")
    {
        return $this->_jsonHelper->jsonDecode($reponse);
    }

    /**
     * getAccessToken function get the access token from configuration.
     *
     * @return string|array
     */
    public function getAccessToken()
    {
        if ($this->_helperData->getAccessToken()) {
            return $this->_helperData->getAccessToken();
        }
        return ['error'=> true, 'error_description'=> 'The access token field is blank. Please provide the access token'];
    }

    /**
     * getCompanyDomainName function get the access token from configuration.
     *
     * @return string|array
     */
    public function getCompanyDomainName()
    {
        if ($this->_helperData->getCompanyDomainName()) {
            return $this->_helperData->getCompanyDomainName();
        }
        return ['error'=> true, 'error_description'=> 'The company domain name field is blank. Please provide the company domain'];
    }

    /**
     * log function log the error message.
     *
     * @param string $message
     * @param array $data
     * @return void
     */
    public function log($message = "", $data = [])
    {
        $this->_logger->critical($message, $data);
    }
}
