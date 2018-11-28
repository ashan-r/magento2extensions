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
namespace Milople\Recurringandsubscriptionpayments\Controller\Adminhtml\Plans;
use Magento\Backend\App\Action;
 
class Save extends \Magento\Backend\App\Action
{
	 protected $planFactory;
	 public function __construct(
     Action\Context $context,
		 \Magento\Framework\Stdlib\DateTime\DateTime $date,
		 \Milople\Recurringandsubscriptionpayments\Model\ResourceModel\Plans\CollectionFactory $planFactory,
		 \Milople\Recurringandsubscriptionpayments\Model\Plans\ProductFactory $planProduct,
		 \Milople\Recurringandsubscriptionpayments\Model\PlansFactory $plans,
		 \Milople\Recurringandsubscriptionpayments\Model\TermsFactory $terms,
		 \Magento\Catalog\Model\Product $product,
		 \Psr\Log\LoggerInterface $logger
    ) {
		$this->planFactory = $planFactory;
		$this->plans=$plans;
		$this->product=$product;
		$this->terms=$terms;
		$this->planProduct=$planProduct;
		$this->plan_productFactory = $planProduct;
		$this->logger = $logger;
        parent::__construct($context);
    }
	 /**
	 * @return void
	 */
   public function execute()
   {
      $isPost = $this->getRequest()->getPost();
		
      if ($isPost) {
         $model = $this->plans->create();
         $plan_id = $this->getRequest()->getParam('plan_id');
 		     if ($plan_id) {
            $model->load($plan_id);
         }
         $formData = $this->getRequest()->getPostValue();
			    $model->setData($formData);
         try
				 {
					if(!isset($formData['plan']))
					{
						$this->messageManager->addError('Please add term for this plan.');
						return $this->_redirect('*/*/edit',
										['plan_id' => $this->getRequest()->getParam('plan_id'),
							 'active_tab' => 'term_section']
								);
					}
					else
					{
						$ableToDeleteTerm=true;
						foreach($formData['plan']['terms'] as $term)
						{
							if($term['delete']==null)
							{
								$ableToDeleteTerm=false;
								break;
							}
						}
						if($ableToDeleteTerm)
						{
							$this->messageManager->addError('Plan must have atleast one term.');
							return $this->_redirect('*/*/edit',
										['plan_id' => $this->getRequest()->getParam('plan_id'),
							 'active_tab' => 'term_section']
									);
						} 
						$col = $formData['plan']['terms'];
					}
					// For Duplication Plan's terms title 
					foreach($col as $term)
					{
					 $termArray[] = strtolower($term['label']);
					 //$termArray[] = $term['label'];
					}
					$termsMessage =array();
					foreach(array_count_values($termArray) as $key=>$term)
					{
					 if($term>1)
					 {       
						$termsMessage[] = $key;
					 }
					}
					if(!empty($termsMessage))
					{
						$this->messageManager->addError(
										__(implode(", ",$termsMessage) ." term title is already exists, please use a unique term title.")); 
						return $this->_redirect('*/*/edit',
										['plan_id' => $this->getRequest()->getParam('plan_id'),
							 'active_tab' => 'term_section']
								);
					}

					$existing_plan = 0 ;
					$plan_id = $model->getId();
					if(isset($plan_id) && $plan_id > 0 && $plan_id != '')
					{
						$existing_plan = 1 ;	
					} 

					$plan = $this->planFactory->create(); 
							$plan->addFieldToFilter('plan_name',$formData['plan_name']);
					$id = 0;
						foreach($plan as $p)
					{
						$id = $p->getPlanId();
					}
					if($id > 0  && $id != $this->getRequest()->getParam('plan_id')) 
					{
						$this->messageManager->addError(
										__($formData['plan_name']." Plan name already exists, please use a unique plan name."));  
						return $this->_redirect('*/*/edit',['plan_id' =>  $this->getRequest()->getParam('plan_id')]);
					}
					else
					{
						 $model->save(); 
					} 
					 $model->save(); 
					 if (isset($formData['products_area_type']) && $formData['products_area_type']>1 && isset($formData['products_area'])) {
												if ($formData['in_products']) $productIds = explode(',', $formData['products_area']); else $productIds = array();                    
										switch ($formData['products_area_type']) {
														case 2: // by product ids
																$productArea = explode(',', $formData['products_area']);
																foreach($productArea as $productId) {
																		$productId = intval($productId);
																		if ($productId && !in_array($productId, $productIds)) {
																				$product = $this->product->load($productId);
																				if ($product && $product->getId() > 0) $productIds[] = $productId;
																		}
																}
																$formData['in_products'] = implode(',', $productIds);
																break;
														case 3: // by SKUs     
																$productArea = explode(',', $formData['products_area_sku']);
																$fetchProduct =$this->product;
																foreach($productArea as $sku) {
																		$sku = trim($sku);
																		$productId = $fetchProduct->getIdBySku($sku);
																		//if ($productId && !in_array($productId, $productIds))
																		$productSku[] = $productId;
																}
																$formData['in_products'] = implode(',', $productSku);
																break;
												}
										}
									/* Here all selected product's id are saved in table with respective plan id */		
						$total_products = explode(",", ($formData['in_products']));
						$group_product_id = array();


						/* This will check if product is of group type 
							 Then ids of its associated products are also needs to add  */
						foreach ($total_products as $product_id)
						{
							$d = settype($product_id,"integer");
							$product = $this->product->load($d); 

							$productType = $product->getTypeId();	
							if ($productType == "grouped")
							{
								$group_product = $this->product; 
										$associated_products = $group_product->getTypeInstance(true)->getAssociatedProducts($group_product);
								$child_product_id = array();
								foreach ($associated_products as $child_product)
								{
									$child_product_id[] = $child_product->getId();
								}
								$total_products = array_merge($total_products,$child_product_id);
							}
						}
						$count = sizeof($total_products);
						$plan_id = $model->getId();

						if($formData['products_area_type'] == 2  || $formData['products_area_type']==3)
						{
							if($existing_plan == 1)   // already exist
							{
								$collection = $this->plan_productFactory->create()->getCollection(); 
										$collection->addFieldToFilter('plan_id',$plan_id)
									->addFieldToSelect('product_id')
									->getData(); // create an array of this plan's all product id

								$collectId = array();		
								foreach ($collection as $collect)
								{
									$collectId[] = $collect['product_id'];	
								}
									$array2 = array_diff($total_products,$collectId);	   // find which new products are already in this plan
									if(sizeof($array2))
									{
									$isAlreadyExist = $this->plan_productFactory->create()->getCollection()   // find new products in whole table
													->addFieldToFilter('product_id',array('in' => array($array2)));
										}
									else
									{
										$isAlreadyExist = 0;    // when change is not related to prodct bt related to plan name,disc amt,term dt..etc
									}
							}
							else   // Check for new add plan has already exist product or not
							{
								$isAlreadyExist = $this->plan_productFactory->create()->getCollection()
										 ->addFieldToFilter('product_id',array('in' => array($total_products)));
							}
						if(count($isAlreadyExist) == 0 || $isAlreadyExist == 0)
						{
							//save plan because all selected products are new
							for($i=0; $i<$count; $i++)
							{
								$productofPlans = ['plan_id' => $plan_id, 'product_id' => $total_products[$i]];
								$productPlanModel = $this->plan_productFactory->create()->setData($productofPlans);
								try {
									$productPlanModel->save();
								} catch (\Exception $e) {
									 $this->logger->addDebug("Exception". $e); 
								}
							}
						/* End : Here all selected product's id are saved in table with respective plan id */
							$this->messageManager->addSuccess(__('Plan was successfully saved.'));
							$this->_getSession()->setFormData(false);
						}
						else
						{
							// give error message	
							$this->messageManager->addError(__('You cannot add same product, which is already added in other plan.'));
						}
					}
					else
					{	
							//save plan because all selected products are new
							for($i=0; $i<$count; $i++)
							{
								$productPlanModel = $this->planProduct->create();
								$productPlanModel->setPlanId($plan_id);
								$productPlanModel->setProductId($total_products[$i]);
								try {
									$productPlanModel->save();
								} catch (\Exception $e) {
									$this->logger->addDebug("Exception". $e); 
								}
							}
						/* End : Here all selected product's id are saved in table with respective plan id */
							$this->messageManager->addSuccess(__('Plan was successfully saved'));
							$this->_getSession()->setFormData(false);
					}
					foreach($col as $c)
					{
								$model_terms = $this->terms->create();
								if(isset($c['delete']) && $c['delete'])
								{
									$model_terms->setId($c['id']);
									$model_terms->delete();
									$model_terms->save();
								}
								else
								{
									if(isset($c['id']) && $c['id']) 
									$model_terms->setId($c['id']);
									$paymentbeforedays=0;
									if(isset($c['paymentbeforedays']))
									$paymentbeforedays=$c['paymentbeforedays'];
									$model_terms->setLabel($c['label']);
									$model_terms->setRepeateach($c['repeat_each']);
									$model_terms->setTermsper($c['termsper']);
									$model_terms->setPaymentBeforeDays($paymentbeforedays);
									$model_terms->setPrice($c['price']);
									$model_terms->setPriceCalculationType($c['pricecalculationtype']);
									$model_terms->setNoofterms($c['noofterms']);
									$model_terms->setSortorder($c['sortorder']);
									$model_terms->setPlanId($model->getId());
									$model_terms->save();
								}
						}
						$this->deleteProduct($total_products,$plan_id);
						/* end */
						if ($this->getRequest()->getParam('back')) {
							$this->_redirect('*/*/edit', array('_current' => true,'plan_id' => $model->getId()));
							return;
						}
					 // Go to grid page
						$this->_redirect('*/*/');
						return;
         } catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
                $this->_getSession()->setFormData($formData);
                $this->_redirect('*/*/edit', array('_current' => true,'id' => $this->getRequest()->getParam('id')));
                return;
		   	}
      }
	    $this->messageManager->addError(__('Unable to find Plan to save'));
        $this->_redirect('*/*/');
   }
   public function deleteProduct($selected_products,$plan_id)
	 {
			$collection = $this->planProduct->create()
								->getCollection()
							->addFieldToFilter('plan_id',$plan_id)
							->addFieldToSelect('product_id')
							->getData();                     // fetch product id of plan_id
			$collectId = array();		
			foreach ($collection as $collect)
			{
				$collectId[] = $collect['product_id'];	//$collect->getProductId();//
			}

			$idsToDelete = array_diff($collectId,$selected_products);	   // find which new products are already in this plan

			if(sizeof($idsToDelete))
			{
				$items = $this->planProduct->create()->getCollection()   // find new products in whole table
								->addFieldToFilter('product_id',array('in' => array($idsToDelete)));
				foreach($items as $item)
				{
					$item->delete();
				}

			}
	}
}
