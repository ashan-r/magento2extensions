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
namespace Milople\Recurringandsubscriptionpayments\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	protected $scopeConfig;
	const HASH_SEPARATOR = ":::";
	const DB_DELIMITER = "\r\n";
	public function __construct(
	\Magento\Framework\App\RequestInterface $httpRequest, 
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Milople\Recurringandsubscriptionpayments\Helper\Config $confighelper,
	\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localedate,
	\Psr\Log\LoggerInterface $logger,
	\Magento\Framework\Stdlib\DateTime\DateTime $date
	) 
	{
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->configHelper = $confighelper;
		$this->logger=$logger;
		$this->_localeDate = $localedate;
		$this->storedate = $date; 
		$this->request=$httpRequest;
	}

	public function getDomain() {
		$domain =$this->request->getServer('SERVER_NAME');
		$temp = explode('.', $domain);
		$exceptions = array('co.uk', 'com.au', 'com.hk', 'co.nz', 'co.in', 'com.sg');
		$count = count($temp);
		if ($count === 1) {
			return $domain;
		}
		$last = $temp[($count - 2)] . '.' . $temp[($count - 1)];
		if (in_array($last, $exceptions)) {
			$new_domain = $temp[($count - 3)] . '.' . $temp[($count - 2)] . '.' . $temp[($count - 1)];
		} else {
			$new_domain = $temp[($count - 2)] . '.' . $temp[($count - 1)];
		}

		return $new_domain;
	}

	public function checkEntry($domain, $serial) {
		 $key = sha1(base64_decode('UmVjdXJyaW5nQW5kU3Vic2NyaXB0aW9uUGF5bWVudHM=1='));
		if (sha1($key . $domain) == $serial) {
			return true;
		}
		return false;
	}

	public function canRun($temp = '') {
		
		$domain =$this->request->getServer('SERVER_NAME');
		if ($domain == "localhost" || $domain == "127.0.0.1") {
			return true;
		}

		if ($temp == '') {
		   $temp = $this -> scopeConfig -> getValue('recurringandsubscription/license_status_group/serial_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		}

		$url = $this -> storeManager -> getStore() -> getBaseUrl();
		$parsedUrl = parse_url($url);
		$host = explode('.', $parsedUrl['host']);
		$subdomains = array_slice($host, 0, count($host) - 2);
		if (sizeof($subdomains) && ($subdomains[0] == 'test' || $subdomains[0] == 'demo' || $subdomains[0] == 'dev')) {
			return true;
		}
		$original = $this -> checkEntry($domain, $temp);
		$wildcard = $this -> checkEntry($this -> getDomain(), $temp);
		if (!$original && !$wildcard) {
			return false;
		}
		return true;
	}
	public function getMessage() {
		return base64_decode('PGRpdiBzdHlsZT0iYm9yZGVyOjNweCBzb2xpZCAjRkYwMDAwOyBtYXJnaW46MTVweCAwOyBwYWRkaW5nOjVweDsiPkxpY2Vuc2Ugb2YgPGI+UmVjdXJyaW5nIGFuZCBTdWJzY3JpcHRpb24gUGF5bWVudHM8L2I+IGV4dGVuc2lvbiBoYXMgYmVlbiB2aW9sYXRlZC4gVG8gZ2V0IHNlcmlhbCBrZXkgcGxlYXNlIGNvbnRhY3QgdXMgb24gPGI+aHR0cHM6Ly93d3cubWlsb3BsZS5jb20vbWFnZW50by1leHRlbnNpb25zL2NvbnRhY3RzLzwvYj48L2Rpdj4=');
	}
	public function getAdminMessage() {
		return base64_decode('PGRpdj5MaWNlbnNlIG9mIDxiPk1pbG9wbGUgUmVjdXJyaW5nIGFuZCBTdWJzY3JpcHRpb24gUGF5bWVudHM8L2I+IGV4dGVuc2lvbiBoYXMgYmVlbiB2aW9sYXRlZC4gVG8gZ2V0IHNlcmlhbCBrZXkgcGxlYXNlIGNvbnRhY3QgdXMgb24gPGI+aHR0cHM6Ly93d3cubWlsb3BsZS5jb20vbWFnZW50by1leHRlbnNpb25zL2NvbnRhY3RzLzwvYj48L2Rpdj4=');
	}
	public function getUnserializeData($data)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $version = $objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
        if($version >= '2.2.0'){
            $returnData = $objectManager->get('Magento\Framework\Serialize\Serializer\Json')->unserialize($data);
        }
        else{

            $returnData = (array) unserialize($data);
        }

        return $returnData;     
    }
    public function getSerializeData($data)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $version = $objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
        if($version >= '2.2.0'){
            $returnData = $objectManager->get('Magento\Framework\Serialize\Serializer\Json')->serialize($data);
        }
        else{

            $returnData = serialize($data);
        }
        return $returnData;     
    }
}
