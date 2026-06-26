<?php
/**
 * @package Citipay_HPP
 */
declare(strict_types=1);

namespace Citipay\HPP\Model\PaymentEstimatorApi;

class ResponseManager
{

    /**
     * Get CalculationHtml content
     *
     * @param array<mixed> $apiResponse
     * @return string
     */
    public function getCalculationHtml(array $apiResponse):string
    {
        return $apiResponse['calculation_html'] ?? '';
    }

    /**
     * Get DisclosureHtml content
     *
     * @param array<mixed> $apiResponse
     * @return string
     */
    public function getDisclosureHtml(array $apiResponse):string
    {
        return $apiResponse['disclosure_html'] ?? '';
    }

    /**
     * Get Citipay logo URL
     *
     * @param array<mixed> $apiResponse
     * @return string
     */
    public function getLogoUrl(array $apiResponse):string
    {
        return $apiResponse['citi_logo_url'] ?? '';
    }
}
