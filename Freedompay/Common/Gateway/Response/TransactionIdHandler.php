<?php

namespace Freedompay\Common\Gateway\Response;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Handles transaction Id from response
 */
class TransactionIdHandler implements HandlerInterface
{
    /**
     * Handles transaction id
     *
     * @param array<mixed> $handlingSubject
     * @param array<mixed> $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response): void
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $handlingSubject['payment'];
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $payment->setTransactionId($this->getTransactionID($response));
        $payment->setIsTransactionClosed(false);
    }

    /**
     * Get transactionId from response
     *
     * @param array<mixed> $response
     * @return string
     */
    public function getTransactionID(array $response): string
    {
        $transactionId = '';
        if (isset($response[RequestHelper::CHECKOUT_TRANSACTION_ID])) {
            $transactionId = $response[RequestHelper::CHECKOUT_TRANSACTION_ID];
        } elseif (isset($response[RequestHelper::GET_TRANSACTION_ID])) {
            $transactionId = $response[RequestHelper::GET_TRANSACTION_ID];
        }
        return $transactionId;
    }
}
