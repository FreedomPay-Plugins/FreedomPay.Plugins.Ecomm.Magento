<?php

declare(strict_types=1);

namespace Freedompay\Common\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Freedompay\Common\Model\ImageHandler;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Common configuration class
 */
class Config
{

    /**
     * Path to config value that contains weight unit
     */
    public const XML_PATH_WEIGHT_UNIT = 'general/locale/weight_unit';

    /**
     * Path to config value of locale
     */

    public const XML_PATH_DEFAULT_LOCALE = 'general/locale/code';
    public const TAX_CALCULATION_INCLUDING_TAX = 'tax/calculation/price_includes_tax';

    public const KEY_ACTIVE = 'active';
    public const KEY_TITLE = 'title';
    public const KEY_MODE = 'mode';
    public const KEY_TEST_API_END_POINT = 'test_api_end_point';
    public const KEY_LIVE_API_END_POINT = 'live_api_end_point';
    public const KEY_TEST_SOAP_API_END_POINT = 'test_soap_api_end_point';
    public const KEY_LIVE_SOAP_API_END_POINT = 'live_soap_api_end_point';
    public const KEY_TEST_STORE_ID = 'test_store_id';
    public const KEY_TEST_TERMINAL_ID = 'test_terminal_id';
    public const KEY_LIVE_STORE_ID = 'live_store_id';
    public const KEY_LIVE_TERMINAL_ID = 'live_terminal_id';
    public const KEY_PAYMENT_ACTION = 'payment_action';
    public const KEY_REQUEST_TOKEN = 'request_token';
    public const KEY_TOKEN_TYPE = 'token_type';
    public const KEY_CSS_ID = 'css_id';
    public const KEY_FRAUD_CHECK = 'fraud_settings/fraud_check';
    public const KEY_FRAUD_ORDER = 'fraud_settings/fraud_order';
    public const KEY_FRAUD_VOID_THRESHOLD = 'fraud_settings/fraud_void_threshold';
    public const KEY_SHOW_ADDRESS = 'show_address';
    public const KEY_ADDRESS_REQUIRED = 'address_required';
    public const KEY_ALLOW_INTL_ADDRESS = 'allow_intl_address';
    public const KEY_DEBUG = 'debug';
    public const KEY_SYSTEM_NAME = 'system_name';
    public const KEY_SYSTEM_VERSION = 'system_version';
    public const VALUE_MIDDLEWARE_NAME = 'FreedomPay_Magento_Plugin';
    public const KEY_ESKEY = 'eskey';
    public const ERR_MSG_GENERIC = 'Something went wrong while processing the request.';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var mixed|StoreManagerInterface
     */
    private mixed $storeManager;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get config values
     *
     * @param string $configPath
     * @return mixed
     */
    public function getConfig(string $configPath): mixed
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get logo file url
     *
     * @param string $file
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl(string $file): string
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');
        /** @var Store $storeManager */
        $storeManager = $this->storeManager->getStore();
        return $storeManager->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . ImageHandler::FILE_DIR . '/' . $file;
    }
}
