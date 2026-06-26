define([
        'jquery',
        'Citipay_HPP/js/payment-estimator/rate-calculator-abstract',
        'uiComponent'
    ],
    function(
        $,
        rateCalculatorAbstract,
        Component
    ) {
        "use strict";

        return Component.extend({

            initialize: function (config) {
                this._super();
                this.initPaymentEstimatorPdp(config.productId);
            },

            initPaymentEstimatorPdp : function (productId) {
                rateCalculatorAbstract.initPaymentEstimator(
                    'citipayhpp/paymentestimator/productpage',
                    { 'productId' : productId}
                );
            }
        });
    });
