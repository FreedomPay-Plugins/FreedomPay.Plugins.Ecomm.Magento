<?php

namespace Citipay\HPP\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Citipay\HPP\Api\Data\NotificationInterfaceFactory;
use Citipay\HPP\Api\NotificationRepositoryInterface;
use Citipay\HPP\Model\Notification as NotificationModel;
use Citipay\HPP\Helper\Constants;
use Freedompay\Common\Helper\Requests;
use Freedompay\Common\Logger\RedactData;

/**
 * Class NotificationManager - Manages notification
 */
class NotificationManager
{
    /**
     * @var NotificationRepositoryInterface
     */
    private NotificationRepositoryInterface $notificationRepository;

    /**
     * @var NotificationInterfaceFactory
     */
    private NotificationInterfaceFactory $notificationFactory;

    /**
     * @var RedactData
     */
    protected RedactData $redactData;

    /**
     * NotificationManager constructor.
     *
     * @param NotificationRepositoryInterface $notificationRepository
     * @param NotificationInterfaceFactory $notificationFactory
     * @param RedactData $redactData
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        NotificationInterfaceFactory    $notificationFactory,
        RedactData                      $redactData
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->notificationFactory = $notificationFactory;
        $this->redactData = $redactData;
    }

    /**
     * Save notification response from PSP
     *
     * @param array<mixed> $notificationContent
     * @return bool
     * @throws LocalizedException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws NotFoundException
     * @throws CommandException
     */
    public function saveNotification(array $notificationContent): bool
    {
        $notification = $this->notificationFactory->create();
        $notificationData = $this->prepareNotificationData($notificationContent);

        if ($notificationData) {
            /** @var NotificationModel $notification */
            $notification->addData(
                $notificationData
            );
            $this->notificationRepository->save($notification);
            return true;
        }
        return false;
    }

    /**
     * Prepare data to be entered to notification table
     *
     * @param array<mixed> $notificationContent
     * @return array<mixed>
     */
    public function prepareNotificationData(array $notificationContent) : array
    {
        $transactionId = $this->getTransactionId($notificationContent);
        $redactedNotificationContent = $this->redactData->redact($notificationContent);
        if ($transactionId) {
            return [
                'transaction_id' => $transactionId,
                'content' => $redactedNotificationContent,
                'status' => Notification::NOTIFICATION_STATUS_PENDING,
                'retry_count' => 0
            ];
        }
        return [];
    }

    /**
     * Get Transaction ID from notification.
     *
     * @param array<mixed> $notificationContent
     * @return mixed
     */
    public function getTransactionId(array $notificationContent): mixed
    {
        return $notificationContent[Constants::CUST_SESSION_ID] ?? null;
    }

    /**
     * Get Transaction ID from webhook response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getTransactionIdFromResponse(array $response): mixed
    {
        return $response[Requests::CHECKOUT_TRANSACTION_ID] ?? null;
    }

    /**
     * Get Status from response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getStatus(array $response): mixed
    {
        if ($response && isset($response[Requests::AUTH_RESPONSE])
            && $response[Requests::AUTH_RESPONSE]
            && $response[Requests::AUTH_RESPONSE][Requests::FREEWAY_RESPONSE]
            [Requests::DECISION] == Requests::STATUS_ACCEPT) {
            return $response[Requests::AUTH_RESPONSE][Requests::FREEWAY_RESPONSE][Requests::DECISION];
        } elseif (isset($response['FailedResponses'])) {
            foreach ($response['FailedResponses'] as $failedResponse) {
                if (isset($failedResponse['FreewayResponse']['FreewayRequestId']) &&
                    isset($failedResponse['FreewayResponse']['Decision'])) {
                    return $failedResponse['FreewayResponse']['Decision'];
                }
            }
        }
        return null;
    }

    /**
     * Get Order Increment Id from response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getOrderIncrementId(array $response): mixed
    {
        if ($response && isset($response[Requests::ORIGINAL_REQUEST])
            && $response[Requests::ORIGINAL_REQUEST]
            && $response[Requests::ORIGINAL_REQUEST][Requests::INVOICE_NUMBER]) {
            return $response[Requests::ORIGINAL_REQUEST][Requests::INVOICE_NUMBER];
        }
        return null;
    }

    /**
     * Get Transaction amount from response.
     *
     * @param array<mixed> $response
     * @return mixed
     */
    public function getTransactionAmount(array $response): mixed
    {
        if ($response && isset($response[Requests::AUTH_RESPONSE])
            && $response[Requests::AUTH_RESPONSE]
            && $response[Requests::AUTH_RESPONSE]
            [Requests::FREEWAY_RESPONSE][Requests::AMOUNT]) {
            return $response[Requests::AUTH_RESPONSE][Requests::FREEWAY_RESPONSE][Requests::AMOUNT];
        }
        return null;
    }
}
