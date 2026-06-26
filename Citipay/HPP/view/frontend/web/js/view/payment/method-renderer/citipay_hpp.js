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
        'mage/translate',
        'Citipay_HPP/js/payment-estimator/rate-calculator-abstract',
        'require'
    ],
    function (
        $,
        ko,
        Component,
        fullScreenLoader,
        customer,
        quote,
        urlBuilder,
        $t,
        rateCalculatorAbstract,
        require
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Citipay_HPP/payment/form',
                orderId: null,
                incrementId: null,
                createTransactionId: null,
                token: null,
                customerBillingAddress: null
            },

            initialize: function () {
                this._super();
                this.isPaymentEstimatorContentVisible(false);
                if (this.isPaymentEstimatorActive()) {
                    this.isPaymentEstimatorContentVisible(true);
                    this.initPaymentEstimatorCheckout();
                }
                return this;
            },

            selectPaymentMethod: function () {
                $('.message.error').hide();
                this.hasError(false);

                return this._super();
            },

            initObservable: function () {
                let self=this;
                this._super()
                    .observe({
                        hasError: ko.observable(false),
                        isVisible: ko.observable(false),
                        isPaymentEstimatorContentVisible:
                            ko.observable(self.isPaymentEstimatorActive())
                    });
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
                let prevQuoteTotals = null;
                if (self.isPaymentEstimatorActive()) {
                    quote.totals.subscribe(function (newTotals) {
                        if (!prevQuoteTotals || prevQuoteTotals.grand_total !== newTotals.grand_total) {
                            prevQuoteTotals = newTotals;
                            if (newTotals) {
                                self.initPaymentEstimatorCheckout();
                            }
                        }
                    });
                }
                return this;
            },

            getCode: function() {
                return window.checkoutConfig.payment.citipay_hpp.methodCode;
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
                return $t('Save Card');
            },

            getCustomerCards: function () {
                return [];
            },

            initPayment: function() {
                let self = this;
                let endpoint = window.checkoutConfig.payment.citipay_hpp.config.initPaymentUrl;
                let formKeyVal = $('input[name="form_key"]').val();
                //If quote is virtual, billing address will be empty in order object.
                //Hence, get billing address from quote object
                let billingAddress = quote.isVirtual() ? JSON.stringify(self.customerBillingAddress) : '';
                $('body').trigger('processStart');
                $.ajax({
                    url: urlBuilder.build(endpoint),
                    type: 'POST',
                    data: {
                        form_key: formKeyVal,
                        billing_address: billingAddress
                    },
                    dataType: 'json'
                }).done(async function (response) {
                    self.hasError(false);
                    if (response){
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
                        } else if(response.ResponseMessage) {
                            self.hasError(true);
                            $('.message.error').html('<div>'
                                + $t('Something went wrong while processing the request.') + '</div>');
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

            isPaymentEstimatorActive: function () {
                return window.checkoutConfig.payment.citipay_hpp.config.paymentEstimatorEnabled;
            },

            getLogoSrc: function () {
                let citipayType = window.checkoutConfig.payment.citipay_hpp.config.citipayType;
                if (citipayType == 10) {
                    return require.toUrl('Citipay_HPP/images/citipay_mil.svg');
                }
                return require.toUrl('Citipay_HPP/images/citipay_dloc.svg');
            },

            /**
             * Init payment estimator API call for checkout page
             */
            initPaymentEstimatorCheckout: function() {
                rateCalculatorAbstract.initPaymentEstimator(
                    'citipayhpp/paymentestimator/checkout',
                    {is_checkout: 1}
                );
            },

            /**
             * Place order in Magento before citi pay transaction
             */
            customPlaceOrder:async function () {
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
            }
        });
    }
);
