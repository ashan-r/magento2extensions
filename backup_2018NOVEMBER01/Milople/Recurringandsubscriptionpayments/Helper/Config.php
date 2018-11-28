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
class Config extends \Magento\Framework\App\Helper\AbstractHelper 
{
    
	const XML_PATH_LICENSE_KEY 				= 'recurringandsubscription/license_status_group/serial_key';
	const XML_PATH_MODULE_STATUS 				= 'recurringandsubscription/license_status_group/status';
	const XML_PATH_GENERAL_ANONYMOUS_SUBSCRIPTIONS 		= 'recurringandsubscription/general_group/availableto';
	const XML_PATH_BRAND_LABEL				= 'recurringandsubscription/general_group/recurring_brand_label';
	const XML_PATH_CUSTOMER_GROUP 				= 'recurringandsubscription/general_group/customergroup';
	const XML_PATH_ACTIVE_ORDER_STATUS 			= 'recurringandsubscription/general_group/activate_order_status';
	const XML_PATH_DISPLAY_TYPE 				= 'recurringandsubscription/general_group/displayrnr';	
	const XML_PATH_APPLY_DISCOUNT				= 'recurringandsubscription/discount_group/apply_discount_settings';
	const XML_PATH_DISCOUNT_AMOUNT				= 'recurringandsubscription/discount_group/amount';
	const XML_PATH_DISCOUNT_CAL_TYPE				= 'recurringandsubscription/discount_group/cal_type';
	const XML_PATH_DISCOUNT_AVAILABLE_TO	 = 'recurringandsubscription/discount_group/discount_available_to';
  const XML_PATH_SEND_ORDER_CONFORMATION_EMAIL 		= 'recurringandsubscription/recurring_and_subscription_payments_order_confirmation_email/send_order_confirmation_email';
	const XML_PATH_SEND_ORDER_CONFORMATION_EMAIL_SENDER 	= 'recurringandsubscription/recurring_and_subscription_payments_order_confirmation_email/sender';
	const XML_PATH_SEND_ORDER_CONFORMATION_EMAIL_TEMPLATE 	= 'recurringandsubscription/recurring_and_subscription_payments_order_confirmation_email/template';
	const XML_PATH_SEND_ORDER_CONFORMATION_EMAIL_CC_TO 	= 'recurringandsubscription/recurring_and_subscription_payments_order_confirmation_email/cc_to';
	const XML_PATH_NEXT_PAYMNET_REMINDER 			= 'recurringandsubscription/next_payments_reminder_email/send_next_payments_reminder_email';
	const XML_PATH_NEXT_PAYMNET_REMINDER_SENDER 		= 'recurringandsubscription/next_payments_reminder_email/sender';
	const XML_PATH_NEXT_PAYMNET_REMINDER_TEMPLATE 		= 'recurringandsubscription/next_payments_reminder_email/template';
	const XML_PATH_NEXT_PAYMNET_REMINDER_CC_TO 		= 'recurringandsubscription/next_payments_reminder_email/cc_to';
	const XML_PATH_NEXT_PAYMNET_REMINDER_BEFORE_DAYS	= 'recurringandsubscription/next_payments_reminder_email/reminder_before_next_payments';
	const XML_PATH_NEXT_PAYMNET_CONFORMATION 		= 'recurringandsubscription/next_payments_confirmation_email/send_next_payments_confirmation_email';
	const XML_PATH_NEXT_PAYMNET_CONFORMATION_SENDER 	= 'recurringandsubscription/next_payments_confirmation_email/sender';
	const XML_PATH_NEXT_PAYMNET_CONFORMATION_TEMPLATE 	= 'recurringandsubscription/next_payments_confirmation_email/template';
	const XML_PATH_NEXT_PAYMNET_CONFORMATION_CC_TO 		= 'recurringandsubscription/next_payments_confirmation_email/cc_to';
	const XML_PATH_ORDER_STATUS_ACTIVE_TEMPLATE 		= 'recurringandsubscription/subscription_status_change_email/active_status_email_template' ;
	const XML_PATH_ORDER_STATUS_SUSPEND_TEMPLATE 		= 'recurringandsubscription/subscription_status_change_email/suspend_status_email_template';
	const XML_PATH_ORDER_STATUS_CANCLE_TEMPLATE 		= 'recurringandsubscription/subscription_status_change_email/cancel_status_email_template';
	const XML_PATH_ORDER_STATUS_EXPIRE_TEMPLATE 		= 'recurringandsubscription/subscription_status_change_email/expire_status_email_template';
	const XML_PATH_ORDER_STATUS_CHANGE_CC_TO		= 'recurringandsubscription/subscription_status_change_email/cc_to' ; 
	const XML_PATH_ORDER_STATUS_CHANGE_SENDER		= 'recurringandsubscription/subscription_status_change_email/sender' ;
	const XML_PATH_EXPIRY_REMINDER_EMAIL 			= 'recurringandsubscription/expire_reminder_email/send_expire_reminder_email';
	const XML_PATH_EXPIRY_REMINDER_EMAIL_SENDER 		= 'recurringandsubscription/expire_reminder_email/sender';
	const XML_PATH_EXPIRY_REMINDER_EMAIL_TEMPLATE 		= 'recurringandsubscription/expire_reminder_email/template';
	const XML_PATH_EXPIRY_REMINDER_EMAIL_CC_TO 		= 'recurringandsubscription/expire_reminder_email/cc_to';
	const XML_PATH_EXPIRY_REMINDER_BEFORE_DAYS		= 'recurringandsubscription/expire_reminder_email/reminder_before_expire';
	

}
