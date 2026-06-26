<?php
namespace Freedompay\Common\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Log handler for Freedompay
 */
class Handler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    protected $fileName = '/var/log/freedompay.log';

    /**
     * Gets logs file location
     *
     * @return string
     */
    public function getLogLocation(): string
    {
        return $this->fileName;
    }
}
