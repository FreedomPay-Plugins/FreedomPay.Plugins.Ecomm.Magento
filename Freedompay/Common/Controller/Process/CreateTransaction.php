<?php
declare(strict_types=1);

namespace Freedompay\Common\Controller\Process;

use Freedompay\Common\Logger\Logger;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Freedompay\Common\Model\Transaction\CustomTransaction;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;

/**
 * Process Freedompay CreateTransaction Response
 */
class CreateTransaction implements ActionInterface
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var ResultRedirectFactory
     */
    private ResultRedirectFactory $resultRedirectFactory;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @var CustomTransaction
     */
    private CustomTransaction $fpTransaction;

    /**
     * @var Validator
     */
    private Validator $formKeyValidator;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * Result constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param CustomTransaction $fpTransaction
     * @param Validator $formKeyValidator
     * @param Logger $logger
     */
    public function __construct(
        JsonFactory           $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context               $context,
        CustomTransaction     $fpTransaction,
        Validator             $formKeyValidator,
        Logger                $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->context = $context;
        $this->fpTransaction = $fpTransaction;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
    }

    /**
     * Add CreateTransaction response to custom table
     *
     * @return ResponseInterface|Json|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute(): Json|ResultInterface|ResponseInterface|Redirect
    {
        try {
            if (!$this->formKeyValidator->validate($this->context->getRequest())) {
                return $this->resultRedirectFactory->create()
                    ->setPath('/');
            }
            $this->logger->info('Save response from FreedomPay to Magento');
            $transactionId = null;
            $params = $this->context->getRequest()->getParams();
            $this->logger->info('CustomTransaction request params : ', $params);
            $orderId = $params['orderId'] ?? null;
            $response = $params['response'] ?? null;

            if ($response) {
                $transactionId = $response['TransactionId'] ?? null;
            }

            if ($orderId && $transactionId) {
                $this->fpTransaction->createPaymentTransaction((int)$orderId, $transactionId, $response);
            }
            return $this->resultJsonFactory->create()
                ->setData(['response' => 'Custom table entry added successfully.']);
        } catch (\Exception $e) {
            $this->logger->error(__('Error occurred while entering data into a custom table' .$e->getMessage()));
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
