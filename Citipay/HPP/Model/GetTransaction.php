<?php
namespace Citipay\HPP\Model;

use Citipay\HPP\Logger\Logger;
use Freedompay\Common\Model\Api\RequestManager;
use Freedompay\Common\Model\Cart\QuoteManager;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Freedompay\Common\Helper\Requests as RequestHelper;

/**
 *
 * Process GetTransaction Request for Citi Pay
 */
class GetTransaction extends RequestManager
{
    /**
     * GetTransaction constructor.
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param QuoteManager $quoteManager
     * @param Logger $logger
     * @param string $commandName
     */
    //phpcs:disable
    public function __construct(
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteManager $quoteManager,
        Logger  $logger,
        string $commandName = RequestHelper::GET_TRANSACTION
    ) {
        parent::__construct(
            $commandPool,
            $paymentDataObjectFactory,
            $quoteManager,
            $logger,
            $commandName
        );
    }
}
