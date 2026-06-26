<?php
namespace Freedompay\HPP\Model\Ui;

use Freedompay\Common\Gateway\Config\Config as CommonConfig;
use Freedompay\HPP\Gateway\Config\PaymentConfig;
use Freedompay\HPP\Model\Data\SavedCard as SavedCardData;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ConfigProvider - Freedompay-HPP configuration class
 */
class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'freedompay_hpp';

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var SavedCardData
     */
    private SavedCardData $savedCardData;

    /**
     * ConfigProvider constructor.
     * @param PaymentConfig $config
     * @param StoreManagerInterface $storeManager
     * @param SavedCardData $savedCardData
     */
    public function __construct(
        PaymentConfig $config,
        StoreManagerInterface $storeManager,
        SavedCardData $savedCardData
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->savedCardData = $savedCardData;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array<mixed>
     * @throws NoSuchEntityException|LocalizedException
     */
    public function getConfig():array
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();

        return [
            'payment' => [
                self::CODE => [
                    'methodCode'    => self::CODE,
                    'config' => [
                        'active' => (bool)$this->config->getValue('active'),
                        'request_token' => $this->config->isEnabled(CommonConfig::KEY_REQUEST_TOKEN),
                        'initPaymentUrl' => $store->getUrl(
                            'freedompayhpp/payment/initpayment',
                            ['_secure' => $store->isCurrentlySecure()]
                        ),
                        'logos' => $this->config->getLogos()
                    ],
                    'savedCards' => $this->savedCardData->getCustomerSavedCards(self::CODE)
                ]
            ]
        ];
    }
}
