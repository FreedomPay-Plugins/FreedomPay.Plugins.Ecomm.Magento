<?php

namespace Citipay\HPP\Model\Ui;

use Citipay\HPP\Gateway\Config\PaymentConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ConfigProvider - Configuration class for Citi Pay
 */
class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'citipay_hpp';

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * ConfigProvider constructor.
     * @param PaymentConfig $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PaymentConfig $config,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
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
                        'active'            => (bool)$this->config->getValue('active'),
                        'initPaymentUrl'    => $store->getUrl(
                            'citipayhpp/payment/initpayment',
                            ['_secure' => $store->isCurrentlySecure()]
                        ),
                        'citipayType' => $this->config->getValue('citipay_type'),
                        'paymentEstimatorEnabled' => $this->config->isPaymentEstimatorEnabled()
                    ]
                ]
            ]
        ];
    }
}
