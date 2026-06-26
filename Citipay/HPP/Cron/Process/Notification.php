<?php

namespace Citipay\HPP\Cron\Process;

use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Citipay\HPP\Api\NotificationRepositoryInterface;
use Citipay\HPP\Helper\Constants;
use Citipay\HPP\Model\ResourceModel\Notification\Collection;
use Citipay\HPP\Model\ResourceModel\Notification\CollectionFactory;
use Citipay\HPP\Model\TransactionOrderUpdater;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Citipay\HPP\Model\Notification as NotificationModel;
use Freedompay\Common\Helper\Requests;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Update order on running cron with notification data
 */
class Notification
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $notificationCollectionFactory;

    /**
     * @var TransactionOrderUpdater
     */
    protected TransactionOrderUpdater $transactionOrderUpdater;

    /**
     * @var NotificationRepositoryInterface
     */
    protected NotificationRepositoryInterface $notificationRepository;

    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @param CollectionFactory $notificationCollectionFactory
     * @param TransactionOrderUpdater $transactionOrderUpdater
     * @param NotificationRepositoryInterface $notificationRepository
     * @param Serializer $serializer
     * @param DateTime $dateTime
     */
    public function __construct(
        CollectionFactory $notificationCollectionFactory,
        TransactionOrderUpdater $transactionOrderUpdater,
        NotificationRepositoryInterface $notificationRepository,
        Serializer $serializer,
        DateTime $dateTime
    ) {
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->transactionOrderUpdater = $transactionOrderUpdater;
        $this->notificationRepository = $notificationRepository;
        $this->serializer = $serializer;
        $this->dateTime = $dateTime;
    }

    /**
     * Get the non-processed notification and process it.
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute(): void
    {
        $notificationCollection = $this->getPendingNotifications();
        if ($notificationCollection->getSize() > 0) {
            foreach ($notificationCollection as $notification) {
                $this->processNotification($notification);
            }
        }
    }

    /**
     * Get pending notification collection from database
     *
     * @return Collection
     */
    private function getPendingNotifications(): Collection
    {
        $notificationCollection = $this->notificationCollectionFactory->create();
        $notificationCollection->addFieldToFilter(
            [
                ['status'],
                ['status']
            ],
            [
                ['eq' => NotificationModel::NOTIFICATION_STATUS_PENDING],
                ['eq' => NotificationModel::NOTIFICATION_STATUS_FAILURE,
                    'retry' => ['lteq' => Requests::WEBHOOK_GET_TRANSACTION_RETRY_COUNT]]
            ]
        );
        $currentTimestamp = $this->dateTime->gmtTimestamp();
        $fiveMinutesAgoDateTime   = $currentTimestamp - 300;
        $fiveMinutesDate  = $this->dateTime->gmtDate('Y-m-d H:i:s', $fiveMinutesAgoDateTime);
        $notificationCollection->addFieldToFilter('updated_at', ['lt' => $fiveMinutesDate]);
        $notificationCollection->setPageSize(NotificationModel::NOTIFICATION_STATUS_PAGE_SIZE);
        return $notificationCollection;
    }

    /**
     * Process notification
     *
     * @param NotificationModel $notification
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     */
    private function processNotification(NotificationModel $notification): void
    {
        try {
            $notificationContent = $this->serializer->unserialize((string)$notification->getContent());
        } catch (Exception) {
            $notificationContent = null;
        }
        $retryCount = $notification->getRetryCount();
        if (!is_array($notificationContent)) {
            $this->handleInvalidNotification($notification, $retryCount);
            return;
        }
        $notificationDate = $notification->getCreatedAt();
        $isValidContent = $this->validateNotificationContent($notificationContent);
        if (!$isValidContent) {
            $this->handleInvalidNotification($notification, $retryCount);
            return;
        }
        try {
            $responseNotification = $this->transactionOrderUpdater->processNotification(
                $notificationContent,
                $notificationDate,
                $retryCount
            );
            $this->handleResponseNotification($responseNotification, $notification, $retryCount);
        } catch (NoSuchEntityException|NotFoundException|CommandException|LocalizedException) {
            $this->handleException($notification, $retryCount);
        }
    }

    /**
     * Handles invalid notification
     *
     * @param NotificationModel $notification
     * @param int $retryCount
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     */
    private function handleInvalidNotification(NotificationModel $notification, int $retryCount): void
    {
        $currentTimestamp = $this->dateTime->gmtTimestamp();
        $notification->setStatus(NotificationModel::NOTIFICATION_STATUS_ERROR);
        $notification->setRetryCount($retryCount + 1);
        $notification->setUpdatedAt($this->dateTime->gmtDate('Y-m-d H:i:s', $currentTimestamp));
        $this->notificationRepository->save($notification);
    }

    /**
     * Handles response of notification from transaction order updater
     *
     * @param string|null $responseNotification
     * @param NotificationModel $notification
     * @param int $retryCount
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     */
    private function handleResponseNotification(
        ?string $responseNotification,
        NotificationModel $notification,
        int $retryCount
    ): void {
        if ($responseNotification) {
            $status = match ($responseNotification) {
                Constants::NOTIFICATION_STATUS_INVALID => NotificationModel::NOTIFICATION_STATUS_ERROR,
                Constants::NOTIFICATION_STATUS_PROCESSING_ERROR => NotificationModel::NOTIFICATION_STATUS_FAILURE,
                Constants::NOTIFICATION_STATUS_SUCCESS => NotificationModel::NOTIFICATION_STATUS_SUCCESS,
                default => null,
            };

            if ($status !== null) {
                $notification->setStatus($status);
            }
        }
        $currentTimestamp = $this->dateTime->gmtTimestamp();
        $notification->setUpdatedAt($this->dateTime->gmtDate('Y-m-d H:i:s', $currentTimestamp));
        $notification->setRetryCount($retryCount + 1);
        $this->notificationRepository->save($notification);
    }

    /**
     * Handle exception
     *
     * @param NotificationModel $notification
     * @param int $retryCount
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     */
    private function handleException(NotificationModel $notification, int $retryCount): void
    {
        $currentTimestamp = $this->dateTime->gmtTimestamp();
        $notification->setUpdatedAt($this->dateTime->gmtDate('Y-m-d H:i:s', $currentTimestamp));
        $notification->setStatus(NotificationModel::NOTIFICATION_STATUS_FAILURE);
        $notification->setRetryCount($retryCount + 1);
        $this->notificationRepository->save($notification);
    }

    /**
     * Validate notification
     *
     * @param array<mixed> $notificationContent
     * @return bool|string
     */
    public function validateNotificationContent(array $notificationContent): bool | string
    {
        return isset($notificationContent[Constants::CUST_SESSION_ID]);
    }
}
