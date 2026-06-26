<?php

namespace Freedompay\Common\Logger;

use DateTimeZone;
use Freedompay\Common\Gateway\Config\PaymentConfig;
use Monolog\LoggerFactory;
use Monolog\Logger as MonologLogger;

/**
 * Logger to log debug logs
 */
class Logger
{
    /**
     * @var PaymentConfig
     */
    private PaymentConfig $paymentConfig;

    /**
     * @var RedactData
     */
    protected RedactData $redactData;

    /**
     * @var LoggerFactory
     */
    private LoggerFactory $loggerFactory;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var mixed[]
     */
    private array $handlers;

    /**
     * @var mixed[]
     */
    private array $processors;

    /**
     * @var DateTimeZone
     */
    private DateTimeZone $timezone;

    /**
     * Constructor
     *
     * @param LoggerFactory $loggerFactory
     * @param PaymentConfig $paymentConfig
     * @param RedactData $redactData
     * @param string $name
     * @param array<mixed> $handlers
     * @param array<mixed> $processors
     * @param DateTimeZone|null $timezone
     * @throws \DateInvalidTimeZoneException
     */
    public function __construct(
        LoggerFactory     $loggerFactory,
        PaymentConfig $paymentConfig,
        RedactData        $redactData,
        string            $name = '',
        array             $handlers = [],
        array             $processors = [],
        ?DateTimeZone     $timezone = null
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->paymentConfig = $paymentConfig;
        $this->redactData = $redactData;
        $this->name = $name;
        $this->handlers = $handlers;
        $this->processors = $processors;
        $this->timezone = $timezone ?? new DateTimeZone(date_default_timezone_get());
    }

    /**
     * Logging info function
     *
     * @param mixed $message
     * @param array<mixed> $context
     * @param string $prefix
     * @return void
     */
    public function info(mixed $message, array $context = [], string $prefix = ''): void
    {
        if ($this->paymentConfig->isDebugEnabled()) {
            $redactedMessage = $this->redactData->redact($message);
            $formattedMessage = $prefix . $redactedMessage;
            $this->generateLogger()->info($formattedMessage, $context);
        }
    }

    /**
     * Logging info function
     *
     * @param mixed $message
     * @param array<mixed> $context
     * @param string $prefix
     * @return void
     */
    public function redactXML(mixed $message, array $context = [], string $prefix = ''): void
    {
        if ($this->paymentConfig->isDebugEnabled()) {
            $redactedMessage = $this->redactData->redactXMLData($message);
            $this->info($redactedMessage, $context, $prefix);
        }
    }

    /**
     * Method to pass data to logger factory and generate logger class
     *
     * @return MonologLogger
     */
    private function generateLogger(): MonologLogger
    {
        return $this->loggerFactory->create([
            'name' => $this->name,
            'handlers' => $this->handlers,
            'processors' => $this->processors,
            'timezone' => $this->timezone
        ]);
    }

    /**
     * Logging errors
     *
     * @param mixed $message
     * @param array<mixed> $context
     * @return void
     */
    public function error(mixed $message, array $context = []): void
    {
        $this->generateLogger()->error($message, $context);
    }

    /**
     * Logging critical errors
     *
     * @param mixed $message
     * @param array<mixed> $context
     * @return void
     */
    public function critical(mixed $message, array $context = []): void
    {
        $this->generateLogger()->critical($message, $context);
    }
}
