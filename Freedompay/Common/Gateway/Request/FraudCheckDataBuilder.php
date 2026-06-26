<?php
namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Gateway\Config\PaymentConfig;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;
use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Builds Fraud check data
 */
class FraudCheckDataBuilder implements BuilderInterface
{
    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * FraudCheckDataBuilder constructor.
     *
     * @param PaymentConfig $config
     */
    public function __construct(
        PaymentConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * Builds ENV request
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $requestData =[];
        //Fraud check should only be included, if it is enabled in the backend admin
        if ($this->config->isEnabled(CommonConfig::KEY_FRAUD_CHECK)) {
            $requestData[RequestHelper::FRAUD_CHECK] = true;
        }
        return $requestData;
    }
}
