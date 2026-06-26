/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Citipay_HPP/js/payment-estimator/rate-calculator-abstract'
    ],
    function (
        $,
        ko,
        Component,
        quote,
        rateCalculatorAbstract
    ) {
        'use strict';

        return Component.extend({

            initialize: function () {
                this._super();
                this.initPaymentEstimatorCart(quote.totals().grand_total);
            },

            initObservable: function () {
                let self=this;
                let prevQuoteTotals = null;
                quote.totals.subscribe(function (newTotals) {
                    if (!prevQuoteTotals || prevQuoteTotals.grand_total !== newTotals.grand_total) {
                        prevQuoteTotals = newTotals;
                        if (newTotals) {
                            self.initPaymentEstimatorCart(newTotals.grand_total);
                        }
                    }
                });
                return this;
            },

            initPaymentEstimatorCart : function (total) {
                rateCalculatorAbstract.initPaymentEstimator(
                    'citipayhpp/paymentestimator/checkout',
                    {is_checkout: 0, sale_amount: total}
                );
            }
        });
    }
);
