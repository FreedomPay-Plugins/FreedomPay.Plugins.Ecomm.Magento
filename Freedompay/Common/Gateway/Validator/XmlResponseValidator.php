<?php

namespace Freedompay\Common\Gateway\Validator;

use Freedompay\Common\Helper\Requests;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Freedompay\Common\Model\Order\OrderManager;

/**
 * Class ResponseValidator
 * Freedompay Xml Response Validator
 */
class XmlResponseValidator extends AbstractValidator
{
    private const REASON_CODE_VOIDED = '247';

    /**
     * @var string
     */
    private string $transactionType;

    /**
     * @var OrderManager
     */
    private OrderManager $orderManager;

    /**
     * @param OrderManager $orderManager
     * @param ResultInterfaceFactory $resultFactory
     * @param string $transactionType
     */
    public function __construct(
        OrderManager $orderManager,
        ResultInterfaceFactory $resultFactory,
        string $transactionType = ''
    ) {
        $this->orderManager = $orderManager;
        parent::__construct($resultFactory);
        $this->transactionType = $transactionType;
    }

    /**
     * Validates Capture/Refund/Cancel transaction response
     *
     * @param array<mixed> $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $responseXML = SubjectReader::readResponse($validationSubject);
        $paymentDO = SubjectReader::readPayment($validationSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();

        $submitResult = $responseXML['Body']['SubmitResponse']['SubmitResult'];
        $responseDecision = $submitResult['decision'];

        $message = [
            __(
                'Something went wrong while processing the  %1 API request',
                $this->transactionType
            )
        ];

        return match ($this->transactionType) {
            Requests::SERVICE_VOID      =>  $this->validateVoidTransaction($responseDecision, $submitResult, $order),
            Requests::SERVICE_CAPTURE   =>  $this->validateCaptureTransaction($responseDecision, $submitResult, $order),
            Requests::SERVICE_REFUND    =>  $this->validateRefundTransaction($responseDecision, $submitResult, $order),
            default =>  $this->handleFailedResponse($submitResult, $order, $message)
        };
    }

    /**
     * Validates Void Transaction Response
     *
     * @param string $status
     * @param array<mixed> $result
     * @param Order $order
     * @return ResultInterface
     */
    public function validateVoidTransaction(string $status, array $result, Order $order): ResultInterface
    {
        if ($status == Requests::STATUS_ACCEPT
            || $result['reasonCode'] == self::REASON_CODE_VOIDED) {
            return $this ->createResult(true);
        } else {
            return $this->handleFailedResponse($result, $order, [$result['voidReply']['processorResponseMessage']]);
        }
    }

    /**
     * Validates Capture Transaction Response
     *
     * @param string $status
     * @param array<mixed> $result
     * @param Order $order
     * @return ResultInterface
     */
    public function validateCaptureTransaction(string $status, array $result, Order $order): ResultInterface
    {
        if (isset($result['torReply']) && $status == Requests::STATUS_ACCEPT) {
            return $this->createResult(
                false,
                [
                    __(
                        '%1 was unsuccessful due to Freeway Service Timeout',
                        ucfirst($this->transactionType)
                    )
                ]
            );
        } elseif ($status == Requests::STATUS_ACCEPT) {
            return $this ->createResult(true);
        } else {
            return $this->handleFailedResponse($result, $order);
        }
    }

    /**
     * Validates Refund Transaction Response
     *
     * @param string $status
     * @param array<mixed> $result
     * @param Order $order
     * @return ResultInterface
     */
    public function validateRefundTransaction(string $status, array $result, Order $order): ResultInterface
    {
        if (isset($result['torReply']) && $status == Requests::STATUS_ACCEPT) {
            return $this->createResult(
                false,
                [
                    __(
                        '%1 was unsuccessful due to Freeway Service Timeout',
                        ucfirst($this->transactionType)
                    )
                ]
            );
        } elseif ($status == Requests::STATUS_ACCEPT) {
            return $this ->createResult(true);
        } else {
            return $this->handleFailedResponse($result, $order);
        }
    }

    /**
     * Update the requestId when action fails
     *
     * @param array<mixed> $response
     * @param Order $order
     * @param array<mixed> $message
     * @return ResultInterface
     */
    private function handleFailedResponse(array $response, Order $order, array $message = []): ResultInterface
    {
        $freewayRequestId = $response['requestID'];
        $this->orderManager->updateOrderHistory(
            $order->getEntityId(),
            (string)$order->getStatus(),
            sprintf(' Transaction failed. FreewayRequestId for %s: %s', $this->transactionType, $freewayRequestId)
        );

        return $this->createResult(
            false,
            $message
        );
    }
}
