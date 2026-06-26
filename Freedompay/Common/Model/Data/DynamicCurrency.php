<?php
namespace Freedompay\Common\Model\Data;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Freedompay\Common\Model\Api\ResponseManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

/**
 *
 * Get dynamic currency information of the order
 */
class DynamicCurrency
{
    /**
     * @var ResponseManager
     */
    private ResponseManager $responseManager;

    /**
     * DynamicCurrency constructor.
     *
     * @param ResponseManager $responseManager
     */
    public function __construct(
        ResponseManager $responseManager
    ) {
        $this->responseManager = $responseManager;
    }

    /**
     * Get html of DCC totals comments block
     *
     * @param Order $order
     * @return array<mixed>
     */
    public function getDCCData(Order $order): array
    {
        $dccData = [];
        /** @var Payment $payment */
        $payment = $order->getPayment();
        $additionalInformation =$payment->getAdditionalInformation();
        $getTransactionResponse = $additionalInformation[RequestHelper::GET_TRANSACTION_RESPONSE] ?? null;

        if ($getTransactionResponse) {
            $dccData = $this->responseManager->checkAndGetDCCData($getTransactionResponse);
        }

        return $dccData;
    }
}
