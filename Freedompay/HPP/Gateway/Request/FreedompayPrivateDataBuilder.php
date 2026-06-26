<?php
namespace Freedompay\HPP\Gateway\Request;

use Freedompay\Common\Gateway\Config\Config as CommonConfig;
use Freedompay\Common\Helper\Requests as RequestHelper;
use Freedompay\HPP\Gateway\Config\PaymentConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\HPP\Helper\Requests as FpRequestHelper;

/**
 * Builds Freedompay required data
 */
class FreedompayPrivateDataBuilder implements BuilderInterface
{
    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * FreedompayPrivateDataBuilder constructor.
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
        $dccEnabled = $buildSubject['isAccountSaveCardAction'] == false
            && $this->config->isEnabled(PaymentConfig::KEY_DYNAMIC_CURRENCY_CONVERSION);
        $requestData =  [
            FpRequestHelper::TIMEOUT_MINUTES => (int)$this->config->getValue(PaymentConfig::KEY_TIME_OUT)
        ];
        if ($dccEnabled) {
            $requestData[FpRequestHelper::DCC_ENABLED] = true;
        }
        $isAddressRequired = $this->config->isEnabled(CommonConfig::KEY_ADDRESS_REQUIRED);
        $isAllowedGlobalAddress = $this->config->isEnabled(CommonConfig::KEY_ALLOW_INTL_ADDRESS);
        $is3dsEnabled = $this->config->isEnabled(PaymentConfig::KEY_3DS);
        if ($is3dsEnabled) {
            $isAddressRequired = true;
            $isAllowedGlobalAddress = true;
        }
        if ($is3dsEnabled
            && ($buildSubject['requestToken'] || $buildSubject['isAccountSaveCardAction'])
        ) {
            $requestTokenData= [
                FpRequestHelper::FIELDS => [
                    (object)[
                        FpRequestHelper::FIELDS_KEY => FpRequestHelper::CHALLENGE_INDICATOR,
                        FpRequestHelper::FIELDS_VALUE => FpRequestHelper::DEFAULT_INDICATOR_VAL
                    ],
                    (object)[
                        FpRequestHelper::FIELDS_KEY => FpRequestHelper::AUTH_INDICATOR,
                        FpRequestHelper::FIELDS_VALUE => FpRequestHelper::DEFAULT_INDICATOR_VAL
                    ]
                ]
            ];
            $requestData = array_merge($requestData, $requestTokenData);
        }
        if ($isAddressRequired) {
            $requestData[RequestHelper::ADDRESS_REQUIRED] = true;
        }
        if ($isAllowedGlobalAddress) {
            $requestData[RequestHelper::ALLOW_INTRNL_ADDR] = true;
        }
        return $requestData;
    }
}
