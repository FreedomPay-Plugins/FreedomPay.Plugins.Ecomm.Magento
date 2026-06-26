<?php

namespace Citipay\HPP\Api\Data;

/**
 * Interface for notification Model
 */
interface NotificationInterface
{
    public const TABLE_NAME              = 'citipay_notification';
    public const ID                      = 'id';
    public const TRANSACTION_ID          = 'transaction_id';
    public const ORDER_ID                = 'order_id';
    public const ORDER_INCREMENT_ID      = 'order_increment_id';
    public const CONTENT                 = 'content';
    public const STATUS                  = 'status';
    public const CREATED_AT              = 'created_at';
    public const UPDATED_AT              = 'updated_at';
    public const RETRY_COUNT             = 'retry_count';

    /**
     * Get notification id
     *
     * @return mixed
     */
    public function getId(): mixed;

    /**
     * Set notification ID
     *
     * @param mixed $value
     * @return $this
     */
    public function setId(mixed $value): mixed;

    /**
     * Get transaction id
     *
     * @return string|null
     */
    public function getTransactionId(): ?string;

    /**
     * Set transaction id
     *
     * @param string $txnId
     * @return void
     */
    public function setTransactionId(string $txnId): void;

    /**
     * Get order id
     *
     * @return string|null
     */
    public function getOrderId(): ?string;

    /**
     * Set order id
     *
     * @param string $orderId
     * @return void
     */
    public function setOrderId(string $orderId): void;

    /**
     * Get order increment id
     *
     * @return string|null
     */
    public function getOrderIncrementId(): ?string;

    /**
     * Set order increment id
     *
     * @param string $orderIncrementId
     * @return void
     */
    public function setOrderIncrementId(string $orderIncrementId): void;

    /**
     * Get response content
     *
     * @return string|null
     */
    public function getContent(): ?string;

    /**
     * Set response content
     *
     * @param string $response
     * @return void
     */
    public function setContent(string $response): void;

    /**
     * Get response processed status
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Set response processed status
     *
     * @param int $status
     * @return void
     */
    public function setStatus(int $status): void;

    /**
     * Get retry count
     *
     * @return int
     */
    public function getRetryCount(): int;

    /**
     * Set retry count
     *
     * @param int $retryCount
     * @return void
     */
    public function setRetryCount(int $retryCount): void;

    /**
     * Get Created at time for notification in db
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set Created at time for notification in db
     *
     * @param string $date
     * @return void
     */
    public function setCreatedAt(string $date): void;

    /**
     * Get Updated at time for notification in db
     *
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * Set Updated at time for notification in db
     *
     * @param string $date
     * @return void
     */
    public function setUpdatedAt(string $date): void;
}
