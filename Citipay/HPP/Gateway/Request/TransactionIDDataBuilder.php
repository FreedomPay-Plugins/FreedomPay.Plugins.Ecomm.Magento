<?php

namespace Citipay\HPP\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Helper\Requests as RequestHelper;

/**
 * Builds magento transaction request - webhook
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
        $transactionId = $buildSubject[RequestHelper::GET_TRANSACTION_ID] ?? '';
        return [
            RequestHelper::GET_TRANSACTION_ID => $transactionId
        ];
    }
}
