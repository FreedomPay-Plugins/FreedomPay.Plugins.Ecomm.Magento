<?php

namespace Citipay\HPP\Model\PaymentEstimatorApi\Api;

use Magento\Framework\Exception\LocalizedException;
use Citipay\HPP\Model\PaymentEstimatorApi\Http\Request;
use Citipay\HPP\Model\PaymentEstimatorApi\Constants;

/**
 * Gets information for Payment Estimator API call from PDP
 */
class Product
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @param Request $request
     */
    public function __construct(
        Request $request
    ) {
        $this->request = $request;
    }

    /**
     * Get URI params for API Request for PDP
     *
     * @param mixed $price
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function doRequestForPDP(mixed $price = 0): array
    {
        $uriParams = [
            'is_checkout' => false,
            Constants::API_PARAM_SALE_AMOUNT => $price
        ];

        return $this->request->sendRequest(
            $uriParams
        );
    }
}
