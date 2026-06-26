<?php
declare(strict_types=1);

namespace Citipay\HPP\Controller\Process;

use Freedompay\Common\Controller\Process\TransactionStatus;
use Citipay\HPP\Model\Transaction\TransactionManager;
use Citipay\HPP\Model\Ui\ConfigProvider;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Citipay\HPP\Logger\Logger;

/**
 * Process Citipay response after redirect
 */
class Response extends TransactionStatus
{
    /**
     * @var string
     */
    protected string $method = ConfigProvider::CODE;

    /**
     * Response constructor.
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param TransactionManager $transactionManager
     * @param Logger $logger
     */
    //phpcs:disable
    public function __construct(
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        ManagerInterface $messageManager,
        TransactionManager $transactionManager,
        Logger $logger,
    ) {
        parent::__construct(
            $resultRedirectFactory,
            $context,
            $messageManager,
            $transactionManager,
            $logger
        );
    }
}

