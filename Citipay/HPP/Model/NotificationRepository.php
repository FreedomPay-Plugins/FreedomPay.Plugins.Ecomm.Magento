<?php

namespace Citipay\HPP\Model;

use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Citipay\HPP\Api\Data\NotificationInterface;
use Citipay\HPP\Api\NotificationRepositoryInterface;
use Citipay\HPP\Model\Notification as NotificationModel;
use Citipay\HPP\Model\ResourceModel\Notification as NotificationResource;

/**
 * Class NotificationRepository - Handles db operations of notifications
 */
class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * @var NotificationResource
     */
    private NotificationResource $notificationResource;

    /**
     * NotificationRepository construct
     *
     * @param NotificationResource $notificationResource
     */
    public function __construct(
        NotificationResource $notificationResource
    ) {
        $this->notificationResource = $notificationResource;
    }

    /**
     * @inheritdoc
     */
    public function save(NotificationInterface $notification): NotificationInterface
    {
        try {
            /** @var NotificationModel $notification */
            $this->notificationResource->save($notification);
            return $notification;
        } catch (Exception) {
            throw new CouldNotSaveException(
                __('We couldn\'t save the notification. Try again later.')
            );
        }
    }
}
