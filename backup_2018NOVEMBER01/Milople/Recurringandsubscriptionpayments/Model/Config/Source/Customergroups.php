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
namespace Milople\Recurringandsubscriptionpayments\Model\Config\Source;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
class Customergroups implements \Magento\Framework\Option\ArrayInterface
{
	protected $groupRepository;
	protected $_objectConverter;
	protected $_searchCriteriaBuilder;
	
    public function __construct(	
			GroupRepositoryInterface $groupRepository,
			ObjectConverter $objectConverter,
			\Psr\Log\LoggerInterface $logger,
			SearchCriteriaBuilder $searchCriteriaBuilder
		){
			$this->groupRepository = $groupRepository;
			$this->_objectConverter = $objectConverter;
			$this->_searchCriteriaBuilder = $searchCriteriaBuilder;
			$this->_logger = $logger;
		}
    public function toOptionArray()
    {
			$groups = $this->groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
			return $this->_objectConverter->toOptionArray($groups, 'id', 'code');	
    }
}
