<?php

namespace Freedompay\HPP\ViewModel\Customer;

use Freedompay\HPP\Gateway\Config\PaymentConfig;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;

/**
 * ViewModel for Freedompay add new card button
 */
class AddNewCard implements ArgumentInterface
{
    /**
     * @var PaymentConfig
     */
    private PaymentConfig $paymentConfig;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @param PaymentConfig $paymentConfig
     * @param UrlInterface $url
     */
    public function __construct(
        PaymentConfig $paymentConfig,
        UrlInterface $url
    ) {
        $this->paymentConfig = $paymentConfig;
        $this->url = $url;
    }

    /**
     * Checks the payment is enabled
     *
     * @return bool
     */
    public function getPaymentStatus(): bool
    {
        if ($this->paymentConfig->isActive() && $this->paymentConfig->isEnabled('request_token')) {
            return true;
        }
        return false;
    }

    /**
     * Redirects to verification transaction controller
     *
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->url->getUrl('freedompayhpp/process/createverificationtransaction');
    }
}
