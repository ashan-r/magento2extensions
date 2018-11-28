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
 * TicketManagerCustomer class
 */
class TicketManagerCustomer
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
     * @var \Webkul\UvDeskConnector\Helper\Tickets
     */
    protected $_ticketHelper;

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
        \Webkul\UvDeskConnector\Helper\Tickets $ticketHelper,
        UvdeskLogger $uvdeskLogger
    ) {

        $this->_helperData = $helperData;
        $this->_messageManager = $messageManager;
        $this->_customerSession = $customerSession;
        $this->_jsonHelper = $jsonHelper;
        $this->_ticketHelper = $ticketHelper;
        $this->_logger = $uvdeskLogger;
    }

    /**
     * Curl request to download the attachment of a ticket in UvDesk.
     *
     * @return  Json.
     */
    public function downloadAttachment($attachmenId)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['response']['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['response']['error'])) {
            return $company_domain;
        }
        // Return  tickets
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/attachment/'.$attachmenId.'.json';
        $ch = curl_init($url);
        $headers =['Authorization: Bearer '.$access_token,];
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
            $this->log("Error in download attachment from client end.", ['response'=>$response,'info'=>$info]);
            return false;
        }
        curl_close($ch);
    }

    /**
     * createTicket create ticket curl request
     *
     * @param [type] $data
     * @param [type] $mime_boundary
     * @return boolean
     */
    public function createTicket($data, $mime_boundary)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['response']['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['response']['error'])) {
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
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            $this->_messageManager->addSuccess(__(' Success ! Ticket has been created successfully.'));
            // $customerUvdeskId  = $this->_customerSession->getCustomerUvdeskId();
            // if (!isset($customerUvdeskId)) {
            //     $customerEmail = $this->_customerSession->getCustomer()->getEmail();
            //     $customerUvDeskData = $this->getCustomerFromEmail($customerEmail);
            //     if (!empty($customerUvDeskData['customers'])) {
            //         $customerUvDeskId = $customerUvDeskData['customers'][0]['id'];
            //         $this->_customerSession->setCustomerUvdeskId($customerUvDeskId);
            //     }
            // }
            return true;
        } else {
            $this->log('There is an error in creating ticket from customer end', ['response'=>$response, 'info'=>$info]);
            $this->_messageManager->addError(__('We are not able to proceed your request. Please contact administration'));
        }
        curl_close($ch);
    }

    /**
     * getAllTickets function is getting all tickets of uvdesk acc to the filters
     *
     * @param int $page
     * @param int $labels
     * @param int $tab
     * @param int $agent
     * @param int $customer
     * @param int $group
     * @param int $team
     * @param int $priority
     * @param int $type
     * @param int $tag
     * @param int $mailbox
     * @param int $status
     * @param int $sort
     * @return array
     */
    public function getAllTickets($page = null, $labels = null, $tab = null, $agent = null, $customer = null, $group = null, $team = null, $priority = null, $type = null, $tag = null, $mailbox = null, $status = null, $sort = null)
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
        }
        $customerEmail = $this->_ticketHelper->getLoggedInUserDetail()['email'];
        $str.="&actAsType=customer&actAsEmail=".$customerEmail;
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
        if ($info['http_code'] == 200) {
            return $this->getJsonDecodeResponse($response);
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('There is some error in getting all tickets at customer end', ['response'=>$response, 'info'=>$info]);
            return $response;
        }
        curl_close($ch);
    }

    /**
     * Curl request to get the ticket types in UvDesk.
     *
     * @param integer $ticketId
     * @param integer $pageNo
     * @return array
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
        if ($info['http_code'] == 200) {
            return $this->getJsonDecodeResponse($response);
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log("Due to some issue ticket thread cannot be fetched at customer end.", ['response'=>$response,'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
            return ['error'=>true, 'error_description'=> __('Due to some issue ticket thread cannot be fetched. Please contact administration.')];
        }
        curl_close($ch);
    }

    /**
     * Curl request to get the ticket types in UvDesk.
     *
     * @param integer $ticketId
     * @param integer $ticketIncrementId
     * @param array $data
     * @return array
     */
    public function addReplyToTickett($ticketId, $ticketIncrementId, $data)
    {
        $access_token = $this->_helperData->getAccessToken();
        $company_domain = $this->_helperData->getCompanyDomainName();
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
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            $this->_messageManager->addSuccess(__('Success ! Reply added successfully.'));
            return true;
        } elseif ($info['http_code'] == 400) {
            $this->_messageManager->addError(__(' Error, request data not valid. (http-code: 400).'));
            return false;
        } elseif ($info['http_code'] == 404) {
            $this->_messageManager->addError(__('Error, resource not found (http-code: 404)'));
            return false;
        } else {
            $this->_messageManager->addError(__('Error, HTTP Status Code :%1', $info['http_code']));
            return false;
        }
        curl_close($ch);
    }

    /**
     * Curl request to get the ticket types in UvDesk.
     *
     * @return array
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
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            return $this->getJsonDecodeResponse($response);
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('There is some error in getting ticket\'s types at customer end', ['rsponse'=>$response, 'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
            return ['error'=>true, 'error_description'=> __('There is an error in getting ticket type. Please contact administration.')];
        }
        curl_close($ch);
    }

    /**
     * Curl request to get all members of UvDesk.
     *
     * @return  array.
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
        };
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
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ids));
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        if ($info['http_code'] == 200) {
            return $this->getJsonDecodeResponse($response);
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('Due to some issue cannot get all members at customer end.', ['response'=>$response,'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
            return ['error'=>true, 'error_description'=> __('Due to some issue cannot get all members. Please contact administration.')];
        }
        curl_close($ch);
    }

    /**
     * Curl request to get the information of single tickets in UvDesk.
     *
     * @return  array.
     */
    public function getSingleTicketData($ticketIncrementId)
    {
        $access_token = $this->getAccessToken(1);
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
        if ($info['http_code'] == 200) {
            return $this->getJsonDecodeResponse($response);
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('There is some error in getting the ticket details at customer end', ['response'=>$response, 'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
            return ['error'=>true, 'error_description'=> __('There is some error in getting the ticket details. Please contact administration.')];
        }
        curl_close($ch);
    }

    /**
     * createCustomerAtUvDesk function is use to create customer at uvdesk.
     *
     * @param array $customerData
     * @return array
     */
    public function createCustomerAtUveDesk($customerData = [])
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/customers.json';
        $customerData = json_encode($customerData);
        $headers = [
        "Authorization: Bearer ".$access_token
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $customerData);
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
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            return $this->getJsonDecodeResponse($response);
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('There is an error in creating customer at uvdesk end', ['response'=>$response, 'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
            return "";
        }
    }

    /**
     * Curl request to get the customer detail via email from UvDesk.
     *
     * @param string $customerEmail
     * @return array
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
        if ($info['http_code'] == 200 || $info['http_code'] == 201) {
            return $this->getJsonDecodeResponse($response);
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('There is an error in getting detail from customer email at customer end', ['response'=>$response, 'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
            return "";
        }
        curl_close($ch);
    }

    /**
     * Curl request to change the agent of a ticket in UvDesk.
     *
     * @param integer $ticketId
     * @param string $email
     * @return array
     */
    public function addCollaborater($ticketId = null, $email = null)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $data = ["email"=>$email];
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/collaborator.json';
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        ];
        curl_setopt($ch, CURLOPT_POST, true);
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
            return $this->getJsonDecodeResponse($response);
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('There is some error in adding collaborator at customer end', ['response'=>$response, 'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
        }
        curl_close($ch);
    }

    /**
     * Curl request to delete the agent of a ticket in UvDesk.
     *
     * @param integer $ticketId
     * @param integer $collaboratorId
     * @return array
     */
    public function removeCollaborater($ticketId = null, $collaboratorId = null)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $data = ["collaboratorId"=>$collaboratorId];
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/collaborator.json';
        $ch = curl_init($url);
        $headers = [
        'Authorization: Bearer '.$access_token,
        "content-type: application/json"
        ];
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);
        $err = curl_error($ch);
        curl_close($ch);
        if ($info['http_code'] == 200) {
            return ['response'=>$this->getJsonDecodeResponse($response),'info'=>$info];
        } else {
            $response = $this->getJsonDecodeResponse($response);
            $this->log('There is some error in removing collaborator', ['response'=>$response, 'info'=>$info]);
            if (isset($response['error'])) {
                return $response;
            }
        }
        curl_close($ch);
    }

    /**
     * Curl request to add a reply to a tickets in UvDesk.
     *
     * @param integer $ticketId
     * @param integer $ticketIncrementId
     * @param [type] $data
     * @param [type] $mime_boundary
     * @return array
     */
    public function addReplyToTicket($ticketId, $ticketIncrementId, $data, $mime_boundary)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
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
         $err = curl_error($ch);
         if ($info['http_code'] == 200 || $info['http_code'] == 201) {
             $this->_messageManager->addSuccess(__('Success ! Reply added successfully.'));
             return true;
         } else {
                $response = $this->getJsonDecodeResponse($response);
                $this->log('There is an error in adding reply to ticket at customer end.', ['response'=>$response, 'info'=>$info]);
                if (isset($response['error'])) {
                    return $response;
                }
                return ['error'=>true, 'error_description'=>__('There is an error in adding reply to ticket')];
               }
            curl_close($ch);
    }

    /**
     * get the json decoded data of response
     *
     * @param string $response
     * @return array
     */
    public function getJsonDecodeResponse($response = "")
    {
        return $this->_jsonHelper->jsonDecode($response);
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
        $this->log('The access token field in configuration is blank');
        return ['error'=> true, 'error_description'=> __('We cannot proceed your request.')];
    }

    /**
     * getCompanyDomainName function get the domain name of company from configuration.
     *
     * @return string|array
     */
    public function getCompanyDomainName()
    {
        if ($this->_helperData->getCompanyDomainName()) {
            return $this->_helperData->getCompanyDomainName();
        }
        $this->log('The company domain field in configuration is blank');
        return ['error'=> true, 'error_description'=> __('We cannot proceed your request.')];
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
