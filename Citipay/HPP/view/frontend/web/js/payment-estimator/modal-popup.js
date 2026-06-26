define([
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function($, modal) {
        "use strict";

        let options = {
            type: 'popup',
            responsive: true,
            modalClass: 'citypay-payment-estimator-modal',
            buttons: []
        };

        function removeCitipayModalCloseButton(modalWrapper) {
            const peModalParent = $(modalWrapper);
            if (peModalParent.length) {
                const closeBtn = peModalParent.find('.close-modal-header');
                if (closeBtn.length) {
                    closeBtn[0].remove();
                }
            }
        }

        return function () {

            if ($('#payment-estimator-modal').length) {
                modal(options, $('#payment-estimator-modal'));
                $('.payment-estimator .see-details-link').click(function () {
                    removeCitipayModalCloseButton('#payment-estimator-modal');
                    $('#payment-estimator-modal').modal('openModal');
                });
                $(document).on('click', '[aria-label="Close"]', function(){
                    $('#payment-estimator-modal').modal('closeModal');
                });
            }
            else if ($('.minicart-payment-estimator-modal').length) {
                modal(options, $('.minicart-payment-estimator-modal'));
                $('.minicart-payment-estimator-content .see-details-link').on('click', function () {
                    removeCitipayModalCloseButton('.minicart-payment-estimator-modal');
                    $('.minicart-payment-estimator-modal').modal('openModal');
                });
                $(document).on('click', '[aria-label="Close"]', function(){
                    $('.minicart-payment-estimator-modal').modal('closeModal');
                });
            }
        }
    });
