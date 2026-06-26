<?php
namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Gateway\Config\PaymentConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Builds AdditionalPaymentMethods data
 */
class AdditionalPaymentMethodsDataBuilder implements BuilderInterface
{
    private const KEY_ADDITIONAL_PAYMENT_METHODS = 'additional_payment_methods';
    public const REQUEST_PARAM_ADDITIONAL_PAYMENT_METHODS = 'AdditionalPaymentMethods';

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
            self::REQUEST_PARAM_ADDITIONAL_PAYMENT_METHODS =>
                $this->config->getValue(self::KEY_ADDITIONAL_PAYMENT_METHODS)
        ];
    }
}
