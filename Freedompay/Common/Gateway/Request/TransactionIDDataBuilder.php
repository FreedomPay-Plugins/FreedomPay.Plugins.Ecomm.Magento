<?php

namespace Freedompay\Common\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Helper\Requests as RequestHelper;

/**
 * Class TransactionIDDataBuilder
 * Builds magento transaction request
 */
class TransactionIDDataBuilder implements BuilderInterface
{
    /**
     * Builds transaction id
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $transactionId = $buildSubject['transactionId'] ??
            $payment->getPayment()->getAdditionalInformation(RequestHelper::TRANSACTION_ID);
        return [
            RequestHelper::GET_TRANSACTION_ID => $transactionId
        ];
    }
}
