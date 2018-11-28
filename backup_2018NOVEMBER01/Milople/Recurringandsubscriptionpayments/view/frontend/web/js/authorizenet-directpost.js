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
***/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/iframe',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function (
        $,
        Component,
        fullScreenLoader,
        setPaymentInformationAction,
        additionalValidators,
        messageList,
        redirectOnSuccessAction
    ) {
        'use strict';
        var receiveUrl = BASE_URL.concat('recurringandsubscriptionpayments/authorize/applyAutoCapture');
        //alert(receiveUrl);
					var isPartialOrder;		
        $.ajax({
            type: "POST",
            url: receiveUrl,
            success: function (result) {
								//alert('inside sucess');
                isPartialOrder = result;
								//alert(isPartialOrder);
            },
            async: false
        });
        return Component.extend({
            defaults: {
                template: 'Magento_Authorizenet/payment/authorizenet-directpost',
                timeoutMessage: 'Sorry, but something went wrong. Please contact the seller.'
            },
            placeOrderHandler: null,
            validateHandler: null,
            isSuccess: true,
            customerProfileId: '',
            paymentProfileId: '',

            /**
             * @param {Object} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Object} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @returns {Object}
             */
            context: function () {
                return this;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return 'authorizenet_directpost';
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            },

            getTimeoutTime: function () {
                return 99999;
            },

            /**
             * Override root component's place order
             */
            placeOrder: function () {
                var self = this;

                if (this.validateHandler() && additionalValidators.validate()) {

                    fullScreenLoader.startLoader();

                    this.isPlaceOrderActionAllowed(false);

                    if (isPartialOrder) {
						if($('#authorizenet_directpost_cc_cid').length){
							var result = {
								"ccNumber": $('#authorizenet_directpost_cc_number').val(),
								"expMonth": $('#authorizenet_directpost_expiration').val(),
								"expYear": $('#authorizenet_directpost_expiration_yr').val(),
								"ccId": $('#authorizenet_directpost_cc_cid').val()
							};	
						}else{
							var result = {
								"ccNumber": $('#authorizenet_directpost_cc_number').val(),
								"expMonth": $('#authorizenet_directpost_expiration').val(),
								"expYear": $('#authorizenet_directpost_expiration_yr').val()
							};
						}                        
						console.log(result);						
                        var requestUrl = BASE_URL.concat('recurringandsubscriptionpayments/authorize/createProfile');
											$.ajax({
                            type: "POST",
                            url: requestUrl,
                            data: {
                                result: result
                            },
                            success: function (res) {
								console.log(res);
                                if (res.result === false) {
                                    fullScreenLoader.stopLoader();
                                    self.isPlaceOrderActionAllowed(true);
                                    self.isSuccess = false;
                                } else {
                                    self.customerProfileId = res.customerProfileId;
                                    self.paymentProfileId = res.paymentProfileId;
                                }
                            },
                            async: false
                        });
                    }

                    if (this.isSuccess) {
                        $.when(
                            setPaymentInformationAction(
                                this.messageContainer,
                                {
                                    method: this.getCode()
                                }
                            )
                        ).done(this.done.bind(this))
                            .fail(this.fail.bind(this));

                        this.initTimeoutHandler();
                    }
                }
            },
            done: function () {
                var self = this;
                //var updateRequestUrl = BASE_URL.concat('recurringandsubscriptionpayments/authorize/updateProfile');

                this.placeOrderHandler().fail(function () {
                    fullScreenLoader.stopLoader();
                });

                /*if (0) {
					console.log(self.customerProfileId);
					console.log(self.paymentProfileId);
                    $.when(
                        $.ajax({
                        type: "POST",
                        url: updateRequestUrl,
                        data: {
                            customerProfileId: self.customerProfileId,
                            paymentProfileId: self.paymentProfileId
                        },
						success: function (res) {
							setTimeout(function(){ redirectOnSuccessAction.execute(); }, 30000);		
						},
                        async: false
                    })).done(											
                        
                    );
                }*/

                return this;
            },

            fail: function () {
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);

                return this;
            },

            initTimeoutHandler: function () {
                this.timeoutId = setTimeout(
                    this.timeoutHandler.bind(this),
                    this.getTimeoutTime()
                );

                $(window).off('clearTimeout')
                    .on('clearTimeout', this.clearTimeout.bind(this));
            },

            clearTimeout: function () {
                clearTimeout(this.timeoutId);

                return this;
            },

            /**
             * {Function}
             */
            timeoutHandler: function () {
                this.clearTimeout();

                alert(
                    {
                        content: this.getTimeoutMessage(),
                        actions: {

                            /**
                             * {Function}
                             */
                            always: this.alertActionHandler.bind(this)
                        }
                    }
                );

                this.fail();
            },

            getTimeoutMessage: function () {
                return $t(this.timeoutMessage);
            }
        });
    }
);