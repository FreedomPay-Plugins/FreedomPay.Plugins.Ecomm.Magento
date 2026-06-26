<?php
namespace Freedompay\HPP\Model\Transaction;

use Freedompay\HPP\Model\Api\ResponseManager;
use Freedompay\Common\Model\Cart\QuoteManager;
use Freedompay\Common\Model\Order\OrderManager;
use Freedompay\Common\Model\Transaction\CustomTransaction;
use Freedompay\HPP\Model\Data\SavedCard as SavedCardData;
use Freedompay\HPP\Model\GetTransaction as FPRequestManager;
use Magento\Framework\Exception\LocalizedException;

/**
 *
 * Manages Freedompay transactions
 */
class TransactionManager extends \Freedompay\Common\Model\Transaction\TransactionManager
{
    /**
     * @var SavedCardData
     */
    private SavedCardData $savedCardData;

    /**
     * @var ResponseManager
     */
    private ResponseManager $responseManager;

    /**
     * @var CustomTransaction
     */
    private CustomTransaction $customTransaction;

    /**
     * @param OrderManager $orderManager
     * @param QuoteManager $quoteManager
     * @param FPRequestManager $fpRequestManager
     * @param ResponseManager $responseManager
     * @param CustomTransaction $customTransaction
     * @param SavedCardData $savedCardData
     */
    //phpcs:disable
    public function __construct(
        OrderManager $orderManager,
        QuoteManager $quoteManager,
        FPRequestManager $fpRequestManager,
        ResponseManager $responseManager,
        CustomTransaction $customTransaction,
        SavedCardData $savedCardData
    ) {
        $this->savedCardData = $savedCardData;
        $this->responseManager      = $responseManager;
        $this->customTransaction    = $customTransaction;
        parent::__construct(
            $orderManager,
            $quoteManager,
            $fpRequestManager,
            $responseManager
        );
    }

    /**
     * Method to save card from checkout
     *
     * @param array<mixed> $transactionResponse
     * @param int $customerId
     * @param string $method
     * @return bool
     * @throws LocalizedException
     */
    public function saveCardFromResponse(array $transactionResponse, int $customerId, string $method): bool
    {
        $this->savedCardData->savePaymentCard($transactionResponse, $customerId, $method);
        return true;
    }

    /**
     * Method to save card from my account
     *
     * @param string $transactionId
     * @param array<mixed> $transactionResponse
     * @param string $method
     * @return array<mixed>|mixed[]|true[]
     * @throws LocalizedException
     */
    public function saveCardFromAccount(string $transactionId, array $transactionResponse, string $method): array
    {
        //Add transaction response data to the custom table
        $this->customTransaction
            ->addGetTransactionResponse($transactionId, $transactionResponse);
        if (!$this->responseManager->isTokenAvailable($transactionResponse)) {
            return ['status' => false, 'isAccountSaveCardAction' => true];
        }
        $this->savedCardData->savePaymentCard(
            $transactionResponse,
            $this->savedCardData->getCustomerId(),
            $method
        );
        return ['status' => true, 'isAccountSaveCardAction' => true];
    }

    /**
     * Method to check if token values are present in response
     *
     * @param array<mixed> $transactionResponse
     * @return bool
     */
    public function isTokenAvailable(array $transactionResponse): bool
    {
        return $this->responseManager->isTokenAvailable($transactionResponse);
    }
}
