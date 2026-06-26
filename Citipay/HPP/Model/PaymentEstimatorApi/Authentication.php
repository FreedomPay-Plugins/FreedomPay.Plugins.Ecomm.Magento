<?php

namespace Citipay\HPP\Model\PaymentEstimatorApi;

use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Citipay\HPP\Gateway\Config\PaymentConfig as Config;

/**
 * Class to read auth token from db
 */
class Authentication
{
    /**
     * @var ConfigResource
     */
    protected ConfigResource $configResource;

    /**
     * @param ConfigResource $configResource
     */
    public function __construct(
        ConfigResource $configResource
    ) {
        $this->configResource = $configResource;
    }

    /**
     * Get config value from database
     *
     * @return string
     * @throws LocalizedException
     */
    public function getAuthToken(): string
    {
        $connection = $this->configResource->getConnection();
        if (!$connection) {
            return '';
        }
        $select = $connection->select()
            ->from($this->configResource->getMainTable(), ['value'])
            ->where('path = ?', Config::KEY_PAYMENT_ESTIMATOR_TOKEN_FULL_PATH)
            ->where('scope = ?', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->where('scope_id = ?', 0);

        return $connection->fetchOne($select);
    }
}
