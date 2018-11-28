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
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
class AuthorizeCim{
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
		\Psr\Log\LoggerInterface $logger
	) {
		$this -> scopeConfig = $scopeConfig;
		$this->logger = $logger;
	}
	/**
     * Create Authorizenet Customer Profile
     *
     * @param $data
     * @param \Magento\Quote\Model\Quote $quote
     * @return AnetAPI\AnetApiResponseType
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomerProfile($data,$quote)
    {			
		$merchantId = $this->scopeConfig->getValue('payment/authorizenet_directpost/login');
    $merchantTransactionKey = $this->scopeConfig->getValue('payment/authorizenet_directpost/trans_key');
    $istestMode = $this->scopeConfig->getValue('payment/authorizenet_directpost/test');		
		$billingAddress = $quote->getBillingAddress();
		$street = $billingAddress->getStreet();
		// Create Authentication for Request
		$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
		$merchantAuthentication->setName($merchantId);
		$merchantAuthentication->setTransactionKey($merchantTransactionKey);	
		// Set the transaction's refId
		$refId = 'ref' . time();
		// Create CC for Request
		$creditCard = new AnetAPI\CreditCardType();
		$creditCard->setCardNumber($data['cc_number']);
        if (intval($data['cc_exp_month']) < 10) $data['cc_exp_month'] = '0' . $data['cc_exp_month'];
        $creditCard->setExpirationDate($data['cc_exp_year'] . '-' . $data['cc_exp_month']);
        if($data['cc_id']) {
            $creditCard->setCardCode($data['cc_id']);
        }	
		$paymentCreditCard = new AnetAPI\PaymentType();
		$paymentCreditCard->setCreditCard($creditCard);		
		// Create the Bill To info for new payment type
		$billto = new AnetAPI\CustomerAddressType();
		$billto->setFirstName($billingAddress->getFirstname());
		$billto->setLastName($billingAddress->getLastname());
		$billto->setCompany($billingAddress->getCompany());
		$billto->setAddress($street[0]);
		$billto->setCity($billingAddress->getCity());
		$billto->setState($billingAddress->getRegion());
		$billto->setZip($billingAddress->getPostcode());
		$billto->setCountry($billingAddress->getCountryId());
		$billto->setPhoneNumber($billingAddress->getTelephone());
		$billto->setfaxNumber($billingAddress->getFax());
		// Create a new Customer Payment Profile object
		$paymentprofile = new AnetAPI\CustomerPaymentProfileType();
		$paymentprofile->setCustomerType('individual');
		$paymentprofile->setBillTo($billto);
		$paymentprofile->setPayment($paymentCreditCard);
		$paymentprofile->setDefaultPaymentProfile(true);
		$paymentpros[] = $paymentprofile;
		// Create a new CustomerProfileType and add the payment profile object
		$customerProfile = new AnetAPI\CustomerProfileType();		
		$customerProfile->setMerchantCustomerId($quote->getId());
		$customerProfile->setEmail($quote->getCustomerEmail());
		$customerProfile->setPaymentProfiles($paymentpros);
		// Assemble the complete transaction request
		$request = new AnetAPI\CreateCustomerProfileRequest();
		$request->setMerchantAuthentication($merchantAuthentication);
		$request->setRefId($refId);
		$request->setProfile($customerProfile);
		// Create the controller and get the response
		$controller = new AnetController\CreateCustomerProfileController($request);
		if ($this->scopeConfig->getValue('payment/authorizenet_directpost/cgi_url') == "https://test.authorize.net/gateway/transact.dll") {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }
		if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
			$paymentProfiles = $response->getCustomerPaymentProfileIdList();
			$result['result'] = true;
			$result['customerProfileId'] = $response->getCustomerProfileId();
			$result['paymentProfileId'] = $paymentProfiles[0];
			return $result;
			
		} else {			
			$errorMessages = $response->getMessages()->getMessage();
			if($errorMessages[0]->getCode() == "E00039"){
				$existingcustomerprofileid = $this->getInbetweenStrings("ID "," already",$errorMessages[0]->getText());
				// Assemble the complete transaction request
				$paymentprofilerequest = new AnetAPI\CreateCustomerPaymentProfileRequest();
				$paymentprofilerequest->setMerchantAuthentication($merchantAuthentication);
				// Add an existing profile id to the request
				$paymentprofilerequest->setCustomerProfileId($existingcustomerprofileid[0]);
				$paymentprofilerequest->setPaymentProfile($paymentprofile);
				$paymentprofilerequest->setValidationMode("liveMode");
				// Create the controller and get the response
				$paymentController = new AnetController\CreateCustomerPaymentProfileController($paymentprofilerequest);
				
				if ($this->scopeConfig->getValue('payment/authorizenet_directpost/cgi_url') == "https://test.authorize.net/gateway/transact.dll") {
					$paymentResponse = $paymentController->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
				} else {
					$paymentResponse = $paymentController->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
				}
				if(($paymentResponse != null) && ($paymentResponse->getMessages()->getResultCode() == "Ok")){			
					$result['result'] = true;
					$result['customerProfileId'] =  $existingcustomerprofileid[0];
					$result['paymentProfileId'] = $paymentResponse->getCustomerPaymentProfileId();
					return $result;
				}else{
					$errorMessages = $paymentResponse->getMessages()->getMessage();
					$emessage = "Error on create profile of authorizenet : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";			  
					$result['result'] = false;
					$result['message'] = $emessage;
					return $result;
				}
			}else{
				$emessage = "Error on create profile of authorizenet : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
				$result['result'] = false;
				$result['message'] = $emessage;
				return $result;
			}			
		}		
	}
	public function autoCaptureAuthorizenet($profileid, $paymentprofileid, $amount){
		$merchantId = $this->scopeConfig->getValue('payment/authorizenet_directpost/login');
        $merchantTransactionKey = $this->scopeConfig->getValue('payment/authorizenet_directpost/trans_key');
        
		
		$merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
		$merchantAuthentication->setName($merchantId);
		$merchantAuthentication->setTransactionKey($merchantTransactionKey);
		
		$refId = 'ref' . time();
		
		$profileToCharge = new AnetAPI\CustomerProfilePaymentType();
		$profileToCharge->setCustomerProfileId($profileid);
		$paymentProfile = new AnetAPI\PaymentProfileType();
		$paymentProfile->setPaymentProfileId($paymentprofileid);
		$profileToCharge->setPaymentProfile($paymentProfile);
		$transactionRequestType = new AnetAPI\TransactionRequestType();
		$transactionRequestType->setTransactionType( "authCaptureTransaction"); 
		$transactionRequestType->setAmount($amount);
		$transactionRequestType->setProfile($profileToCharge);
		$request = new AnetAPI\CreateTransactionRequest();
		$request->setMerchantAuthentication($merchantAuthentication);
		$request->setRefId($refId);
		$request->setTransactionRequest( $transactionRequestType);		
		$controller = new AnetController\CreateTransactionController($request);
		if ($this->scopeConfig->getValue('payment/authorizenet_directpost/cgi_url') == "https://test.authorize.net/gateway/transact.dll") {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }
		if ($response != null)
		{
			if($response->getMessages()->getResultCode() == 'Ok'){
				$result["status"] = true;
				$result["trans_id"] = $response->getTransactionResponse()->getTransId();
				return $result;
			}else{
				$result["status"] = false;
				$errorMessages = $response->getMessages()->getMessage();
				$result["error_code"] = $errorMessages[0]->getCode();
				return $result;
			}
			
		}
		$result["status"] = false; 
		$result["error_code"] = 0;
		return $result;
	}
	/**
     * Give String Between Two String
     *
     * @param String $start
     * @param String $end
     * @param String $str
     * @return string or Null     
     */
	Private function getInbetweenStrings($start, $end, $str){
		$matches = array();
		$regex = "/$start([a-zA-Z0-9_]*)$end/";
		preg_match_all($regex, $str, $matches);
		return $matches[1];
	}
}	