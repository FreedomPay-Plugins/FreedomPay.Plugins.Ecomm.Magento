<?php

namespace Citipay\HPP\Model\PaymentEstimatorApi\Http;

use Exception;
use Citipay\HPP\Gateway\Config\PaymentConfig as Config;
use Citipay\HPP\Model\PaymentEstimatorApi\Constants;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Citipay\HPP\Logger\PaymentEstimatorApi\Logger;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Citipay\HPP\Model\PaymentEstimatorApi\Authentication;

/**
 * Executes API request
 */
class Client extends Curl
{
    /**
     * @var Curl
     */
    private Curl $curlClient;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var Authentication
     */
    private Authentication $auth;

    /**
     * @param Curl $curl
     * @param Config $config
     * @param Serializer $serializer
     * @param Logger $logger
     * @param Authentication $auth
     */
    public function __construct(
        Curl $curl,
        Config $config,
        Serializer $serializer,
        Logger $logger,
        Authentication $auth
    ) {
        parent::__construct();
        $this->curlClient   = $curl;
        $this->config       = $config;
        $this->serializer   = $serializer;
        $this->logger       = $logger;
        $this->auth         = $auth;
    }

    /**
     * Post Authentication API request
     *
     * @return string
     * @throws LocalizedException
     */
    public function doAuthenticate(): string
    {
        $url = $this->config->getTokenApiEndPoint();

        try {
            $clientCredentials = $this->config->getPaymentEstimatorCredentials();
            $uriParams = [
                Constants::API_PARAM_CLIENT_ID => $clientCredentials['clientId'],
                Constants::API_PARAM_CLIENT_SECRET => $clientCredentials['clientSecret'],
                Constants::API_PARAM_GRANT_TYPE_NAME => Constants::API_PARAM_GRANT_TYPE_VALUE
            ];

            $this->curlClient->setHeaders(
                [
                    'Cache-Control' => 'no-cache'
                ]
            );
            $encodeParams = (string)$this->serializer->serialize($uriParams);
            $this->logger->info($encodeParams, [], 'Authenticate API params::');
            $this->logger->info($url, [], 'Authenticate API URI::');
            $this->curlClient->makeRequest('POST', $url, $uriParams);
            $responseBody = $this->curlClient->getBody();
            $this->logger->info($responseBody, [], 'Authenticate API Response::');

            return $responseBody;
        } catch (Exception $exception) {
            $this->logger->error(
                'API Client - ' . $url . ' - request error:: ' . $exception->getMessage()
            );
            throw new LocalizedException(__($exception->getMessage()));
        }
    }

    /**
     * Post API request
     *
     * @param array<mixed> $uriParams
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function doRequest(
        array $uriParams = []
    ): array {
        $url = $this->config->getEstimatorApiEndPoint();
        try {
            $token = $this->auth->getAuthToken();
            $credentials = $this->config->getCredentials();
            $programType = $this->config->getProgramType();

            $uriParamsFromConfig = [
                Constants::API_PARAM_PROGRAM_NAME => $programType,
                Constants::API_PARAM_STORE_ID => $credentials['storeId'],
                Constants::API_PARAM_TERMINAL_ID => $credentials['terminalId']
            ];
            $uriParams = array_merge($uriParamsFromConfig, $uriParams);
            $uriParams = (string)$this->serializer->serialize($uriParams);

            $this->curlClient->setHeaders(
                [
                    'Content-Type'  =>  'application/json',
                    'Authorization' =>  'Bearer ' . $token
                ]
            );
            $this->logger->info($uriParams, [], 'API params::');
            $this->logger->info($url, [], 'API URI::');

            $this->curlClient->makeRequest('POST', $url, $uriParams);
            $responseBody = $this->curlClient->getBody();
            $this->logger->info($responseBody, [], 'API Response::');

            return [
                'response'  =>  $responseBody ?
                    $this->serializer->unserialize($responseBody) : [],
                'status'    =>  $this->curlClient->_responseStatus
            ];
        } catch (Exception $exception) {
            $this->logger->error(
                'API Client - ' . $url . ' - request error:: ' . $exception->getMessage()
            );
            throw new LocalizedException(__($exception->getMessage()));
        }
    }
}
