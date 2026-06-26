<?php
declare(strict_types=1);

namespace Citipay\HPP\Controller\Process;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Citipay\HPP\Model\NotificationManager;
use Citipay\HPP\Helper\Constants;

/**
 * Notification - Process notification
 */
class Notification implements CsrfAwareActionInterface
{
    /**
     * @var NotificationManager
     */
    private NotificationManager $notificationManager;

    /**
     * @var Http
     */
    private Http $response;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @param Serializer $serializer
     * @param Request $request
     * @param NotificationManager $notificationManager
     * @param Http $response
     */
    public function __construct(
        Serializer                      $serializer,
        Request                         $request,
        NotificationManager             $notificationManager,
        Http                            $response
    ) {
        $this->serializer = $serializer;
        $this->request = $request;
        $this->notificationManager = $notificationManager;
        $this->response = $response;
    }

    /**
     * Process notification response from PSP
     *
     * @throws LocalizedException
     */
    public function execute(): ResultInterface|ResponseInterface|Http
    {
        $response = null;
        if ($this->request->getMethod() == Constants::API_METHOD_GET) {
            return $this->response->setStatusCode(200);
        }
        $body = $this->request->getContent();

        if (!$body) {
            return $this->response->setStatusCode(500);
        }
        $notificationContent = $this->serializer->unSerialize($body);
        if (is_array($notificationContent) && $notificationContent) {
            $response = $this->notificationManager->saveNotification($notificationContent);
        }
        if ($response) {
            return $this->response->setStatusCode(200);
        } else {
            return $this->response->setStatusCode(500);
        }
    }

    /**
     * Method to create CSRF validation exception
     *
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Method to validate for CSRF
     *
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
