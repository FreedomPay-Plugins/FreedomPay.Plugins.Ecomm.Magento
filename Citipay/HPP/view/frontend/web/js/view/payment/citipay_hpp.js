/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'citipay_hpp',
                component: 'Citipay_HPP/js/view/payment/method-renderer/citipay_hpp'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
