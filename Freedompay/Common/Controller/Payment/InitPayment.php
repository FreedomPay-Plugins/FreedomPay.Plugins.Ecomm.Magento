<?php

namespace Freedompay\Common\Controller\Payment;

use Exception;
use Freedompay\Common\Logger\Logger;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Payment\Gateway\Command\ResultInterface;
use Freedompay\Common\Model\Api\RequestManager;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * Init payment transaction
 */
class InitPayment implements ActionInterface
{

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var ResultRedirectFactory
     */
    private ResultRedirectFactory $resultRedirectFactory;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @var RequestManager
     */
    private RequestManager $requestManager;

    /**
     * @var Validator
     */
    private Validator $formKeyValidator;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * InitPayment constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param RequestManager $requestManager
     * @param Validator $formKeyValidator
     * @param Logger $logger
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        RequestManager $requestManager,
        Validator $formKeyValidator,
        Logger $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->context = $context;
        $this->requestManager = $requestManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
    }

    /**
     * Calls CreateTransaction service model
     *
     * @return Json|Redirect
     */
    public function execute(): Json|Redirect
    {
        if (!$this->formKeyValidator->validate($this->context->getRequest())) {
            return $this->resultRedirectFactory->create()
                ->setPath('/');
        }

        $requestParams = $this->context->getRequest()->getParams();

        try {
            $this->logger->info(__('Send CreateTransaction request'));

            /** @var ResultInterface $result */
            $result = $this->requestManager->process($requestParams);

            return $this->resultJsonFactory->create()->setData($result);
        } catch (Exception $e) {
            $this->logger->error(__('Error occurred on send create transaction request' . $e->getMessage()));
            return $this->resultJsonFactory->create()->setHttpResponseCode(400)->setData([
                'error' => $e->getMessage()
            ]);
        }
    }
}
