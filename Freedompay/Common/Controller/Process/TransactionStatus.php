<?php
declare(strict_types=1);

namespace Freedompay\Common\Controller\Process;

use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Freedompay\Common\Model\Transaction\TransactionManager;
use Freedompay\Common\Logger\Logger;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;

/**
 * Process Freedompay GetTransaction response after redirect
 */
class TransactionStatus implements ActionInterface
{

    public const SUCCESS_MSG_SAVE_CARD = 'Payment method was successfully added.';
    public const ERR_MSG_SAVE_CARD = 'Something went wrong while adding the payment method.';

    /**
     * @var ResultRedirectFactory
     */
    private ResultRedirectFactory $resultRedirectFactory;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var TransactionManager
     */
    private TransactionManager $transactionManager;

    /**
     * @var string
     */
    protected string $method = '';

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * Response constructor.
     *
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param TransactionManager $transactionManager
     * @param Logger $logger
     */
    public function __construct(
        ResultRedirectFactory  $resultRedirectFactory,
        Context                $context,
        ManagerInterface       $messageManager,
        TransactionManager     $transactionManager,
        Logger                 $logger
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->context = $context;
        $this->messageManager = $messageManager;
        $this->transactionManager = $transactionManager;
        $this->logger = $logger;
    }

    /**
     * Cancel order if transaction fails
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        try {
            $params = $this->context->getRequest()->getParams();
            $params = array_change_key_case($params, CASE_LOWER);
            $this->logger->info('GetTransaction URI Params : ', $params);
            $accountSaveCardAction = $this->accountSaveCardAction($params);
            $isAccountSaveCardAction = false;

            if (isset($accountSaveCardAction['isAccountSaveCardAction'])) {
                $isAccountSaveCardAction = $accountSaveCardAction['isAccountSaveCardAction'];
            }
            if (isset($accountSaveCardAction['redirectToStoredPaymentMethods'])
                && $accountSaveCardAction['redirectToStoredPaymentMethods']) {
                $this->redirectToStoredPaymentMethods(self::ERR_MSG_SAVE_CARD, false);
            }
            /**
             * @var mixed $processedResponse
             */
            $processedResponse = $this->transactionManager->processTransaction(
                $params,
                $this->method,
                $isAccountSaveCardAction
            );
            $this->clearAccountSaveCardActionFlag();
            if ($processedResponse['status']) {
                if (isset($processedResponse['isAccountSaveCardAction'])
                    && $processedResponse['isAccountSaveCardAction']) {
                    //If isAccountSaveCardAction transaction is success,
                    // redirect to storedPayments page with success message
                    return $this->redirectToStoredPaymentMethods(self::SUCCESS_MSG_SAVE_CARD, true);
                } else {
                    //If checkout transaction is success, redirect to order success page
                    return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
                }
            } else {
                if (isset($processedResponse['isAccountSaveCardAction'])
                    && $processedResponse['isAccountSaveCardAction']) {
                    $this->clearAccountSaveCardActionFlag();
                    //If isAccountSaveCardAction transaction is failed,
                    // redirect to storedPayments page with error message
                    return $this->redirectToStoredPaymentMethods(self::ERR_MSG_SAVE_CARD, false);
                } else {
                    $this->logger->info('Response status:: ', ['$processedResponse' => $processedResponse]);
                    return $this->redirectToCart(CommonConfig::ERR_MSG_GENERIC);
                }
            }

        } catch (Exception $e) {
            $this->logger->error(__('Error while processing transaction status:: ' . $e->getMessage()));
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Redirects to cart
     *
     * @param string $message
     * @return Redirect
     */
    public function redirectToCart(string $message): Redirect
    {
        $this->messageManager->addErrorMessage($message);
        return $this->resultRedirectFactory->create()->setPath('checkout/cart');
    }

    /**
     * Redirects to stored payment methods
     *
     * @param string $message
     * @param bool $status
     * @return Redirect
     */
    public function redirectToStoredPaymentMethods(string $message, bool $status): Redirect
    {
        if ($status) {
            $this->messageManager->addSuccessMessage($message);
        } else {
            $this->messageManager->addErrorMessage($message);
        }
        return $this->resultRedirectFactory->create()->setPath('vault/cards/listaction');
    }

    /**
     * Method to process account save card action
     *
     * @param array<mixed> $params
     * @return array<mixed>
     */
    public function accountSaveCardAction(array $params): array
    {
        return [];
    }

    /**
     * Clear isAccountSaveCardActionFlag
     *
     * @return bool
     */
    public function clearAccountSaveCardActionFlag(): bool
    {
        return false;
    }
}
