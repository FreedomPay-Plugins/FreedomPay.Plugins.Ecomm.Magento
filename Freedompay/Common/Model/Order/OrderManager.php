<?php
declare(strict_types=1);

namespace Freedompay\Common\Model\Order;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory as OrderStatusHistoryFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Freedompay\Common\Logger\RedactData;

/**
 * Order management for payment methods
 */
class OrderManager
{
    public const ORDER_STATUS_PENDING           =   'pending';
    public const ORDER_STATE_PENDING            =   'new';
    public const ORDER_STATE_PROCESSING         =   'processing';
    public const ORDER_STATUS_PROCESSING        =   'processing';
    public const ORDER_STATUS_CANCELED          =   'canceled';
    public const ORDER_STATE_CANCELED           =   'canceled';
    public const ORDER_STATUS_PAYMENT_REVIEW    =   'payment_review';
    public const ORDER_STATE_PAYMENT_REVIEW     =   'payment_review';
    public const ORDER_STATE_PENDING_PAYMENT    =   'pending_payment';
    public const ORDER_STATUS_PENDING_PAYMENT   =   'pending_payment';
    public const ORDER_STATUS_COMPLETE          =   'complete';
    public const ORDER_STATE_COMPLETE           =   'complete';

    /**
     * @var OrderManagementInterface
     */
    protected OrderManagementInterface $orderManagement;

    /**
     * @var OrderStatusHistoryFactory
     */
    private OrderStatusHistoryFactory $orderStatusHistoryFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    protected TransactionRepositoryInterface $transactionRepository;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    protected OrderPaymentRepositoryInterface $orderPaymentRepository;

    /**
     * @var BuilderInterface
     */
    protected BuilderInterface $paymentTransactionBuilder;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var InvoiceService
     */
    protected InvoiceService $invoiceService;

    /**
     * @var InvoiceSender
     */
    protected InvoiceSender $invoiceSender;

    /**
     * @var Transaction
     */
    protected Transaction $transaction;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $orderFactory;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var RedactData
     */
    protected RedactData $redactData;

    /**
     * @param OrderManagementInterface $orderManagement
     * @param OrderStatusHistoryFactory $orderStatusHistoryFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param BuilderInterface $paymentTransactionBuilder
     * @param OrderFactory $orderFactory
     * @param CheckoutSession $checkoutSession
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     * @param SerializerInterface $serializer
     * @param RedactData $redactData
     */
    public function __construct(
        OrderManagementInterface        $orderManagement,
        OrderStatusHistoryFactory       $orderStatusHistoryFactory,
        OrderRepositoryInterface        $orderRepository,
        TransactionRepositoryInterface  $transactionRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        BuilderInterface                $paymentTransactionBuilder,
        OrderFactory                    $orderFactory,
        CheckoutSession                 $checkoutSession,
        InvoiceService                  $invoiceService,
        InvoiceSender                   $invoiceSender,
        Transaction                     $transaction,
        SerializerInterface             $serializer,
        RedactData                      $redactData
    ) {
        $this->orderManagement = $orderManagement;
        $this->orderStatusHistoryFactory = $orderStatusHistoryFactory;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->paymentTransactionBuilder = $paymentTransactionBuilder;
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transaction = $transaction;
        $this->serializer = $serializer;
        $this->redactData = $redactData;
    }

    /**
     * Get last orderId
     *
     * @return int
     */
    public function getLastOrderId(): int
    {
        return (int)$this->checkoutSession->getData('last_order_id');
    }

    /**
     * Get Order by order ID
     *
     * @param int $orderId
     * @return OrderInterface
     */
    public function getOrder(int $orderId)
    {
        if (!$this->order instanceof OrderInterface) {
            $order = $this->orderRepository->get($orderId);
            $this->setOrder($order);
        }
        return $this->order;
    }

    /**
     * Get customer id from order.
     *
     * @param int $orderId
     * @return int
     */
    public function getCustomerId(int $orderId): int
    {
        $order = $this->getOrder($orderId);
        return (int)$order->getCustomerId();
    }

    /**
     * Get Order by increment ID
     *
     * @param string $incrementId
     * @return OrderInterface|null
     */
    public function getOrderByIncrementId(string $incrementId): OrderInterface|null
    {
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        $this->setOrder($order);
        return $this->order;
    }

    /**
     * Set order to private variable
     *
     * @param OrderInterface $order
     * @return void
     */
    private function setOrder(OrderInterface $order): void
    {
        $this->order = $order;
    }

