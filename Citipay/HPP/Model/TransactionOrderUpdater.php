<?php
declare(strict_types=1);

namespace Citipay\HPP\Model;

use Citipay\HPP\Gateway\Command\WebhookGatewayCommand;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Freedompay\Common\Model\Order\OrderManager;
use Citipay\HPP\Helper\Constants;
use Citipay\HPP\Logger\Logger;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Freedompay\Common\Helper\Requests;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory as PaymentCollectionFactory;

/**
 * class TransactionOrderUpdater - Update order based on notification
 */
class TransactionOrderUpdater
{
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var OrderManager
     */
    protected OrderManager $orderManager;

    /**
     * @var NotificationManager
     */
    private NotificationManager $notificationManager;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var PriceCurrencyInterface
     */
    protected PriceCurrencyInterface $priceCurrency;

    /**
     * @var CommandPoolInterface
     */
    protected CommandPoolInterface $commandPool;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $transactionCollectionFactory;

    /**
     * @var PaymentCollectionFactory
     */
    private PaymentCollectionFactory $paymentCollectionFactory;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManager $orderManager
     * @param NotificationManager $notificationManager
     * @param Logger $logger
     * @param PriceCurrencyInterface $priceCurrency
     * @param CommandPoolInterface $commandPool
     * @param CollectionFactory $transactionCollectionFactory
     * @param PaymentCollectionFactory $paymentCollectionFactory
     */
    public function __construct(
        OrderRepositoryInterface      $orderRepository,
        OrderManager                  $orderManager,
        NotificationManager           $notificationManager,
        Logger                        $logger,
        PriceCurrencyInterface $priceCurrency,
        CommandPoolInterface $commandPool,
        CollectionFactory $transactionCollectionFactory,
        PaymentCollectionFactory $paymentCollectionFactory
    ) {
        $this->orderRepository         = $orderRepository;
        $this->orderManager            = $orderManager;
        $this->notificationManager     = $notificationManager;
        $this->logger                  = $logger;
        $this->commandPool = $commandPool;
        $this->priceCurrency = $priceCurrency;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
    }

