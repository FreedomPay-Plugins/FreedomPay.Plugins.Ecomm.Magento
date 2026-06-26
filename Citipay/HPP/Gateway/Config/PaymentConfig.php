<?php

namespace Citipay\HPP\Gateway\Config;

use Freedompay\Common\Model\Adminhtml\Source\Environment;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Citipay\HPP\Model\Ui\ConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Config\Config;
use Magento\Store\Model\StoreManagerInterface;
use Citipay\HPP\Helper\Constants;

/**
 * Payment configuration class for Citipay
 */
class PaymentConfig extends \Freedompay\Common\Gateway\Config\PaymentConfig
{
    public const MODULE_NAME = 'Citipay_HPP';

    public const KEY_PAYMENT_ESTIMATOR_ENABLED = 'citipay_payment_estimator/enabled';
    public const KEY_PAYMENT_ESTIMATOR_MODE = 'citipay_payment_estimator/mode';
    public const KEY_PAYMENT_ESTIMATOR_TEST_CLIENT_ID = 'citipay_payment_estimator/test_client_id';
    public const KEY_PAYMENT_ESTIMATOR_LIVE_CLIENT_ID = 'citipay_payment_estimator/live_client_id';
    public const KEY_PAYMENT_ESTIMATOR_TEST_CLIENT_SECRET = 'citipay_payment_estimator/test_client_secret';
    public const KEY_PAYMENT_ESTIMATOR_LIVE_CLIENT_SECRET = 'citipay_payment_estimator/live_client_secret';
    public const KEY_PAYMENT_ESTIMATOR_TEST_END_POINT = 'citipay_payment_estimator/payment_estimator_test_api_end_point';// phpcs:ignore
    public const KEY_PAYMENT_ESTIMATOR_LIVE_END_POINT = 'citipay_payment_estimator/payment_estimator_live_api_end_point';// phpcs:ignore
    public const KEY_PAYMENT_ESTIMATOR_TOKEN = 'citipay_payment_estimator/payment_estimator_token';
    public const KEY_PAYMENT_ESTIMATOR_TOKEN_FULL_PATH = 'payment/citipay_hpp/citipay_payment_estimator/payment_estimator_token';// phpcs:ignore
    public const KEY_PAYMENT_ESTIMATOR_TEST_TOKEN_END_POINT = 'citipay_payment_estimator/payment_estimator_test_token_api_end_point';// phpcs:ignore
    public const KEY_PAYMENT_ESTIMATOR_LIVE_TOKEN_END_POINT = 'citipay_payment_estimator/payment_estimator_live_token_api_end_point';// phpcs:ignore
    public const KEY_PAYMENT_ESTIMATOR_ENABLE_PDP_MESSAGING = 'citipay_payment_estimator/enable_pdp_messaging';
    public const CITIPAY_MIL_PE_PROGRAM_TYPE_CODE = 'CitiPayMIL';
    public const CITIPAY_DLOC_PE_PROGRAM_TYPE_CODE = 'CitiTTS';

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * PaymentConfig constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param string $methodCode
     * @param string $pathPattern
     */
    //phpcs:disable
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        string $methodCode = ConfigProvider::CODE,
        string $pathPattern = Config::DEFAULT_PATH_PATTERN
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $scopeConfig,
            $methodCode,
            $pathPattern
        );
    }

    /**
     * Get environment mode
     *
     * @return mixed|string|null
     */
    public function isEstimatorTestMode(): mixed
    {
        $mode = $this->getValue(self::KEY_PAYMENT_ESTIMATOR_MODE);
        return $mode == Environment::VALUE_ENVIRONMENT_SANDBOX;
    }

    /**
     * Get payment estimator api endpoint
     *
     * @return string
     */
    public function getEstimatorApiEndPoint(): string
    {
        if ($this->isEstimatorTestMode()) {
            return $this->getValue(self::KEY_PAYMENT_ESTIMATOR_TEST_END_POINT);
        } else {
            return $this->getValue(self::KEY_PAYMENT_ESTIMATOR_LIVE_END_POINT);
        }
    }

    /**
     * Get payment estimator token api endpoint
     *
     * @return string
     */
    public function getTokenApiEndPoint(): string
    {
        if ($this->isEstimatorTestMode()) {
            return $this->getValue(self::KEY_PAYMENT_ESTIMATOR_TEST_TOKEN_END_POINT);
        } else {
            return $this->getValue(self::KEY_PAYMENT_ESTIMATOR_LIVE_TOKEN_END_POINT);
        }
    }

    /**
     * Get client credentials
     *
     * @return mixed|string|null
     */
    public function getPaymentEstimatorCredentials(): mixed
    {
        if ($this->isEstimatorTestMode()) {
            return [
                'clientId' => $this->getValue(self::KEY_PAYMENT_ESTIMATOR_TEST_CLIENT_ID),
                'clientSecret' => $this->getValue(self::KEY_PAYMENT_ESTIMATOR_TEST_CLIENT_SECRET)
            ];
        } else {
            return [
                'clientId' => $this->getValue(self::KEY_PAYMENT_ESTIMATOR_LIVE_CLIENT_ID),
                'clientSecret' => $this->getValue(self::KEY_PAYMENT_ESTIMATOR_LIVE_CLIENT_SECRET)
            ];
        }
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isPaymentEstimatorEnabled(): bool
    {
        return $this->getValue(self::KEY_PAYMENT_ESTIMATOR_ENABLED) && $this->isCurrencyUSD();
    }

    /**
     * Get PDP payment messaging status
     *
     * @return bool
     */
    public function isPdpMessagingEnabled(): bool
    {
        return (bool) $this->getValue(self::KEY_PAYMENT_ESTIMATOR_ENABLE_PDP_MESSAGING);
    }

    /**
     * Check if the current currency is USD
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isCurrencyUSD(): bool
    {
        /** @phpstan-ignore-next-line */
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        return $currentCurrencyCode === 'USD';
    }

    /**
     * Check citipay type selected and return corresponding programType
     *
     * @return string
     */
    public function getProgramType(): string
    {
        $citipayType = $this->getValue('citipay_type');
        if ($citipayType == Constants::CITIPAY_MIL_VALUE) {
            return self::CITIPAY_MIL_PE_PROGRAM_TYPE_CODE;
        }
        return self::CITIPAY_DLOC_PE_PROGRAM_TYPE_CODE;
    }

}
