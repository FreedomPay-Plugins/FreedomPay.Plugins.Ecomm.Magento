<?php

namespace Freedompay\HPP\Gateway\Response;

use Freedompay\Common\Model\Transaction\CustomTransaction;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Freedompay/Citi Pay Create Verification Response Handler
 */
class CreateVerificationResponseHandler implements HandlerInterface
{
    /**
     * @var CustomTransaction
     */
    private CustomTransaction $customTransaction;

    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @param CustomTransaction $customTransaction
     * @param CustomerSession $customerSession
     */
    public function __construct(
        CustomTransaction $customTransaction,
        CustomerSession $customerSession,
    ) {
        $this->customTransaction = $customTransaction;
        $this->customerSession = $customerSession;
    }

    /**
     * Handles response
     *
     * @param array<mixed> $handlingSubject
     * @param array<mixed> $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if ($response) {
            $customerId = $this->customerSession->getCustomerId();
            $transactionId = $response['TransactionId'] ?? null;
            if ($customerId && $transactionId) {
                $this->customTransaction->createPaymentTransaction($customerId, $transactionId, $response);
            }
        }
    }
}
