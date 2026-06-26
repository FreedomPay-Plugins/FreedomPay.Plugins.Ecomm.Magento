define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'Citipay_HPP/js/payment-estimator/rate-calculator-abstract'
    ],
    function (
        $,
        ko,
        Component,
        customerData,
        rateCalculatorAbstract
    ) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Citipay_HPP/minicart-payment-estimator'
        },

        initialize: function () {
            this._super();
            let cartData = customerData.get('cart');
            cartData.subscribe(function (updatedCart) {
                this.initPaymentEstimatorMiniCart();
            }, this);
            this.initPaymentEstimatorMiniCart();
        },

        initPaymentEstimatorMiniCart: function() {
            let self = this;
            rateCalculatorAbstract.initPaymentEstimator(
                'citipayhpp/paymentestimator/minicart',
                {is_checkout: 0},
                self.getDynamicClasses()
            );
        },
        getDynamicClasses: function() {
            return {
                calculationHtml: '.minicart-payment-estimator-calculation-html',
                paymentEstimatorDisclosureText: '.minicart-payment-estimator-disclosure-text',
                paymentEstimatorContent: '.minicart-payment-estimator-content',
                paymentEstimatorLogo: '.minicart-payment-estimator-logo',
                citipayimgWrapper: '.minicart-citipayimg-wrapper',
                paymentEstimatorLogoText: '.minicart-payment-estimator-logo-text',
                paymentEstimatorContentLink: '.minicart-payment-estimator-content .see-details-link',
            };
        }
    });
});
