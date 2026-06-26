<?php
namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Gateway\Config\PaymentConfig;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;
use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Model\Quote;

/**
 * Builds Fraud check data
 */
class FraudCheckServiceDataBuilder implements BuilderInterface
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
        $isFraudCheckEnabled = $this->config->isEnabled(CommonConfig::KEY_FRAUD_CHECK);
        $fraudOrder = $this->config->getValue(CommonConfig::KEY_FRAUD_ORDER);
        $fraudOrderVoidThreshold = $this->config->getValue(CommonConfig::KEY_FRAUD_VOID_THRESHOLD);
        /** @var Quote $quote */
        $quote = $buildSubject['quote'];
        if ($isFraudCheckEnabled && $fraudOrder && $fraudOrderVoidThreshold) {
            $fraudCheckService = $this->buildFraudCheckData($fraudOrder, $fraudOrderVoidThreshold, $quote);
            return [
                RequestHelper::FRAUD_CHECK => true,
                RequestHelper::FRAUD_CHECK_DATA => $fraudCheckService
            ];
        } else {
            return [];
        }
    }

    /**
     * Builds FraudCheckData array
     *
     * @param string $fraudOrder
     * @param string $fraudOrderVoidThreshold
     * @param Quote $quote
     * @return array<mixed>
     */
    public function buildFraudCheckData(string $fraudOrder, string $fraudOrderVoidThreshold, Quote $quote): array
    {
        return [
            'Order' => $fraudOrder,
            'VoidThreshold' => $fraudOrderVoidThreshold,
            "SiteIdentifier"=> "internet"
        ];
    }
}
