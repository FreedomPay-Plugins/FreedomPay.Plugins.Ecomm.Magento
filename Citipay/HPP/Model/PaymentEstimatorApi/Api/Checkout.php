<?php

namespace Citipay\HPP\Model\PaymentEstimatorApi\Api;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Citipay\HPP\Model\PaymentEstimatorApi\Http\Request;
use Citipay\HPP\Model\PaymentEstimatorApi\Constants;

/**
 * Gets information for Payment Estimator API call from Checkout
 */
class Checkout
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
     * Get URI params for API Request for checkout
     *
     * @param bool $isCheckout
     * @param mixed $price
     *
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function doRequestForCheckout(bool $isCheckout = true, mixed $price = 0): array
    {
        if ($isCheckout) {
            $quote = $this->checkoutSession->getQuote();
            $total = $quote->getGrandTotal();
        } else {
            $total = $price;
        }
        $uriParams = [
            'is_checkout' => $isCheckout,
            Constants::API_PARAM_SALE_AMOUNT => $total
        ];

        return $this->request->sendRequest(
            $uriParams
        );
    }
}
