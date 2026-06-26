<?php
namespace Citipay\HPP\Logger\PaymentEstimatorApi;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Log handler for Citipay
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
    protected $fileName = '/var/log/citipay_payment_estimator.log';
}
