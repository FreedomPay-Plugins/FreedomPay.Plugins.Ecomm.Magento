<?php
/**
 * @package Citipay_HPP
 */
declare(strict_types=1);

namespace Citipay\HPP\ViewModel\PaymentEstimator\Cart;

use Citipay\HPP\Gateway\Config\PaymentConfig;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CalculationHtml implements ArgumentInterface
{
    /**
     * @var PaymentConfig
     */
    public PaymentConfig $config;

    /**
     * @param PaymentConfig $config
     */
    public function __construct(
        PaymentConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * Check if payment estimator is enabled
     *
     * @return bool
     */
    public function isEnabledCart(): bool
    {
        return $this->config->isPaymentEstimatorEnabled();
    }
}