    /**
     * Cancel Order
     *
     * @param int $orderId
     * @param string $transactionId
     * @param string $freewayRequestId
     * @param string $state
     * @param string $status
     * @return void
     */
    public function cancelMagentoOrder(
        int    $orderId,
        string $transactionId,
        string $freewayRequestId = '',
        string $state = self::ORDER_STATE_CANCELED,
        string $status = self::ORDER_STATUS_CANCELED
    ): void {
        $this->orderManagement->cancel($orderId);

        if ($freewayRequestId) {
            $message = sprintf(
                'Payment failed/Suspected Fraud. Order has been canceled.
                TransactionId: %s, FreewayRequestId: %s',
                $transactionId,
                $freewayRequestId
            );
        } else {
            $message = sprintf(
                'Payment failed/Suspected Fraud. Order has been canceled.
                TransactionId: %s',
                $transactionId
            );
        }

        $this->updateOrderStatus(
            $message,
            $state,
            $status,
            $orderId
        );
    }

    /**
     * Cancel Order
     *
     * @param int $orderId
     * @param array<mixed> $response
     * @param string $state
     * @param string $status
     * @return void
     */
    public function cancelWebhookMagentoOrder(
        int    $orderId,
        array $response,
        string $state = self::ORDER_STATE_CANCELED,
        string $status = self::ORDER_STATUS_CANCELED
    ): void {
        $this->orderManagement->cancel($orderId);
        $this->updateOrderStatus(
            (string)$this->serializer->serialize($response),
            $state,
            $status,
            $orderId
        );
    }

    /**
     * Update Order History
     *
     * @param string $msg
     * @param string $state
     * @param string $status
     * @param int $orderId
     * @return void
     */
    public function updateOrderStatus(
        string $msg,
        string $state,
        string $status,
        int    $orderId
    ): void {
        $order = $this->getOrder($orderId);
        $order->setState($state)->setStatus($status);
        $this->orderRepository->save($order);
        $this->updateOrderHistory(
            $orderId,
            $status,
            $msg
        );
    }

    /**
     * Update order history data
     *
     * @param int $orderId
     * @param string $orderStatus
     * @param string $orderComment
     * @param bool $isCustomerNotified
     * @return void
     */
    public function updateOrderHistory(
        int    $orderId,
        string $orderStatus,
        string $orderComment,
        bool   $isCustomerNotified = true
    ): void {
        $orderStatusHistory = $this->orderStatusHistoryFactory->create()
            ->setParentId($orderId)
            ->setEntityName('order')
            ->setStatus($orderStatus)
            ->setComment($orderComment)
            ->setIsCustomerNotified($isCustomerNotified);
        $this->orderManagement->addComment($orderId, $orderStatusHistory);
    }

    /**
     * Process order
     *
     * @param array<mixed> $response
     * @param bool $isAuthOnly
     * @param bool $isCaptured
     * @param bool $isBillingAddressUpdateRequired
     * @param bool $manualReview
     * @return bool|int
     * @throws LocalizedException
     */
    public function processOrder(
        array $response,
        bool $isAuthOnly = false,
        bool $isCaptured = false,
        bool $isBillingAddressUpdateRequired = false,
        bool $manualReview = false
    ): bool|int {
        $orderIncrementId = $response['InvoiceNumber'];
        $checkoutTransactionId = $response['CheckoutTransactionId'];
        $transactionAmountFromResponse = $response['OriginalRequest']['TransactionTotal'];
        $paymentType = $response['PaymentType'];

        /** @var Order $order */
        $order = $this->getOrderByIncrementId($orderIncrementId);
        $orderId = (int)$order->getEntityId();
        $currencySymbol = $order->getBaseCurrency()->getCurrencySymbol();
        $transactionAmount = $currencySymbol . number_format($transactionAmountFromResponse, 2);

        if (!$orderId) {
            return false;
        }

        if ($isBillingAddressUpdateRequired) {
            $newAddress = $this->getChangedAddress($response, $isAuthOnly, $isCaptured);
            if (is_array($newAddress)) {
                $this->updateBillingAddress($order, $newAddress);
            }
        }

        //Update payment information
        $this->updateOrderPayment($order, $response);
        if ($manualReview) {
            return false;
        }
        //If auth only transaction
        if ($isAuthOnly) {
            $state = self::ORDER_STATE_PENDING;
            $status = self::ORDER_STATUS_PENDING;
            //Add transaction entry
            $this->addNewTransactionEntry($order, $checkoutTransactionId, 'authorization');
            $this->updateOrderStatus(
                sprintf(
                    'Payment authorized. TransactionId: %s, Authorized amount: %s, Payment type: %s',
                    $checkoutTransactionId,
                    $transactionAmount,
                    $paymentType
                ),
                $state,
                $status,
                $orderId
            );
            return $orderId;
        } elseif ($isCaptured) {
            if ($this->isOrderVirtual($order)) {
                $state = self::ORDER_STATE_COMPLETE;
                $status = self::ORDER_STATUS_COMPLETE;
            } else {
                $state = self::ORDER_STATE_PROCESSING;
                $status = self::ORDER_STATUS_PROCESSING;
            }
            //Generate Invoice
            $this->generateInvoice($order, $checkoutTransactionId);
            //Add transaction entry
            $this->addNewTransactionEntry($order, $checkoutTransactionId, 'capture');
            //Add order comment
            $this->updateOrderStatus(
                sprintf(
                    'Payment captured. TransactionId: %s Captured amount: %s',
                    $checkoutTransactionId,
                    $transactionAmount
                ),
                $state,
                $status,
                $orderId
            );
            return $orderId;
        }
        return false;
    }

