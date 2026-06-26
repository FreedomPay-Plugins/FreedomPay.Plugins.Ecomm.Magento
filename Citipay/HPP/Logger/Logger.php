<?php
namespace Citipay\HPP\Logger;

use DateTimeZone;
use Citipay\HPP\Gateway\Config\PaymentConfig;
use Freedompay\Common\Logger\Logger as BaseLogger;
use Freedompay\Common\Logger\RedactData;
use Monolog\LoggerFactory;

/**
 * Logger for Citipay
 */
class Logger extends BaseLogger
{
    /**
     * @param LoggerFactory $loggerFactory
     * @param PaymentConfig $paymentConfigConfig
     * @param RedactData $redactData
     * @param string $name
     * @param array<mixed> $handlers
     * @param array<mixed> $processors
     * @param DateTimeZone|null $timezone
     * @throws \DateInvalidTimeZoneException
     */
    //phpcs:disable
    public function __construct(
        LoggerFactory     $loggerFactory,
        PaymentConfig $paymentConfigConfig,
        RedactData $redactData,
        string $name = '',
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        parent::__construct($loggerFactory, $paymentConfigConfig, $redactData, $name, $handlers, $processors, $timezone);
    }
}
