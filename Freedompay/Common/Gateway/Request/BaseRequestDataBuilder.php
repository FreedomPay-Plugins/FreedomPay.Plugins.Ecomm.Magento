<?php
namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Freedompay\Common\Gateway\Config\PaymentConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;

/**
 * Builds merchant account data
 */
class BaseRequestDataBuilder implements BuilderInterface
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
        $credentials = $this->config->getCredentials();
        $isAllowedGlobalAddress = $this->config->isEnabled(CommonConfig::KEY_ALLOW_INTL_ADDRESS);
        $requestData = [
            RequestHelper::STORE_ID => $credentials['storeId'],
            RequestHelper::TERMINAL_ID => $credentials['terminalId'],
            RequestHelper::CSS_ID => $this->config->getValue(CommonConfig::KEY_CSS_ID),
            RequestHelper::CULTURE_CODE => $this->requestHelper->getCultureCode(),
            RequestHelper::MERCHANT_REF_CODE => strtotime('now') . uniqid()
        ];
        if ($isAllowedGlobalAddress) {
            $requestData[RequestHelper::ALLOW_INTRNL_ADDR] = true;
        }
        return $this->requestHelper->removeNullValues($requestData);
    }
}
