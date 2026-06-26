<?php
namespace Citipay\HPP\Model\Transaction;

use Citipay\HPP\Model\GetTransaction as CPRequestManager;
use Citipay\HPP\Model\Api\ResponseManager as CPResponseManager;
use Freedompay\Common\Model\Cart\QuoteManager;
use Freedompay\Common\Model\Order\OrderManager;
use Magento\Framework\Exception\LocalizedException;
use Freedompay\Common\Model\Transaction\TransactionManager as CommonTransactionManager;

/**
 *
 * Manages Citipay transactions
 */
class TransactionManager extends CommonTransactionManager
{
    /**
     * @var OrderManager
     */
    private OrderManager $orderManager;

    /**
     * @var CPResponseManager
     */
    private CPResponseManager $responseManager;

    /**
     * @param OrderManager $orderManager
     * @param QuoteManager $quoteManager
     * @param CPRequestManager $cpRequestManager
     * @param CPResponseManager $responseManager
     */
    //phpcs:disable
    public function __construct(
        OrderManager $orderManager,
        QuoteManager $quoteManager,
        CPRequestManager $cpRequestManager,
        CPResponseManager $responseManager
    ) {
        $this->orderManager         = $orderManager;
        $this->responseManager      = $responseManager;
        parent::__construct(
            $orderManager,
            $quoteManager,
            $cpRequestManager,
            $responseManager
        );
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
        $isStatusReview = $this->responseManager->isStatusReview($transactionResponse);
        return (int)$this->orderManager->processOrder(
            $transactionResponse,
            $isAuthOnly,
            $isCaptured,
            $isBillingAddressUpdateRequired,
            $isStatusReview
        );
    }
}
