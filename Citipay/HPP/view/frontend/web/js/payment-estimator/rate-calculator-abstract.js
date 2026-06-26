/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'mage/url',
        'modalPopup',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate',
    ],
    function (
        $,
        ko,
        urlBuilder,
        fpModalPopupService,
        fullScreenLoader,
        $t
    ) {
        'use strict';

        return {
            /**
             * Init payment estimator API call via Ajax
             */
            initPaymentEstimator: function(urlVal, params, dynamicClasses = []) {
                let self = this;
                let formKeyVal = $('input[name="form_key"]').val();
                let endpoint = BASE_URL + urlVal;
                let reqParams = Object.assign(
                    {},
                    params,
                    {form_key: formKeyVal}
                );
                if (dynamicClasses.length == 0) {
                    dynamicClasses = self.getDynamicClasses();
                }
                let paymentEstimatorCalculationHtml =  dynamicClasses['calculationHtml'];
                let paymentEstimatorDisclosureText = dynamicClasses['paymentEstimatorDisclosureText'];
                let paymentEstimatorContent = dynamicClasses['paymentEstimatorContent'];
                let paymentEstimatorLogo = dynamicClasses['paymentEstimatorLogo'];
                let citipayimgWrapper = dynamicClasses['citipayimgWrapper'];
                let paymentEstimatorLogoText = dynamicClasses['paymentEstimatorLogoText'];
                let paymentEstimatorContentLink = dynamicClasses['paymentEstimatorContentLink'];
                $(paymentEstimatorContent).show();
                self.hidePaymentEstimatorContent(dynamicClasses);
                if (paymentEstimatorContent == '.minicart-payment-estimator-content') {
                    $('#minicart-content-wrapper').addClass('rc-loader-wrap');
                } else {
                    $(paymentEstimatorContent).addClass('rc-loader');
                }

                $.ajax({
                    url: urlBuilder.build(endpoint),
                    type: 'POST',
                    data: reqParams,
                    dataType: 'json',
                    timeout: 30000
                }).done(function (response) {
                    if (response){
                        if(response.calculation_html) {
                            $(paymentEstimatorCalculationHtml).html(response.calculation_html);
                            if(response.disclosure_html) {
                               $(paymentEstimatorDisclosureText).html(response.disclosure_html);
                                $(paymentEstimatorContentLink).show();
                            }
                            if(response.citi_logo_url) {
                                $(paymentEstimatorLogo).attr('src', response.citi_logo_url);
                                $(paymentEstimatorLogo).show();
                                $(paymentEstimatorLogoText).show();
                                $(citipayimgWrapper).show();
                            }
                            if(response.product_name) {
                                $(paymentEstimatorLogoText).html(response.title_name);
                                if (response.product_name == 'Installment Loan') {
                                    if (response.status == '200' &&
                                        $('.payment-estimator-checkout .see-details-link').length) {
                                        $('.see-details-link').html($t('See Offer Details'));
                                    } else {
                                        $('.see-details-link').html($t('See Details'));
                                    }
                                }
                                if(response.product_name == 'Citi Pay Credit') {
                                    $('.see-details-link').html($t('Learn More'));
                                }
                                $(paymentEstimatorCalculationHtml).show();
                            }
                        } else {
                            self.removeLoaderClass(paymentEstimatorContent);
                            $(paymentEstimatorContent).hide();
                            if ($('.payment-estimator-checkout').length){
                                $('.payment-estimator-checkout').hide();
                            }
                        }
                    }
                    fpModalPopupService();
                    $('body').trigger('processStop');
                    fullScreenLoader.stopLoader();
                    self.removeLoaderClass(paymentEstimatorContent);
                }).fail(function (response) {
                    $('body').trigger('processStop');
                    self.removeLoaderClass(paymentEstimatorContent);
                });
            },
            getDynamicClasses: function() {
                return {
                    calculationHtml: '.payment-estimator-calculation-html',
                    paymentEstimatorDisclosureText: '.payment-estimator-disclosure-text',
                    paymentEstimatorContent: '.payment-estimator-content',
                    paymentEstimatorLogo: '.payment-estimator-logo',
                    citipayimgWrapper: '.citipayimg-wrapper',
                    paymentEstimatorLogoText: '.payment-estimator-logo-text',
                    paymentEstimatorContentLink: '.payment-estimator-content .see-details-link',
                };
            },
            removeLoaderClass: function(paymentEstimatorContent) {
                if (paymentEstimatorContent == '.minicart-payment-estimator-content') {
                    $('#minicart-content-wrapper').removeClass('rc-loader-wrap');
                } else {
                    if ($('.payment-estimator-checkout').length){
                        $('.payment-estimator-checkout').removeClass('rc-loader');
                        $(paymentEstimatorContent).removeClass('rc-loader');
                    } else {
                        $(paymentEstimatorContent).removeClass('rc-loader');
                    }
                }
            },
            hidePaymentEstimatorContent: function(dynamicClasses) {
                $(dynamicClasses['calculationHtml']).hide();
                $(dynamicClasses['paymentEstimatorLogo']).hide();
                $(dynamicClasses['paymentEstimatorLogoText']).hide();
                $(dynamicClasses['paymentEstimatorContentLink']).hide();
            }
        };
    }
);
