<?php
namespace Freedompay\HPP\Gateway\Request;

use Freedompay\Common\Helper\Requests;
use Freedompay\HPP\Helper\Requests as RequestHelper;
use Freedompay\HPP\Gateway\Config\PaymentConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;

/**
 * Builds merchant account data
 */
class RequestTokenDataBuilder implements BuilderInterface
{
    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * @var RequestHelper
     */
    private RequestHelper $requestHelper;

    /**
     * BaseRequestDataBuilder constructor.
     *
     * @param PaymentConfig $config
     * @param RequestHelper $requestHelper
     */
    public function __construct(
        PaymentConfig $config,
        RequestHelper $requestHelper
    ) {
        $this->config = $config;
        $this->requestHelper = $requestHelper;
    }
    /**
     * Builds ENV request
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject):array
    {
        $requestToken = (bool)$buildSubject['requestToken'];
        $tokenValue = $buildSubject['tokenValue'] ?? null;

        $requestData = [
            Requests::TOKEN_VALUE => $tokenValue
        ];
        if ($buildSubject['isAccountSaveCardAction']) {
            $requestData[Requests::INVOICE_NUMBER] = 'z' .  strtotime('now');
        }
        if ($requestToken) {
            $requestData[RequestHelper::REQUEST_TOKEN] = true;
            $requestData[RequestHelper::TOKEN_TYPE] = $this->config->getValue(CommonConfig::KEY_TOKEN_TYPE);
        }

        return $this->requestHelper->removeNullValues($requestData);
    }
}
