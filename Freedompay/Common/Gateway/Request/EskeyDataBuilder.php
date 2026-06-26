<?php
namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Gateway\Config\PaymentConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;

/**
 * Builds Freedompay required data
 */
class EskeyDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private string $esKey;

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * FreedompayPrivateDataBuilder constructor.
     *
     * @param PaymentConfig $config
     * @param string $esKey
     */
    public function __construct(
        PaymentConfig $config,
        string $esKey = ''
    ) {
        $this->config = $config;
        $this->esKey = $esKey;
    }
    /**
     * Builds ENV request
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        return [
            $this->esKey => $this->config->getValue(CommonConfig::KEY_ESKEY)
        ];
    }
}
