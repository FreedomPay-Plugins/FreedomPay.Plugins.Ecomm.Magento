<?php

namespace Citipay\HPP\Model;

use Magento\Framework\Model\AbstractModel;
use Citipay\HPP\Api\Data\NotificationInterface;
use Citipay\HPP\Model\ResourceModel\Notification as NotificationResource;

/**
 * Notification class
 *
 * citipay_notification table model class
 */
class Notification extends AbstractModel implements NotificationInterface
{
    public const NOTIFICATION_STATUS_PENDING = 0;
    public const NOTIFICATION_STATUS_SUCCESS = 1;
    public const NOTIFICATION_STATUS_FAILURE = 2;
    public const NOTIFICATION_STATUS_ERROR = 3;
    public const NOTIFICATION_STATUS_PAGE_SIZE = 100;

    /**
     * Initialize the resource model
     *
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(NotificationResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getId(): mixed
    {
        return $this->getData(NotificationInterface::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId(mixed $value): mixed
    {
        return $this->setData(NotificationInterface::ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId(): ?string
    {
        return $this->getData(NotificationInterface::TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId(string $txnId): void
    {
        $this->setData(NotificationInterface::TRANSACTION_ID, $txnId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId(): ?string
    {
        return $this->getData(NotificationInterface::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(string $orderId): void
    {
        $this->setData(NotificationInterface::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderIncrementId(): ?string
    {
        return $this->getData(NotificationInterface::ORDER_INCREMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderIncrementId(string $orderIncrementId): void
    {
        $this->setData(NotificationInterface::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * @inheritDoc
     */
    public function getContent(): ?string
    {
        return $this->getData(NotificationInterface::CONTENT);
    }

    /**
     * @inheritDoc
     */
    public function setContent(string $response): void
    {
        $this->setData(NotificationInterface::CONTENT, $response);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->getData(NotificationInterface::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(int $status): void
    {
        $this->setData(NotificationInterface::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getRetryCount(): int
    {
        return $this->getData(NotificationInterface::RETRY_COUNT);
    }

    /**
     * @inheritDoc
     */
    public function setRetryCount(int $retryCount): void
    {
        $this->setData(NotificationInterface::RETRY_COUNT, $retryCount);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return $this->getData(NotificationInterface::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $date): void
    {
        $this->setData(NotificationInterface::CREATED_AT, $date);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(NotificationInterface::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $date): void
    {
        $this->setData(NotificationInterface::UPDATED_AT, $date);
    }
}
