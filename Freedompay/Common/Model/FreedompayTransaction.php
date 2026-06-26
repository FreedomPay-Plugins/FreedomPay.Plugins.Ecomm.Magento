<?php

namespace Freedompay\Common\Model;

use Freedompay\Common\Model\ResourceModel\FreedompayTransaction as FreedompayTransactionResource;
use Freedompay\Common\Api\Data\FreedompayTransactionInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * FreedompayTransaction class
 *
 * fp_payment_transaction table model class
 */
class FreedompayTransaction extends AbstractModel implements FreedompayTransactionInterface
{
    /**
     * Initialize the resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(FreedompayTransactionResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): int
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): void
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId(): string
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId(string $transactionId): void
    {
        $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * @inheritDoc
     */
    public function getResponseStatus(): string
    {
        return $this->getData(self::RESPONSE_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setResponseStatus(string $responseStatus): void
    {
        $this->setData(self::RESPONSE_STATUS, $responseStatus);
    }

    /**
     * @inheritDoc
     */
    public function getGetTransactionResponse(): string
    {
        return $this->getData(self::GET_RESPONSE);
    }

    /**
     * @inheritDoc
     */
    public function setGetTransactionResponse(string $response): void
    {
        $this->setData(self::GET_RESPONSE, $response);
    }

    /**
     * @inheritDoc
     */
    public function getCreateVerificationTransactionResponse(): string
    {
        return $this->getData(self::CREATE_VERIFICATION_RESPONSE);
    }

    /**
     * @inheritDoc
     */
    public function setCreateVerificationTransactionResponse(string $response): void
    {
        $this->setData(self::CREATE_VERIFICATION_RESPONSE, $response);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdTime): void
    {
        $this->setData(self::CREATED_AT, $createdTime);
    }
}
