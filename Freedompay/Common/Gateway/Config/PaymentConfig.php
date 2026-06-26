<?php

namespace Freedompay\Common\Gateway\Config;

use Freedompay\Common\Gateway\Config\Config as CommonConfig;
use Freedompay\Common\Model\Adminhtml\Source\Environment;

/**
 * Payment configuration class common to the modules
 */
class PaymentConfig extends \Magento\Payment\Gateway\Config\Config
{
    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->getValue(CommonConfig::KEY_ACTIVE);
    }

    /**
     * Get title of payment
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->getValue(CommonConfig::KEY_TITLE);
    }

    /**
     * Get environment mode
     *
     * @return mixed|string|null
     */
    public function isTestMode(): mixed
    {
        $mode = $this->getValue(CommonConfig::KEY_MODE);
        if ($mode == Environment::VALUE_ENVIRONMENT_PRODUCTION) {
            return false;
        }
        return true;
    }

    /**
     * Get environment credentials
     *
     * @return mixed|string|null
     */
    public function getCredentials(): mixed
    {
        if ($this->isTestMode()) {
            return [
                'storeId' => $this->getValue(CommonConfig::KEY_TEST_STORE_ID),
                'terminalId' => $this->getValue(CommonConfig::KEY_TEST_TERMINAL_ID),
            ];
        } else {
            return [
                'storeId' => $this->getValue(CommonConfig::KEY_LIVE_STORE_ID),
                'terminalId' => $this->getValue(CommonConfig::KEY_LIVE_TERMINAL_ID),
            ];
        }
    }

    /**
     * Check if key is enabled
     *
     * @param string $key
     * @return bool|null
     */
    public function isEnabled(string $key): bool|null
    {
        return $this->getValue($key);
    }

    /**
     * Get endpoint
     *
     * @return string|null
     */
    public function getApiEndPoint(): ?string
    {
        if ($this->isTestMode()) {
            return $this->getValue(CommonConfig::KEY_TEST_API_END_POINT);
        } else {
            return $this->getValue(CommonConfig::KEY_LIVE_API_END_POINT);
        }
    }

    /**
     * Get soap api endpoint
     *
     * @return string|null
     */
    public function getSoapApiEndPoint(): ?string
    {
        if ($this->isTestMode()) {
            return $this->getValue(CommonConfig::KEY_TEST_SOAP_API_END_POINT);
        } else {
            return $this->getValue(CommonConfig::KEY_LIVE_SOAP_API_END_POINT);
        }
    }

    /**
     * Check if debug is enabled
     *
     * @return mixed|null
     */
    public function isDebugEnabled(): mixed
    {
        return $this->getValue(CommonConfig::KEY_DEBUG);
    }
}
