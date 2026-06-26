<?php
declare(strict_types=1);

namespace Freedompay\HPP\Controller\Process;

use Freedompay\Common\Controller\Process\TransactionStatus;
use Freedompay\Common\Model\Transaction\CustomTransaction;
use Freedompay\HPP\Model\Transaction\TransactionManager;
use Freedompay\HPP\Model\Ui\ConfigProvider;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Freedompay\HPP\Logger\Logger;

/**
 * Process FreedompayHpp response after redirect
 */
class Response extends TransactionStatus
{
    /**
     * @var string
     */
    protected string $method = ConfigProvider::CODE;

    /**
     * @var array|string[]
     */
    private array $isAccountSaveCardActionErrorStatuses = ['C', 'D', 'I', 'M', 'N', 'P', 'S', 'U', 'X', '1', '2', '3'];

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * @var CustomTransaction
     */
    private CustomTransaction $customTransaction;

    /**
     * Response constructor.
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param TransactionManager $transactionManager
     * @param Logger $logger
     * @param DataPersistorInterface $dataPersistor
     * @param CustomTransaction $customTransaction
     */
    //phpcs:disable
    public function __construct(
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        ManagerInterface $messageManager,
        TransactionManager $transactionManager,
        Logger $logger,
        DataPersistorInterface $dataPersistor,
        CustomTransaction $customTransaction
    ) {
        $this->logger = $logger;
        $this->dataPersistor = $dataPersistor;
        $this->customTransaction = $customTransaction;
        parent::__construct(
            $resultRedirectFactory,
            $context,
            $messageManager,
            $transactionManager,
            $logger
        );
    }

    /**
     * Method to process Account save card action flag
     *
     * @param array<mixed> $params
     * @return bool[]
     * @throws LocalizedException
     */
    public function accountSaveCardAction(array $params): array
    {
        $isAccountSaveCardAction = false;
        $redirectToStoredPaymentMethods = false;
        $status = $params['status'] ?? null;
        if ($this->dataPersistor->get('isAccountSaveCardAction')) {
            $isAccountSaveCardAction = true;
            $this->customTransaction->updateResponseStatus($params);
        }
        $this->logger->info('Is saveCardFromAccount transaction?:: ', ['$isAccountSaveCardAction' => $isAccountSaveCardAction]);
        if ($isAccountSaveCardAction
            && (in_array($status, $this->isAccountSaveCardActionErrorStatuses) || isset($params['errcode']))) {
            $this->clearAccountSaveCardActionFlag();
            $redirectToStoredPaymentMethods = true;
        }
        return [
            'isAccountSaveCardAction' => $isAccountSaveCardAction,
            'redirectToStoredPaymentMethods' => $redirectToStoredPaymentMethods
        ];
    }

    /**
     * Clear isAccountSaveCardActionFlag
     *
     * @return bool
     */
    public function clearAccountSaveCardActionFlag(): bool
    {
        if ($this->dataPersistor->get('isAccountSaveCardAction')) {
            $this->dataPersistor->clear('isAccountSaveCardAction');
        }
        return true;
    }
}

