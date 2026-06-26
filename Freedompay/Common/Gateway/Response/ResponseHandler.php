<?php

namespace Freedompay\Common\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Freedompay\Common\Model\Cart\QuoteManager;

/**
 * Class ResponseHandler
 * Freedompay/Citi Pay Response Handler
 */
class ResponseHandler implements HandlerInterface
{

    /**
     * @var QuoteManager
     */
    protected QuoteManager $quoteManager;

    /**
     * ResponseHandler constructor.
     * @param QuoteManager $quoteManager
     */
    public function __construct(
        QuoteManager $quoteManager
    ) {
        $this->quoteManager = $quoteManager;
    }

    /**
     * Handles payment transaction response
     *
     * @param array<mixed> $handlingSubject
     * @param array<mixed> $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        if ($response && isset($response['TransactionId'])) {
            $this->quoteManager->setPaymentAdditionalInformation($response['TransactionId']);
        }
    }
}
