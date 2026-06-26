<?php

namespace Freedompay\Common\Gateway\Http\Client;

use Exception;
use Freedompay\Common\Helper\Requests;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Framework\HTTP\Header as HttpHeader;
use Freedompay\Common\Logger\Logger;
use Freedompay\Common\Model\Data\XmlParser;

/**
 * Execute API request
 */
class TimeoutReversalClient implements ClientInterface
{
    public const RETRY_LIMIT = 1;
    public const TIMEOUT_VALUE = 35;

    /**
     * @var CurlFactory
     */
    protected CurlFactory $curlFactory;

    /**
     * @var Curl
     */
    protected Curl $curlClient;

    /**
     * @var HttpHeader
     */
    protected HttpHeader $httpHeader;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var XmlParser
     */
    private XmlParser $xmlParser;

    /**
     * FreedomPayClient constructor.
     * @param CurlFactory $curlFactory
     * @param Curl $curl
     * @param HttpHeader $httpHeader
     * @param Logger $logger
     * @param XmlParser $xmlParser
     */
    public function __construct(
        curlFactory $curlFactory,
        Curl $curl,
        HttpHeader $httpHeader,
        Logger $logger,
        XmlParser $xmlParser
    ) {
        $this->curlFactory = $curlFactory;
        $this->curlClient = $curl;
        $this->httpHeader = $httpHeader;
        $this->logger = $logger;
        $this->xmlParser = $xmlParser;
    }

    /**
     * Places request to gateway. Returns result as array
     *
     * @param TransferInterface $transferObject
     * @return array|mixed
     * @throws LocalizedException
     * @throws Exception
     */
    public function placeRequest(TransferInterface $transferObject): mixed
    {
        $headers = $transferObject->getHeaders();
        $contentType = $headers['Content-Type'];

        if ($contentType ==  Requests::CONTENT_TYPE_JSON) {
            $params = (string)json_encode($transferObject->getBody());
        } else {
            $params = $transferObject->getBody();
        }
        $response = $this->postRequest($transferObject->getUri(), $params, $headers);

        $this->logger->info($response, [], 'API Response::');
        if ($contentType == Requests::CONTENT_TYPE_XML) {
            return $this->xmlParser->parseResponse($response);
        }
        return $response ? json_decode($response, true) : [];
    }

    /**
     * Execute CreateTransaction service
     *
     * @param string $url
     * @param string|array<mixed> $params
     * @param array<mixed> $headers
     * @return string
     * @throws LocalizedException
     */
    public function postRequest(
        string $url,
        array|string $params,
        array $headers
    ): string {
        $retryCount = 0;
        $serviceType = isset($headers['X-Service-Type']) ? ucfirst($headers['X-Service-Type']) : null;
        unset($headers['X-Service-Type']);
        /** @phpstan-ignore-next-line */
        while ($retryCount <= self::RETRY_LIMIT) {
            if ($retryCount == 1) {
                if (is_string($params)) {
                    $this->logger->info("Adding torService field for retry attempt #{$retryCount}");
                    $params = $this->xmlParser->addTorServiceField($params);
                }
            }
            $this->logger->redactXML($params, [], 'API params::');
            $this->logger->info($url, [], 'API URI::');
            try {
                if ($params) {
                    $this->curlClient->setHeaders($headers);
                    $this->curlClient->setOption((string)CURLOPT_TIMEOUT, self::TIMEOUT_VALUE);
                    $this->curlClient->post($url, $params);
                    return $this->curlClient->getBody();
                }
            } catch (Exception $exception) {
                $this->logger->error('TimeoutReversalClient post request error:: ' . $exception->getMessage());
                $retryCount++;
                if ($retryCount > self::RETRY_LIMIT) {
                    throw new LocalizedException(
                        __("{$serviceType} was unsuccessful due to Freeway Service Timeout")
                    );
                }
                $this->logger->info("Retrying {$serviceType} attempt #{$retryCount} due to timeout...");
            }
        }
        /** @phpstan-ignore-next-line */
        throw new LocalizedException(__('Unexpected error: request failed without a valid response.'));
    }
}