    /**
     * Check if the order contains only virtual products
     *
     * @param Order $order
     * @return bool
     */
    public function isOrderVirtual(Order $order): bool
    {
        foreach ($order->getAllItems() as $item) {
            $product = $item->getProduct(); // Load the product
            if ($product === null || !$product->getIsVirtual()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if order is valid
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isValidOrder(array $response): bool
    {
        $apiTransactionAmount = $response['OriginalRequest']['TransactionTotal'];
        $incrementId = $response['InvoiceNumber'];
        /** @var Order $order */
        $order = $this->getOrderByIncrementId($incrementId);
        $orderTotalAmount = $order->getBaseGrandTotal();

        return $apiTransactionAmount == $orderTotalAmount;
    }

    /**
     * Generates invoice
     *
     * @param Order $order
     * @param string $transactionId
     * @return void
     * @throws LocalizedException
     */
    public function generateInvoice(Order $order, string $transactionId = ''): void
    {
        if (!$order->getEntityId()) {
            throw new LocalizedException(__('The order no longer exists'));
        }

        if (!$order->canInvoice()) {
            throw new LocalizedException(
                __('You can\'t create an invoice with this order')
            );
        }
        $invoice = $this->invoiceService->prepareInvoice($order);
        if (!$invoice->getTotalQty()) {
            throw new LocalizedException(
                __('You can\'t create an invoice without products')
            );
        }
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE); /** @phpstan-ignore-line */
        if ($transactionId) {
            $invoice->setTransactionId($transactionId);
        }
        $invoice->register();
        $invoice->getOrder()->setCustomerNoteNotify(0);
        $invoice->getOrder()->setIsInProcess(true);/** @phpstan-ignore-line */

        $transactionSave =
            $this->transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
        $transactionSave->save();
        $this->invoiceSender->send($invoice);
        $order->addCommentToStatusHistory(
            (string)__(
                'We\'ve notified the customer that the "#%1" invoice has been created',
                $invoice->getId()
            )
        )->setIsCustomerNotified(1);
    }

    /**
     * Add new payment transaction corresponding to the operation.
     *
     * @param Order $order
     * @param string $transactionId
     * @param string $transactionType
     * @return void
     */
    private function addNewTransactionEntry(Order $order, string $transactionId, string $transactionType): void
    {
        /** @var Payment $payment */
        $payment = $order->getPayment();

        /** @var Payment\Transaction $transaction */
        $transaction = $this->paymentTransactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($transactionId)
            ->setAdditionalInformation(
                [RequestHelper::CHECKOUT_TRANSACTION_ID, $transactionId]
            )->setFailSafe(true)
            ->build($transactionType);
        $this->transactionRepository->save($transaction);
    }

    /**
     * Update order payment information
     *
     * @param Order $order
     * @param array<mixed> $response
     * @return void
     * @throws LocalizedException
     */
    private function updateOrderPayment(Order $order, array $response): void
    {
        $transactionId = $response[RequestHelper::CHECKOUT_TRANSACTION_ID];
        $redactedResponse = (array)$this->serializer->unserialize($this->redactData->redact($response));

        /** @var Payment $payment */
        $payment = $order->getPayment();
        $payment->setLastTransId($transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setAdditionalInformation(RequestHelper::GET_TRANSACTION_RESPONSE, $redactedResponse);
        $payment->setIsTransactionClosed(false);
        $this->orderPaymentRepository->save($payment);
        $this->orderRepository->save($order);
    }

    /**
     * Update order payment information
     *
     * @param Order $order
     * @param array<mixed> $response
     * @param string $transactionId
     * @return void
     * @throws LocalizedException
     */
    private function updateWebhookOrderPayment(Order $order, array $response, string $transactionId): void
    {
        /** @var Payment $payment */
        $payment = $order->getPayment();
        $payment->setLastTransId($transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setAdditionalInformation(RequestHelper::GET_TRANSACTION_RESPONSE, $response);
        $payment->setIsTransactionClosed(false);
        $this->orderPaymentRepository->save($payment);
        $this->orderRepository->save($order);
    }

    /**
     * Update billing address
     *
     * @param Order $order
     * @param array<mixed> $newAddress
     * @return void
     */
    public function updateBillingAddress(
        Order $order,
        array $newAddress
    ): void {
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $billingAddress->setFirstname($newAddress['Name']);
            $billingAddress->setLastname('');
            $billingAddress->setCity($newAddress['City']);
            $billingAddress->setCountryId($newAddress['CountryCode']);
            $billingAddress->setPostcode($newAddress['PostalCode']);
            $billingAddress->setRegion($newAddress['State']);
            $billingAddress->setStreet([$newAddress['Street1'], $newAddress['Street2']]);
            $this->orderRepository->save($order);
        }
    }

    /**
     * Check if address has changed
     *
     * @param array<mixed> $response
     * @param bool $isAuthOnly
     * @param bool $isCaptured
     * @return bool|array<mixed>
     */
    public function getChangedAddress(
        array $response,
        bool $isAuthOnly,
        bool $isCaptured
    ):bool|array {
        $originalRequest = $response['OriginalRequest'];
        $originalAddress = $originalRequest['BillingAddress'];
        $newAddress = [];

        if ($isAuthOnly) {
            $newAddress = $response['AuthResponse']['BillingAddress'];
        } elseif ($isCaptured) {
            $newAddress = $response['CaptureResponse']['BillingAddress'];
        }

        if ($newAddress) {
            if (!$originalAddress) {
                return $newAddress;
            }
            $isAddressChanged = array_diff_assoc($originalAddress, $newAddress);
            if (count($isAddressChanged)) {
                return $newAddress;
            }
        }

        return false;
    }

    /**
     * Process authorize from webhook
     *
     * @param array<mixed> $response
     * @param Order $order
     * @param string $transactionId
     * @param string $paymentMethodType
     * @param float $transactionAmount
     * @return int
     * @throws LocalizedException
     */
    public function processAuthorize(
        array $response,
        Order $order,
        string $transactionId,
        string $paymentMethodType,
        float $transactionAmount
    ): int {
        $state = self::ORDER_STATE_PENDING;
        $status = self::ORDER_STATUS_PENDING;
        $orderId = (int)$order->getEntityId();
        $this->updateWebhookOrderPayment($order, $response, $transactionId);
        //Add transaction entry
        $this->addNewTransactionEntry($order, $transactionId, 'authorization');
        $this->updateOrderStatus(
            sprintf(
                'Payment authorized. TransactionId: %s, Authorized amount: %s, Payment Method: %s',
                $transactionId,
                $transactionAmount,
                $paymentMethodType
            ),
            $state,
            $status,
            $orderId
        );
        return $orderId;
    }

    /**
     * Update the order status to payment review - webhook
     *
     * @param array<mixed> $response
     * @param Order $order
     * @return void
     * @throws LocalizedException
     */
    public function processPaymentReview(array $response, Order $order): void
    {
        $state = self::ORDER_STATE_PAYMENT_REVIEW;
        $status = self::ORDER_STATE_PAYMENT_REVIEW;
        $orderId = (int)$order->getEntityId();
        $this->updateOrderStatus(
            (string)$this->serializer->serialize($response),
            $state,
            $status,
            $orderId
        );
    }

    /**
     * Add comment to order
     *
     * @param int $orderId
     * @param string $comment
     * @param bool $notify
     * @return void
     */
    public function addOrderComment(int $orderId, string $comment, bool $notify = false): void
    {
        /** @var Order $order */
        $order = $this->getOrder($orderId);
        $order->addCommentToStatusHistory(
            $comment,
            false,
            $notify
        );
        $this->orderRepository->save($order);
    }
}
