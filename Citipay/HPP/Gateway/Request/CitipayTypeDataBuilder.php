<?php

namespace Citipay\HPP\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Citipay\HPP\Gateway\Config\PaymentConfig;
use Freedompay\Common\Gateway\Request\AdditionalPaymentMethodsDataBuilder;

/**
 * Builds magento transaction request - webhook
 */
class CitipayTypeDataBuilder implements BuilderInterface
{
    private const KEY_CITI_PAY_TYPE = 'citipay_type';

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * AdditionalPaymentMethodsDataBuilder constructor.
     *
     * @param PaymentConfig $config
     */
    public function __construct(
        PaymentConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * Builds AdditionalPaymentMethods data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        return [
            AdditionalPaymentMethodsDataBuilder::REQUEST_PARAM_ADDITIONAL_PAYMENT_METHODS =>
                $this->config->getValue(self::KEY_CITI_PAY_TYPE)
        ];
    }
}
