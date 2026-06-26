<?php

namespace Freedompay\HPP\Model;

use Freedompay\Common\Model\Cart\QuoteManager;
use Freedompay\HPP\Helper\Requests as RequestHelper;
use Freedompay\HPP\Logger\Logger;
use Freedompay\HPP\Model\Api\CreateVerficationTransaction\RequestManager;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;

/**
 * Process CreateVerificationTransaction Request for Freedompay
 */
class CreateVerification extends RequestManager
{
    /**
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
        Logger $logger,
        string $commandName = RequestHelper::CREATE_VERIFICATION_TRANSACTION

    ) {
        parent::__construct($commandPool, $paymentDataObjectFactory, $quoteManager, $logger, $commandName);
    }
}
