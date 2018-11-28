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
namespace Milople\Recurringandsubscriptionpayments\Controller\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Cart as CustomerCart;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Escaper $escaper,
         CustomerCart $cart,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );
        $this->resolver=$resolver;
        $this->product=$product;
        $this->logger=$logger;
        $this->cartHelper=$cartHelper;
        $this->escaper=$escaper;
      }
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->resolver->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }
			if(isset($params['super_group']) && !empty($params['super_group']) )
			{
				$data  = $params['super_group'];
				foreach($data as $key=>$d)
				{
					$opt = array(                
								'qty' => $d,								
								'milople_subscription_type' =>$params['milople_subscription_type'],
								'milople_subscription_start_date' =>$params['milople_subscription_start_date'],
								);	
								
					$request = new \Magento\Framework\DataObject();
					$request->setData($opt);
			     	$product = $this->product->load($key);			
					$this->cart->addProduct($product, $request);			
					$this->cart->save();

				}
					
			}
			else
			{
            	$this->cart->addProduct($product, $params);
			}
					
           // $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $product->getName()
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
                return $this->goBack(null, $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->escaper->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                       $this->escaper->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $cartUrl = $this->cartHelper->getCartUrl();
                $url = $this->_redirect->getRedirectUrl($cartUrl);
            }

            return $this->goBack($url);

        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->logger->critical($e);
            return $this->goBack();
        }
	}
}
