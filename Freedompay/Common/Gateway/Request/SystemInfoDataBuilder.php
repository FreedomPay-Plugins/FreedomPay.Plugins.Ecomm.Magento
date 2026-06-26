<?php

namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Gateway\Config\PaymentConfig;
use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;

/**
 * Builds system info
 */
class SystemInfoDataBuilder implements BuilderInterface
{
    /**
     * @var RequestHelper
     */
    private RequestHelper $requestHelper;

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * @var string
     */
    private string $moduleName;

    /**
     * @param RequestHelper $requestHelper
     * @param PaymentConfig $config
     * @param string $moduleName
     */
    public function __construct(
        RequestHelper $requestHelper,
        PaymentConfig $config,
        string $moduleName
    ) {
        $this->requestHelper = $requestHelper;
        $this->config = $config;
        $this->moduleName = $moduleName;
    }

    /**
     * Builds system info request
     *
     * @param array<string> $buildSubject
     * @return array<mixed>
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function build(array $buildSubject): array
    {
        $moduleName = $this->moduleName;

        return [
            RequestHelper::CLIENT_METADATA => [
                RequestHelper::SYSTEM_NAME => $this->config->getValue(CommonConfig::KEY_SYSTEM_NAME),
                RequestHelper::SYSTEM_VERSION =>
                    $this->config->getValue(CommonConfig::KEY_SYSTEM_VERSION),
                RequestHelper::MIDDLEWARE_NAME => CommonConfig::VALUE_MIDDLEWARE_NAME,
                RequestHelper::MIDDLEWARE_VERSION => $this->requestHelper->getMagentoModuleVersion($moduleName)
            ]
        ];
    }
}
