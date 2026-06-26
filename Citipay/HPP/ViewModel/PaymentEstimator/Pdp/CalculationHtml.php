<?php
/**
 * @package Citipay_HPP
 */
declare(strict_types=1);

namespace Citipay\HPP\ViewModel\PaymentEstimator\Pdp;

use Citipay\HPP\Gateway\Config\PaymentConfig;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;

class CalculationHtml implements ArgumentInterface
{
    /**
     * @var PaymentConfig
     */
    public PaymentConfig $config;

    /**
     * @var RequestInterface
     */
    public RequestInterface $request;

    /**
     * @param PaymentConfig $config
     * @param RequestInterface $request
     */
    public function __construct(
        PaymentConfig $config,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Check if payment estimator is enabled
     *
     * @return bool
     */
    public function isEnabledPdp(): bool
    {
        return $this->config->isPaymentEstimatorEnabled() && $this->config->isPdpMessagingEnabled();
    }

    /**
     * Return productId from the request
     *
     * @return int
     */
    public function getProductId(): int
    {
        /** @phpstan-ignore-next-line */
        $currentPage = $this->request->getFullActionName();
        if ('catalog_product_view' === $currentPage) {
            $productId = $this->request->getParam('id');
        } else {
            $productId = $this->request->getParam('product_id');
        }
        return (int) $productId;
    }
}
