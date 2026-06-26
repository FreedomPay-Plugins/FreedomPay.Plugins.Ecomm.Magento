<?php

namespace Citipay\HPP\Model\PaymentEstimatorApi\Api;

use Magento\Framework\Exception\LocalizedException;
use Citipay\HPP\Model\PaymentEstimatorApi\Http\Client;
use Citipay\HPP\Gateway\Config\PaymentConfig as Config;
use Citipay\HPP\Model\PaymentEstimatorApi\Constants;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Get authentication token from API
 */
class Authenticate
{

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var WriterInterface
     */
    private WriterInterface $configWriter;

    /**
     * @param Client $client
     * @param Serializer $serializer
     * @param WriterInterface $configWriter
     */
    public function __construct(
        Client $client,
        Serializer $serializer,
        WriterInterface $configWriter
    ) {
        $this->client = $client;
        $this->serializer   = $serializer;
        $this->configWriter = $configWriter;
    }

    /**
     * Generate new authentication token
     *
     * @return string
     * @throws LocalizedException
     */
    public function generateAuthToken(): string
    {
        // Generate new authentication token
        $token  = $this->getAuthToken();

        if ($token) {
            //Save new token in the database
            $this->saveAuthToken($token);
        }

        return $token;
    }

    /**
     * Get authentication token
     *
     * @return string
     * @throws LocalizedException
     */
    public function getAuthToken():string
    {
        $authToken = $this->client->doAuthenticate();
        if ($authToken) {
            $authToken = $this->serializer->unserialize($authToken);
            if (isset($authToken[Constants::API_KEY_TOKEN])) {
                return $authToken[Constants::API_KEY_TOKEN];
            }
        }
        return '';
    }

    /**
     * Save auth token to database
     *
     * @param string $token
     * @return void
     */
    public function saveAuthToken(string $token): void
    {
        $this->configWriter->save(
            Config::KEY_PAYMENT_ESTIMATOR_TOKEN_FULL_PATH,
            $token
        );
    }
}