    /**
     * Process Notification from cron
     *
     * @param array<mixed> $notificationContent
     * @param string $notificationDate
     * @param int $retryCount
     * @return LocalizedException|string|null
     * @throws ClientException
     * @throws CommandException
     * @throws ConverterException
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function processNotification(
        array $notificationContent,
        string $notificationDate,
        int $retryCount
    ): LocalizedException|string|null {
        $transactionIdNotification = $this->notificationManager->getTransactionId($notificationContent);
        $getResponse = $this->getWebhookGetResponse($transactionIdNotification) ?? [];
        $status = $orderIncrementId = $transactionId = null;
        if (is_array($getResponse)) {
            $status = $this->notificationManager->getStatus($getResponse) ?? null;
            $orderIncrementId = $this->notificationManager->getOrderIncrementId($getResponse) ?? null;
            $transactionId = $this->notificationManager->getTransactionIdFromResponse($getResponse) ?? null;
        }
        if (!$getResponse || !$orderIncrementId || !$transactionId) {
            $orderId = (int) $this->getOrderIdByTransactionId($transactionIdNotification);
            /** @var Order $order */
            $order = $this->orderRepository->get($orderId);
        } else {
            /** @var Order $order */
            $order = $this->orderManager->getOrderByIncrementId($orderIncrementId);
        }
        $orderId = (int)$order->getId();
        if ($orderId) {
            $this->orderManager->addOrderComment(
                $orderId,
                $this->getNotificationDetails($transactionId, $getResponse, $notificationDate)
            );
        }
        /** @phpstan-ignore-next-line */
        $getResponseApiCallStatus = $getResponse['status'] ?? null;
        if ($getResponseApiCallStatus != Requests::RESPONSE_SUCCESS_CODE) {
            if ($retryCount >= Requests::WEBHOOK_GET_TRANSACTION_RETRY_COUNT) {
                $this->orderManager->addOrderComment(
                    $orderId,
                    'Out of Band Notification Details, Error: No Response Received.'
                );
                return Constants::NOTIFICATION_STATUS_INVALID;
            }
            return Constants::NOTIFICATION_STATUS_PROCESSING_ERROR;
        }
        if (!is_array($getResponse)) {
            return Constants::NOTIFICATION_STATUS_PROCESSING_ERROR;
        }
        if (!$orderIncrementId || !$transactionId) {
            return Constants::NOTIFICATION_STATUS_INVALID;
        }
        try {
            $transactionAmount = (float)$this->notificationManager->getTransactionAmount($getResponse);
            return $this->processOrder(
                $status,
                $getResponse,
                $order,
                $transactionId,
                $transactionAmount,
            );
        } catch (Exception) {
            $this->logger->info('Error occurred while processing notification');
            throw new LocalizedException(
                __('Error occurred while processing notification')
            );
        }
    }

    /**
     * Get transaction using transaction ID from webhook response
     *
     * @param string $transactionId
     * @return ResultInterface|null|array<mixed>
     * @throws CommandException|ConverterException|ClientException|NotFoundException
     */
    private function getWebhookGetResponse(string $transactionId): ResultInterface|null|array
    {
        $paymentCommandParams = [
            Requests::GET_TRANSACTION_ID => $transactionId
        ];
        /** @var WebhookGatewayCommand $gatewayCommand */
        $gatewayCommand = $this->commandPool->get(Requests::GET_WEBHOOK_TRANSACTION);
        return $gatewayCommand->execute($paymentCommandParams);
    }

    /**
     * Process authorize from notification
     *
     * @param array<mixed> $notificationContent
     * @param Order $order
     * @param string $transactionId
     * @param string $paymentMethodType
     * @param float $transactionAmount
     * @return string|null
     * @throws LocalizedException
     */
    public function processAuthorize(
        array $notificationContent,
        Order $order,
        string $transactionId,
        string $paymentMethodType,
        float $transactionAmount
    ): null|string {
        if ($order->getPayment()) {
            $this->orderManager->processAuthorize(
                $notificationContent,
                $order,
                $transactionId,
                $paymentMethodType,
                $transactionAmount
            );
            return Constants::NOTIFICATION_STATUS_SUCCESS;
        }
        return Constants::NOTIFICATION_STATUS_PROCESSING_ERROR;
    }

    /**
     * Process the notification action.
     *
     * @param string|null $status
     * @param array<mixed> $response
     * @param Order $order
     * @param string $transactionId
     * @param float|null $transactionAmount
     * @return LocalizedException|string|null
     * @throws LocalizedException
     */
    protected function processOrder(
        string|null $status,
        array $response,
        Order $order,
        string $transactionId,
        float|null $transactionAmount
    ): LocalizedException|string|null {
        if ($status == Constants::STATUS_ACCEPT &&
            ($order->getState() === OrderManager::ORDER_STATE_PENDING_PAYMENT
                || $order->getState() === OrderManager::ORDER_STATE_PAYMENT_REVIEW)) {
            $orderGrandTotal = $this->priceCurrency->convertAndRound($order->getGrandTotal());
            if (!$transactionAmount || !$orderGrandTotal) {
                return Constants::NOTIFICATION_STATUS_INVALID;
            }
            $transactionAmount = $this->priceCurrency->convertAndRound($transactionAmount);
            if ($transactionAmount !== $orderGrandTotal) {
                return Constants::NOTIFICATION_STATUS_INVALID;
            }
            return $this->processAuthorize(
                $response,
                $order,
                $transactionId,
                'authorization',
                $transactionAmount,
            );

        } elseif (($status !== Constants::STATUS_ACCEPT)
            && $order->getState() !== Order::STATE_CANCELED) {
            $this->orderManager->cancelWebhookMagentoOrder((int)$order->getId(), $response);
            return Constants::NOTIFICATION_STATUS_SUCCESS;
        } else {
            return Constants::NOTIFICATION_STATUS_INVALID;
        }
    }

    /**
     * Add order comment for every notification received
     *
     * @param string|null $transactionId
     * @param mixed $getResponse
     * @param string|null $notificationDate
     * @return string
     */
    public function getNotificationDetails(
        string|null $transactionId,
        mixed $getResponse,
        string|null $notificationDate
    ) : string {
        $statusFlag = $getResponse['CreditApplicationInformation']['StatusFlag'] ?? 'null';
        $freewayRequestId = $getResponse['AuthResponse']['FreewayResponse']['FreewayRequestId'] ?? 'null';
        $decision = $getResponse['AuthResponse']['FreewayResponse']['Decision'] ?? 'null';
        return sprintf(
            'Date: %s,
            Out of Band Notification Details,
            Transaction ID: %s,
            Status Flag: %s,
            Freeway RequestId: %s,
            Freeway Decision: %s ',
            $notificationDate,
            $transactionId,
            $statusFlag,
            $freewayRequestId,
            $decision
        );
    }

    /**
     * Get order id from Transaction ID
     *
     * @param string $transactionId
     * @return int|null
     */
    public function getOrderIdByTransactionId(string $transactionId): int|null
    {
        $transactionCollection = $this->transactionCollectionFactory->create();
        $transactionCollection->addFieldToFilter('txn_id', $transactionId);
        /**
         * @var Transaction $transaction
         */
        $transaction = $transactionCollection->getFirstItem();
        if ($transaction->getId()) {
            return (int)$transaction->getOrderId();
        } else {
            $paymentCollection = $this->paymentCollectionFactory->create();
            $paymentCollection->addFieldToFilter('last_trans_id', $transactionId);
            /**
             * @var Payment $payment
             */
            $payment = $paymentCollection->getFirstItem();
            if ($payment->getId()) {
                return (int)$payment->getParentId();
            }
        }
        return null;
    }
}
