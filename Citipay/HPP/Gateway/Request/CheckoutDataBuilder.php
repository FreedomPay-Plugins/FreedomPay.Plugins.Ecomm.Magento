<?php

namespace Citipay\HPP\Gateway\Request;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Freedompay\Common\Model\Data\Formatter;
use Citipay\HPP\Gateway\Config\PaymentConfig;
use Freedompay\Common\Gateway\Request\CheckoutDataBuilder as CommonCheckoutDataBuilder;
use Citipay\HPP\Helper\Constants;

/**
 * Builds checkout data
 */
class CheckoutDataBuilder extends CommonCheckoutDataBuilder
{
    /**
     * @var PaymentConfig
     */
    private PaymentConfig $paymentConfig;

    /**
     * BaseRequestDataBuilder constructor.
     *
     * @param RequestHelper $requestHelper
     * @param Formatter $formatter
     * @param PaymentConfig $paymentConfig
     */
    public function __construct(
        RequestHelper $requestHelper,
        Formatter $formatter,
        PaymentConfig $paymentConfig
    ) {
        $this->paymentConfig = $paymentConfig;
        parent::__construct($requestHelper, $formatter);
    }

    /**
     * Check payment action of citipay and if its MIL then payment action should be auth only
     *
     * @return bool
     */
    public function isCaptureMode(): bool
    {
        $citipayType = $this->paymentConfig->getValue(Constants::CITIPAY_TYPE);
        // If citipay type is MIL, the payment action should be auth only
        if ($citipayType && $citipayType == Constants::CITIPAY_MIL_VALUE) {
            return false;
        } else {
            return parent::isCaptureMode();
        }
    }
}
