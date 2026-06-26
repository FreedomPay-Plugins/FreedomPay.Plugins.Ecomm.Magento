<?php

namespace Freedompay\Common\Gateway\Response;

use Freedompay\Common\Helper\Requests;
use Freedompay\Common\Model\Order\OrderManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class XmlResponseHandler
 * Freedompay/Citi Pay SOAP Transaction Response Handler
 */
class XmlResponseHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * @var string
     */
    private string $transactionType;

    /**
     * @var OrderManager
     */
    private OrderManager $orderManager;

    /**
     * @param SubjectReader $subjectReader
     * @param OrderManager $orderManager
     * @param string $transactionType
     */
    public function __construct(
        SubjectReader $subjectReader,
        OrderManager  $orderManager,
        string        $transactionType = ''
    ) {
        $this->subjectReader = $subjectReader;
        $this->transactionType = $transactionType;
        $this->orderManager = $orderManager;
    }
    /**
     * Handles response
     *
     * @param array<mixed> $handlingSubject
     * @param array<mixed> $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();
        $result = $response['Body']['SubmitResponse']['SubmitResult'];
        $freewayRequestId = $result['requestID'];
        switch ($this->transactionType) {
            case Requests::SERVICE_VOID:
                $payment->setAdditionalInformation(
                    Requests::KEY_FREEWAY_VOID_RESPONSE,
                    $result
                );
                break;
            case Requests::SERVICE_CAPTURE:
                $payment->setAdditionalInformation(
                    Requests::KEY_FREEWAY_CAPTURE_RESPONSE,
                    $result
                );
                break;
            case Requests::SERVICE_REFUND:
                $payment->setAdditionalInformation(
                    Requests::KEY_FREEWAY_REFUND_RESPONSE,
                    $result
                );
                break;
        };
        $this->orderManager->updateOrderHistory(
            $order->getEntityId(),
            (string)$order->getStatus(),
            sprintf(' FreewayRequestId for %s: %s', $this->transactionType, $freewayRequestId)
        );
    }
}
