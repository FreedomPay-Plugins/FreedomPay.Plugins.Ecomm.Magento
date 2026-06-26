<?php
declare(strict_types=1);

namespace Citipay\HPP\Controller\Payment;

use Citipay\HPP\Logger\Logger;
use Citipay\HPP\Model\Ui\ConfigProvider;
use Freedompay\Common\Controller\Process\TransactionStatus;
use Citipay\HPP\Model\Transaction\TransactionManager;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Process CitipayHPP cancel response after redirect
 */
class Cancel extends TransactionStatus
{
    /**
     * @var string
     */
    protected string $method = ConfigProvider::CODE;

    /**
     * Cancel constructor.
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
        Logger $logger
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
