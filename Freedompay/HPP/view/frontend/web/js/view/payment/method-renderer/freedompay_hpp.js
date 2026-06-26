/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'mage/url',
        'mage/translate'
    ],
    function (
        $,
        ko,
        Component,
        fullScreenLoader,
        customer,
        quote,
        urlBuilder,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Freedompay_HPP/payment/form',
                orderId: null,
                incrementId: null,
                createTransactionId: null,
                token: null,
                customerBillingAddress: null
            },

            selectPaymentMethod: function () {
                $('.message.error').hide();
                this.hasError(false);
                this.isVisible(false);
                if (window.checkoutConfig.payment.freedompay_hpp.config.request_token) {
                    this.isVisible(true);
                    this.toggleCheckbox();
                }
                return this._super();
            },

            initObservable: function () {
                this._super()
                    .observe({
                        hasError: ko.observable(false),
                        isVisible: ko.observable(window.checkoutConfig.payment.freedompay_hpp.config.request_token)
                    });
                let self=this;
                let prevAddress;
                quote.billingAddress.subscribe(
                    function(newAddress) {
                        if (!newAddress ^ !prevAddress || newAddress.getKey() !== prevAddress.getKey()) {
                            prevAddress = newAddress;
                            if (newAddress) {
                                self.customerBillingAddress = newAddress;
                            }
                        }
                    }
                );
                return this;
            },

            getCode: function() {
                return window.checkoutConfig.payment.freedompay_hpp.methodCode;
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'createTransactionId': this.createTransactionId
                    }
                };
            },

            isLoggedIn: function () {
                return customer.isLoggedIn();
            },

            getSavedCardSelectElementId: function() {
                return 'customer_cards_' + this.getCode();
            },

            getSaveCardDivElementId: function() {
                return 'checkbox_area_' + this.getCode();
            },

            getSaveCardCheckboxElementId: function() {
                return 'checkbox' + this.getCode();
            },

            getSavedCardCheckboxLabel: function() {
                return $t('Save Credit/Debit Card');
            },

            getCustomerCards: function () {
                return window.checkoutConfig.payment.freedompay_hpp.savedCards;
            },

            toggleCheckbox: function() {
                let saveCardSelectElement = $('#' + this.getSavedCardSelectElementId());
                if (saveCardSelectElement.length){
                    let saveCardCheckbox = $('#' + this.getSaveCardDivElementId());
                    if (saveCardSelectElement.val()) {
                        saveCardCheckbox.hide();
                    } else {
                        if (window.checkoutConfig.payment.freedompay_hpp.config.request_token) {
                            saveCardCheckbox.show();
                        }
                    }
                }
            },

            initPayment: function() {
                let self = this;
                let isSaveCardChecked = false;
                let tokenValue = '';
                let savedCardSelector = $('#'+this.getSavedCardSelectElementId());
                let checkboxSelector = $('#'+this.getSaveCardCheckboxElementId());
                let endpoint = window.checkoutConfig.payment.freedompay_hpp.config.initPaymentUrl;
                let formKeyVal = $('input[name="form_key"]').val();
                if (savedCardSelector.is(':visible')) {
                    tokenValue = savedCardSelector.val();
                }
                if (checkboxSelector.is(':visible')) {
                    isSaveCardChecked = checkboxSelector.is(':checked');
                }
                //If quote is virtual, billing address will be empty in order object.
                //Hence, get billing address from quote object
                let billingAddress = quote.isVirtual() ? JSON.stringify(self.customerBillingAddress) : '';
                $('body').trigger('processStart');
                $.ajax({
                    url: urlBuilder.build(endpoint),
                    type: 'POST',
                    data: {
                        form_key: formKeyVal,
                        request_token: isSaveCardChecked ? 1 : 0,
                        token_value: tokenValue,
                        billing_address: billingAddress
                    },
                    dataType: 'json'
                }).done(async function (response) {
                    self.hasError(false);
                    if (response){
                        let responseMessage = response.ResponseMessage;
                        if (response.CheckoutUrl) {
                            if (response.TransactionId) {
                                self.createTransactionId = response.TransactionId;
                            }
                            await self.customPlaceOrder();
                            $.ajax({
                                url: urlBuilder.build('freedompay/process/addmandatoryfieldvalidationcomment'),
                                type: 'POST'
                            }).done(function () {
                                window.location = response.CheckoutUrl;
                            });
                        } else if(responseMessage) {
                            self.hasError(true);
                            if(responseMessage == 'Postal code is not valid in ShipToAddress') {
                                $('.message.error').html('<div>'
                                    + $t('Shipping postal code is not a valid United States Postal Code, ' +
                                        'please change the postal code and try again.') + '</div>');
                            } else {
                                $('.message.error').html('<div>'
                                    + $t(responseMessage) + '</div>');
                            }
                        }
                    } else{
                        self.hasError(true);
                    }
                    $('body').trigger('processStop');
                    fullScreenLoader.stopLoader();
                }).fail(function (response) {
                    self.hasError(true);
                    $('body').trigger('processStop');
                    fullScreenLoader.stopLoader();
                });
            },

            /**
             * Place order in Magento before freedompay transaction
             */
            customPlaceOrder: async function () {
                let self = this;
                fullScreenLoader.startLoader();
                await this.getPlaceOrderDeferredObject()
                    .then((result) => {
                        self.orderId = result;
                        fullScreenLoader.stopLoader();
                        return !!result
                    })
                    .fail(function() {
                        fullScreenLoader.stopLoader();
                        return false;
                    });
            },

            getErrorMessage: function() {
                return $t('Something went wrong while processing the request.');
            },
            getLogos: function () {
                return window.checkoutConfig.payment.freedompay_hpp.config.logos;
            }
        });
    }
);
