<?php

namespace Freedompay\Common\Gateway\Http\Client;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Execute API request
 */
class MagentoTransactionClient implements ClientInterface
{
    /**
     * Places request to gateway. Returns result as array
     *
     * @param TransferInterface $transferObject
     * @return array<mixed>
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $headers = $transferObject->getHeaders();

        if (isset($headers[RequestHelper::GET_TRANSACTION_ID])) {
            return [RequestHelper::GET_TRANSACTION_ID => $headers[RequestHelper::GET_TRANSACTION_ID]];
        }

        return [];
    }
}
