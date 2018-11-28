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
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;
/**
 * Product status functionality model
 */
class Subscriptionstatus extends AbstractSource implements SourceInterface, OptionSourceInterface
{
     /**
     * Retrieve option array
     * @return string[]
     */
    public static function getOptionArray()
    {
        return ['1' => __('Active'), '2' => __('Suspended'),'3' => __('Suspended by Customer'),'-1' => __('Cancelled'),'0' => __('Expired')];
    }

    /**
     * Retrieve option array with empty value
    * @return string[]
     */
   public function getAllOptions()
   {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
   }
	# Get label of status
	public function getLabel($status)
	{
	   $options = $this->getAllOptions();
       foreach ($options as $v) {
            if ($v['value'] == $status) {
                return $v['label'];
            }
        }
        return '';
	}
}
