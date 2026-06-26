<?php

namespace Citipay\HPP\Model\PaymentEstimatorApi\Http;

use Citipay\HPP\Model\PaymentEstimatorApi\Api\Authenticate;
use Citipay\HPP\Model\PaymentEstimatorApi\Constants;
use Magento\Framework\Exception\LocalizedException;

/**
 * Executes API request
 */
class Request
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var Authenticate
     */
    private Authenticate $authenticate;

    /**
     * @param Client $client
     * @param Authenticate $authenticate
     */
    public function __construct(
        Client $client,
        Authenticate $authenticate
    ) {
        $this->client = $client;
        $this->authenticate = $authenticate;
    }

    /**
     * Send API request
     *
     * @param array<mixed> $uriParams
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function sendRequest(
        array $uriParams = []
    ): array {
        $response = $this->client->doRequest(
            $uriParams
        );
        // If unauthorized error is received
        if (isset($response['status']) && $response['status'] == Constants::API_STATUS_401) {
            //Generate new authentication token
            $this->authenticate->generateAuthToken();
            //Retry api request
            $response = $this->client->doRequest(
                $uriParams
            );
        }
        $successStatuses = [Constants::API_SUCCESS_STATUS, Constants::API_SUCCESS_STATUS_NON_QUALIFYING_AMOUNT];
        if (isset($response['status']) && in_array($response['status'], $successStatuses)) {
            $response['response']['status'] = $response['status'];
            return $response['response'];
        } else {
            return [];
        }
    }
}
