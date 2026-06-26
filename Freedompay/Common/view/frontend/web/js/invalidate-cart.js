define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url'
], function ($, customerData, urlBuilder){
    'use strict';
    $.widget('mage.invalidateCart', {
        _init: function () {
            var sections = ['cart'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);
            customerData.invalidate(['cart']);
        }
    });
    return $.mage.invalidateCart;
});
