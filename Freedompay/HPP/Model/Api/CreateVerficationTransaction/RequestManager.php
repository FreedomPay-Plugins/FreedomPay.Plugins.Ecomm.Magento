<?php

namespace Freedompay\HPP\Model\Api\CreateVerficationTransaction;

use Exception;
use Freedompay\Common\Logger\Logger;
use Freedompay\Common\Model\Cart\QuoteManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Model\Quote;

/**
 * Manage CreateVerification transaction request
 */
class RequestManager
{
    /**
     * @var CommandPoolInterface
     */
    private CommandPoolInterface $commandPool;

    /**
     * @var PaymentDataObjectFactory
     */
    private PaymentDataObjectFactory $paymentDataObjectFactory;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var string
     */
    private string $commandName;

    /**
     * @var QuoteManager
     */
    private QuoteManager $quoteManager;

    /**
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
     * @return ResultInterface|array<mixed>
     * @throws LocalizedException
     */
    public function process(): ResultInterface|array
    {
        $result = [];
        try {
            if ($this->commandName) {
                /** @var Quote $quote */
                $quote = $this->quoteManager->getQuote();
                $this->quoteManager->reserveOrderId($quote);

                $payment = $quote->getPayment();
                $paymentDataObject = $this->paymentDataObjectFactory->create($payment);
                $paymentCommandParams = [
                    'payment' => $paymentDataObject,
                    'quote' => $quote,
                    'isAccountSaveCardAction' => true,
                    'requestToken' => true
                ];
                $this->logger->info(__('Execute Payment Gateway Command - '. $this->commandName));
                $this->logger->info($paymentCommandParams, [], 'paymentCommandParams = ');
                /** @var ResultInterface $result */
                $result = $this->commandPool->get($this->commandName)->execute($paymentCommandParams);
            }
            return $result;
        } catch (Exception $e) {
            $this->logger->error(__('Error occurred while executing CreateVerification command:: ' .$e->getMessage()));
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
