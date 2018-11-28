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
namespace Milople\Recurringandsubscriptionpayments\Block\Html;
/**
 * Date element block
 */
class Date extends \Magento\Framework\View\Element\Html\Date
{
    /**
     * Render block HTML
     * @return string
     */
    protected function _toHtml()
    {
        $html = '<input type="text" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        $html .= 'value="' . $this->escapeHtml($this->getValue()) . '" ';
        $html .= 'class="' . $this->getClass() . '" ' . $this->getExtraParams() . '/> ';
        $calendarYearsRange = $this->getYearsRange();
        $html .= '<script type="text/javascript">
            require(["jquery", "mage/calendar"], function($){
                    $("#' .
            $this->getId() .
            '").calendar({
                        showsTime: ' .
            ($this->getTimeFormat() ? 'true' : 'false') .
            ',
                        ' .
            ($this->getTimeFormat() ? 'timeFormat: "' .
            $this->getTimeFormat() .
            '",' : '') .
            '
                        dateFormat: "' .
            $this->getDateFormat() .
            '",
                        buttonImage: "' .
            $this->getImage() .
            '",
                        ' .
            ($calendarYearsRange ? 'yearRange: "' .
            $calendarYearsRange .
            '",' : '') .
            '
                        buttonText: "' .
            (string)new \Magento\Framework\Phrase(
                'Select Date'
            ) .
            '", minDate: new Date(),
                    })
            });
            </script>';

        return $html;
    }

    /**
     * Convert special characters to HTML entities
     * @return string
     */
    public function getEscapedValue()
    {
        if ($this->getFormat() && $this->getValue()) {
            return strftime($this->getFormat(), strtotime($this->getValue()));
        }
        return htmlspecialchars($this->getValue());
    }
     /**
     * Produce and return block's html output
     * {@inheritdoc}
     * @return string
     */
    public function getHtml()
    {
        return $this->toHtml();
    }
}
