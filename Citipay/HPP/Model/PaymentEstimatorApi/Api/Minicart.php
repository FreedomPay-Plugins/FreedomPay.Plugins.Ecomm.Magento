<?php

namespace Citipay\HPP\Model\PaymentEstimatorApi\Api;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Citipay\HPP\Model\PaymentEstimatorApi\Http\Request;
use Citipay\HPP\Model\PaymentEstimatorApi\Constants;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Gets information for Payment Estimator API call from Minicart
 */
class Minicart
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @param Request $request
     * @param Session $checkoutSession
     */
    public function __construct(
        Request $request,
        Session $checkoutSession
    ) {
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get URI params for API Request for Minicart
     *
     * @param bool $isCheckout
     * @return array<mixed>
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function doRequestForMinicart(bool $isCheckout = false): array
    {
        $quote = $this->checkoutSession->getQuote();
        $total = $quote->getGrandTotal();
        $uriParams = [
            'is_checkout' => $isCheckout,
            Constants::API_PARAM_SALE_AMOUNT => $total
        ];

        return $this->request->sendRequest(
            $uriParams
        );
    }
}
