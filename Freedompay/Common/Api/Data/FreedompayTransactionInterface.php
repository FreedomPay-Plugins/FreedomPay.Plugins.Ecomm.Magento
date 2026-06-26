<?php
declare(strict_types=1);

namespace Freedompay\Common\Api\Data;

interface FreedompayTransactionInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const ENTITY_ID                      = 'entity_id';
    public const CUSTOMER_ID                    = 'customer_id';
    public const TRANSACTION_ID                 = 'transaction_id';
    public const RESPONSE_STATUS                 = 'response_status';
    public const CREATE_VERIFICATION_RESPONSE   = 'verification_transaction_response';
    public const GET_RESPONSE                   = 'get_transaction_response';
    public const CREATED_AT                     = 'created_at';

    /**
     * Get Entity Id.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set Entity Id.
     *
     * @param string $entityId
     * @return void
     */
    public function setEntityId($entityId);

    /**
     * Get Customer Id.
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set Customer Id.
     *
     * @param int $customerId
     * @return void
     */
    public function setCustomerId(int $customerId): void;

    /**
     * Get TransactionId.
     *
     * @return string
     */
    public function getTransactionId(): string;

    /**
     * Set TransactionId.
     *
     * @param string $transactionId
     * @return void
     */
    public function setTransactionId(string $transactionId): void;

    /**
     * Get ResponseStatus.
     *
     * @return string
     */
    public function getResponseStatus(): string;

    /**
     * Set ResponseStatus.
     *
     * @param string $responseStatus
     * @return void
     */
    public function setResponseStatus(string $responseStatus): void;

    /**
     * Get CreateVerificationTransaction Response.
     *
     * @return string
     */
    public function getCreateVerificationTransactionResponse(): string;

    /**
     * Set CreateVerificationTransaction Response.
     *
     * @param string $response
     * @return void
     */
    public function setCreateVerificationTransactionResponse(string $response): void;

    /**
     * Get GetTransaction Response.
     *
     * @return string
     */
    public function getGetTransactionResponse(): string;

    /**
     * Set GetTransaction Response.
     *
     * @param string $response
     * @return void
     */
    public function setGetTransactionResponse(string $response): void;

    /**
     * Get CreatedAt Timestamp.
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set CreatedAt Timestamp.
     *
     * @param string $createdTime
     * @return void
     */
    public function setCreatedAt(string $createdTime): void;
}
