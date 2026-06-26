<?php

namespace Citipay\HPP\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Citipay\HPP\Api\Data\NotificationInterface;

interface NotificationRepositoryInterface
{
    /**
     * Save the notification data
     *
     * @param NotificationInterface $notification
     * @return NotificationInterface
     * @throws InputException
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function save(NotificationInterface $notification): NotificationInterface;
}
