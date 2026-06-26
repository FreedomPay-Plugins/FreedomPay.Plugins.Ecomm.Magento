<?php
namespace Freedompay\Common\Model\Api;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Freedompay\Common\Model\Cart\QuoteManager;
use Magento\Quote\Model\Quote;
use Freedompay\Common\Logger\Logger;

/**
 *
 * Manage Freedompay api request transactions
 */
class RequestManager
{
    /**
     * @var CommandPoolInterface
     */
    protected CommandPoolInterface $commandPool;

    /**
     * @var PaymentDataObjectFactory
     */
    protected PaymentDataObjectFactory $paymentDataObjectFactory;

    /**
     * @var string
     */
    protected string $commandName;

    /**
     * @var QuoteManager
     */
    protected QuoteManager $quoteManager;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * RequestManager constructor.
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param QuoteManager $quoteManager
     * @param Logger $logger
     * @param string $commandName
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteManager $quoteManager,
        Logger $logger,
        string $commandName = ''
    ) {
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->quoteManager = $quoteManager;
        $this->logger = $logger;
        $this->commandName = $commandName;
    }

    /**
     * Process Api request
     *
     * @param array<mixed> $requestParams
     * @return ResultInterface|null|bool|array<mixed>
     * @throws LocalizedException
     */
    public function process(array $requestParams): array|ResultInterface|bool|null
    {
        $result = [];
        try {
            if ($this->commandName) {
                $transactionId = $requestParams['transid'] ?? null;
                /** @var Quote $quote */
                $quote = $this->quoteManager->getQuote();
                $this->quoteManager->reserveOrderId($quote);
                $payment = $quote->getPayment();
                $paymentDataObject = $this->paymentDataObjectFactory->create($payment);
                $tokenDetails = $this->getTokenDetails($requestParams);
                $paymentCommandParams = [
                    'payment' => $paymentDataObject,
                    'quote' => $quote,
                    'transactionId' => $transactionId,
                    'billingAddress' => $requestParams['billing_address'] ?? null,
                    'isAccountSaveCardAction' => false
                ];
                if (!empty($tokenDetails)) {
                    $paymentCommandParams = array_merge($paymentCommandParams, $tokenDetails);
                }
                $this->logger->info(__('Execute Payment Gateway Command - '. $this->commandName));
                $this->logger->info($paymentCommandParams, [], 'paymentCommandParams = ');
                /** @var ResultInterface $result */
                $result = $this->commandPool->get($this->commandName)->execute($paymentCommandParams);
            }
            return $result;
        } catch (Exception $e) {
            $this->logger->error(__('Error occurred while processing gateway command:: ' .$e->getMessage()));
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Add token details to payment command parameters
     *
     * @param array<mixed> $requestParams
     * @return array<mixed>
     */
    public function getTokenDetails(array $requestParams): array
    {
        return [];
    }
}
