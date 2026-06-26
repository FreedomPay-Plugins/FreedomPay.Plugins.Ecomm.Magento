<?php
namespace Freedompay\Common\Model\Transaction;

use Exception;
use Freedompay\Common\Logger\RedactData;
use Freedompay\Common\Model\FreedompayTransactionRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 *
 * Add data to custom table
 */
class CustomTransaction
{
    /**
     * @var FreedompayTransactionRepository
     */
    private FreedompayTransactionRepository $fpTransactionRepository;

    /**
     * @var RedactData
     */
    protected RedactData $redactData;

    /**
     * CustomTransaction constructor.
     * @param FreedompayTransactionRepository $fpTransactionRepository
     * @param RedactData $redactData
     */
    public function __construct(
        FreedompayTransactionRepository $fpTransactionRepository,
        RedactData                      $redactData
    ) {
        $this->fpTransactionRepository = $fpTransactionRepository;
        $this->redactData = $redactData;
    }

    /**
     * Add response to custom table
     *
     * @param int $customerId
     * @param string $transactionId
     * @param array<mixed> $fpResponse
     * @return void
     * @throws LocalizedException
     */
    public function createPaymentTransaction(int $customerId, string $transactionId, array $fpResponse):void
    {
        $redactedFpResponse = $this->redactData->redact($fpResponse);
        try {
            $fpTransactionModel = $this->fpTransactionRepository->create();
            $fpTransactionModel->setcustomerId($customerId);
            $fpTransactionModel->setTransactionId($transactionId);
            $fpTransactionModel->setCreateVerificationTransactionResponse($redactedFpResponse);
            $this->fpTransactionRepository->save($fpTransactionModel);
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Save transaction status to custom table
     *
     * @param array<mixed> $params
     * @return void
     * @throws LocalizedException
     */
    public function updateResponseStatus(array $params): void
    {
        $transactionId = $params['transid'];
        $status = $params['status'] ?? null;
        try {
            $fpTransactionModel = $this->fpTransactionRepository->getByTransactionId($transactionId);
            if ($fpTransactionModel) {
                $fpTransactionModel->setResponseStatus($status);
                $this->fpTransactionRepository->save($fpTransactionModel);
            }
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Add GetTransaction response to custom table
     *
     * @param string $transactionId
     * @param array<mixed> $fpResponse
     * @return void
     * @throws LocalizedException
     */
    public function addGetTransactionResponse(string $transactionId, array $fpResponse):void
    {
        $redactedFpResponse = $this->redactData->redact($fpResponse);
        try {
            $fpTransactionModel = $this->fpTransactionRepository->getByTransactionId($transactionId);
            if ($fpTransactionModel) {
                $fpTransactionModel->setGetTransactionResponse((string)$redactedFpResponse);
                $this->fpTransactionRepository->save($fpTransactionModel);
            }
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
