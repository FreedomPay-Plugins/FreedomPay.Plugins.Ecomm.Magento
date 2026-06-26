<?php

namespace Freedompay\HPP\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Freedompay\HPP\Model\Ui\ConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Payment\Gateway\Config\Config;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;

/**
 * Payment configuration class for Freedompay
 */
class PaymentConfig extends \Freedompay\Common\Gateway\Config\PaymentConfig
{
    public const MODULE_NAME = 'Freedompay_HPP';
    public const KEY_3DS = '3ds';
    public const KEY_TIME_OUT = 'time_out';
    public const KEY_DYNAMIC_CURRENCY_CONVERSION = 'dynamic_currency_conversion';
    public const KEY_SPLIT_CAPTURE = 'split_capture';

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var CommonConfig
     */
    private CommonConfig $commonConfig;

    /**
     * PaymentConfig constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Serializer $serializer
     * @param CommonConfig $commonConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    //phpcs:disable
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Serializer           $serializer,
        CommonConfig         $commonConfig,
        string               $methodCode = ConfigProvider::CODE,
        string               $pathPattern = Config::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct(
            $scopeConfig,
            $methodCode,
            $pathPattern
        );
        $this->serializer = $serializer;
        $this->commonConfig = $commonConfig;
    }

    /**
     * Get logo images from config
     *
     * @return array<mixed>
     * @throws NoSuchEntityException
     */
    public function getLogos(): array
    {
        $logos = $this->getValue('logos');
        $logoImageFileNames = [];
        if ($logos) {
            $logoImageFileNames = (array)$this->serializer->unserialize($logos);
            if (!empty($logoImageFileNames)) {
                foreach ($logoImageFileNames as $key => $logo) {
                    $logoImageFileNames[$key] = $this->commonConfig->getMediaUrl($logo);
                }
            }
        }
        return $logoImageFileNames;
    }

}
