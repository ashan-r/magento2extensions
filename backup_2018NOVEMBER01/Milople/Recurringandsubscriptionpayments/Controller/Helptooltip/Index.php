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
namespace Milople\Recurringandsubscriptionpayments\Controller\Helptooltip;
class Index extends \Magento\Framework\App\Action\Action 
{
	protected $resultPageFactory;
	protected $productModel;
  protected $partialHelper;
	protected $dataHelper;
	protected $calculationModel;
	protected $currency;
	protected $storeManager;
	protected $partialBlock;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\App\Request\Http $request,
		\Milople\Recurringandsubscriptionpayments\Model\TermsFactory $terms,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Milople\Recurringandsubscriptionpayments\Helper\Recurringandsubscriptionpayments $data_helper
	)
    {
    $this->resultPageFactory = $resultPageFactory;
		$this->_storeManager = $storeManager;
		$this->_logger = $logger;
		$this->request = $request;
		$this->terms=$terms;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->helper = $data_helper;
    parent::__construct($context);
    }
   /**
   * return Helptooltip HTML
   */
	public function execute(){
	  $data = $this->request->getPost();
	  $html=$this->helper->getHelpHtml($data['termid'],$data['productPrice'],$data['productType'],$data['symbol']);
	  $result = $this->resultJsonFactory->create();
	  $result->setData(['html' => $html]);	
	  return $result; 
	}
}
