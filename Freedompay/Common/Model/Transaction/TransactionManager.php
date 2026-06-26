<?php

namespace Freedompay\Common\Model\Transaction;

use Exception;
use Freedompay\Common\Helper\Requests as RequestHelper;
use Freedompay\Common\Model\Api\RequestManager;
use Freedompay\Common\Model\Api\ResponseManager;
use Freedompay\Common\Model\Cart\QuoteManager;
use Freedompay\Common\Model\Order\OrderManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\ResultInterface;

/**
 *
 * Manages transactions
 */
class TransactionManager
{
    public const INVALID_FORMAT         = 'Response format is invalid';
    public const INVALID_TRANSACTION
        = 'Transaction declined or Auth/Capture decision is not correctly set in the response';
    public const INVALID_ORDER_TOTAL    = 'Amount mismatch noticed in the response';
    /**
     * @var OrderManager
     */
    private OrderManager $orderManager;

    /**
     * @var QuoteManager
     */
    private QuoteManager $quoteManager;

    /**
     * @var RequestManager
     */
    private RequestManager $requestManager;

    /**
     * @var ResponseManager
     */
    private ResponseManager $responseManager;

    /**
     * @param OrderManager $orderManager
     * @param QuoteManager $quoteManager
     * @param RequestManager $requestManager
     * @param ResponseManager $responseManager
     */
    public function __construct(
        OrderManager      $orderManager,
        QuoteManager      $quoteManager,
        RequestManager    $requestManager,
        ResponseManager   $responseManager
    ) {

        $this->orderManager         = $orderManager;
        $this->quoteManager         = $quoteManager;
        $this->requestManager       = $requestManager;
        $this->responseManager      = $responseManager;
    }

