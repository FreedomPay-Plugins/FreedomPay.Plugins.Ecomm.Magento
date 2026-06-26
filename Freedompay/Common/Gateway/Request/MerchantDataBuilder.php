<?php
namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Freedompay\Common\Gateway\Config\PaymentConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Builds merchant account data
 */
class MerchantDataBuilder implements BuilderInterface
{
    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * BaseRequestDataBuilder constructor.
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
    public function build(array $buildSubject):array
    {
        $credentials = $this->config->getCredentials();
        return [
            RequestHelper::BO_STORE_ID => $credentials['storeId'],
            RequestHelper::BO_TERMINAL_ID => $credentials['terminalId']
        ];
    }
}
