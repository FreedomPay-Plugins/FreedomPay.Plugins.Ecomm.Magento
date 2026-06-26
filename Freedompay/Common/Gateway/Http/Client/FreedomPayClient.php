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
use Magento\Framework\Serialize\Serializer\Json as Serializer;

/**
 * Execute API request
 */
class FreedomPayClient implements ClientInterface
{
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
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * FreedomPayClient constructor.
     * @param CurlFactory $curlFactory
     * @param Curl $curl
     * @param HttpHeader $httpHeader
     * @param Logger $logger
     * @param XmlParser $xmlParser
     * @param Serializer $serializer
     */
    public function __construct(
        curlFactory $curlFactory,
        Curl $curl,
        HttpHeader $httpHeader,
        Logger $logger,
        XmlParser $xmlParser,
        Serializer $serializer
    ) {
        $this->curlFactory = $curlFactory;
        $this->curlClient = $curl;
        $this->httpHeader = $httpHeader;
        $this->logger = $logger;
        $this->xmlParser = $xmlParser;
        $this->serializer = $serializer;
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

        $this->logger->info($params, [], 'API params::');
        $this->logger->info($transferObject->getUri(), [], 'API URI::');

        $response = $this->postRequest($transferObject->getUri(), $params, $headers);

        $this->logger->info($response['body'], [], 'API Response::');
        if ($contentType == Requests::CONTENT_TYPE_XML) {
            return $this->xmlParser->parseResponse($response['body']);
        }
        if ($response['body']) {
            try {
                $responseBody = $this->serializer->unserialize($response['body']);
            } catch (Exception) {
                $responseBody = null;
            }
            if (is_array($responseBody)) {
                $responseBody['status'] = $response['status_code'];
            }
            return $responseBody;
        }
        return [];
    }

    /**
     * Execute CreateTransaction service
     *
     * @param string $url
     * @param string|array<mixed> $params
     * @param array<mixed> $headers
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function postRequest(
        string $url,
        array|string $params,
        array $headers
    ): array {
        try {
            $this->curlClient->setHeaders($headers);
            $this->curlClient->post($url, $params);
            $statusCode = $this->curlClient->getStatus();
            $responseBody = $this->curlClient->getBody();
            return [
                'status_code' => $statusCode,
                'body'        => $responseBody
            ];
        } catch (Exception $exception) {
            $this->logger->error('API Client post request error:: ' . $exception->getMessage());
            throw new LocalizedException(__($exception->getMessage()));
        }
    }
}