    /**
     * Process transaction
     *
     * @param array<mixed> $params
     * @param string $method
     * @param bool $isAccountSaveCardAction
     * @return array<mixed>|bool
     * @throws LocalizedException
     */
    public function processTransaction(array $params, string $method, bool $isAccountSaveCardAction): array|bool
    {
        $transactionId = $params['transid'] ?? null;
        $lastOrderId = $this->orderManager->getLastOrderId();
        $processStatus = ['status' => false, 'isAccountSaveCardAction' => false, 'errorMessage' => ''];
        try {
            //Call Get transaction.
            $transactionResponse = $this->doGetTransactionWithRetry($params);
            //If the response is not in correct format, return
            if (!is_array($transactionResponse)) {
                $processStatus['errorMessage'] = self::INVALID_FORMAT;
                $this->processFailedResponse($params);
                return $processStatus;
            }
            //If isAccountSaveCardAction transaction
            if ($isAccountSaveCardAction) {
                return $this->saveCardFromAccount($transactionId, $transactionResponse, $method);
            }
            //Log FreewayRequestID and FreewayResponseCode from Response to order comment
            $freewayData = $this->responseManager->getFreewayDataFromResponse($transactionResponse);
                $freewayComment = sprintf(
                    'Order Placement: Freeway RequestId: %s, Freeway ResponseCode: %s',
                    $freewayData['freewayRequestId'],
                    $freewayData['freewayResponseCode']
                );
                $this->orderManager->addOrderComment($lastOrderId, $freewayComment);
            //Check if auth/capture decision is correctly set in the response. If not, return.
            if (!$this->responseManager->isValidResponse($transactionResponse)) {
                $processStatus['errorMessage'] = self::INVALID_TRANSACTION;
                $this->processFailedResponse($params, $transactionResponse);
                return $processStatus;
            }

            if ($lastOrderId) {
                //Validate transaction amount from the response with the order amount.
                $isValidTransaction = $this->orderManager->isValidOrder($transactionResponse);
                if (!$isValidTransaction) {
                    $this->orderManager->updateOrderStatus(
                        sprintf(
                            'Payment failed/Suspected Fraud. Amount mismatch noticed. TransactionId: %s',
                            $transactionId
                        ),
                        OrderManager::ORDER_STATE_PAYMENT_REVIEW,
                        OrderManager::ORDER_STATUS_PAYMENT_REVIEW,
                        $lastOrderId
                    );
                    $processStatus['errorMessage'] = self::INVALID_ORDER_TOTAL;
                    $this->processFailedResponse($params, $transactionResponse);
                    return $processStatus;
                }

                $isAuthOnly = $this->responseManager->isAuthOnly($transactionResponse);
                $isCaptured = $this->responseManager->isCaptured($transactionResponse);
                $isBillingAddressUpdateRequired =
                    $this->responseManager->isBillingAddressUpdateRequired($transactionResponse);

                //Process order
                $lastProcessedOrderId = $this->processOrder(
                    $transactionResponse,
                    $isAuthOnly,
                    $isCaptured,
                    $isBillingAddressUpdateRequired
                );

                if ($lastProcessedOrderId) {
                    $customerId = $this->orderManager->getCustomerId($lastProcessedOrderId);
                    //Disable current quote
                    $this->quoteManager->disableQuote();
                    //Save token information, if available
                    if ($this->isTokenAvailable($transactionResponse)) {
                        $this->saveCardFromResponse($transactionResponse, $customerId, $method);
                    }
                    $processStatus = ['status' => true, 'isAccountSaveCardAction' => false];
                } else {
                    $this->processFailedResponse($params, $transactionResponse);
                    return $processStatus;
                }
            }
            return $processStatus;
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Calls GetTransaction and retry if fails
     *
     * @param array<mixed> $requestParams
     * @return array|bool|ResultInterface|mixed[]|null
     * @throws LocalizedException
     */
    public function doGetTransactionWithRetry(array $requestParams): array|ResultInterface|bool|null
    {
        $retryCount = 1;
        //If getTransaction() call is failed, plug-in will do a retry
        do {
            $transactionResponse = $this->requestManager->process($requestParams);
            $retryCount++;
        } while (!is_array($transactionResponse)
        && $retryCount <= RequestHelper::GET_TRANSACTION_RETRY_COUNT
        );

        return $transactionResponse;
    }

    /**
     * Process failed transactions
     *
     * @param array<mixed> $params
     * @param array<mixed> $response
     * @return void
     */
    public function processFailedResponse(array $params, array $response = []): void
    {
        $freewayRequestId = '';
        $lastOrderId = $this->orderManager->getLastOrderId();
        $transactionId = $params['transid'] ?? null;
        if ($response) {
            $freewayRequestId = $this->responseManager->getFreewayRequestId($response);
        }
        $this->orderManager->cancelMagentoOrder($lastOrderId, $transactionId, $freewayRequestId);
    }

    /**
     * Method to save card from checkout
     *
     * @param array<mixed> $transactionResponse
     * @param int $customerId
     * @param string $method
     * @return bool
     */
    public function saveCardFromResponse(array $transactionResponse, int $customerId, string $method): bool
    {
        return false;
    }

    /**
     * Method to save card from my account
     *
     * @param string $transactionId
     * @param array<mixed> $transactionResponse
     * @param string $method
     * @return array<mixed>
     */
    public function saveCardFromAccount(string $transactionId, array $transactionResponse, string $method): array
    {
        return [];
    }

    /**
     * Method to check if token values are present in response
     *
     * @param array<mixed> $transactionResponse
     * @return bool
     */
    public function isTokenAvailable(array $transactionResponse): bool
    {
        return false;
    }

    /**
     * Process Order
     *
     * @param array<mixed> $transactionResponse
     * @param bool $isAuthOnly
     * @param bool $isCaptured
     * @param bool $isBillingAddressUpdateRequired
     * @return int
     * @throws LocalizedException
     */
    public function processOrder(
        array $transactionResponse,
        bool $isAuthOnly,
        bool $isCaptured,
        bool $isBillingAddressUpdateRequired,
    ) : int {
        return (int)$this->orderManager->processOrder(
            $transactionResponse,
            $isAuthOnly,
            $isCaptured,
            $isBillingAddressUpdateRequired
        );
    }
}
