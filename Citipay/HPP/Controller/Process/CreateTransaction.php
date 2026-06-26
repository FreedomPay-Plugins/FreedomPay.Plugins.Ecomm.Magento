<?php
declare(strict_types=1);

namespace Citipay\HPP\Controller\Process;

use Citipay\HPP\Logger\Logger;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Freedompay\Common\Model\Transaction\CustomTransaction;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * Process Citipay HPP CreateTransaction Response
 */
class CreateTransaction extends \Freedompay\Common\Controller\Process\CreateTransaction
{
    /**
     * Result constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param CustomTransaction $fpTransaction
     * @param Validator $formKeyValidator
     * @param Logger $logger
     */
    //phpcs:ignore
    public function __construct(
        JsonFactory           $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context               $context,
        CustomTransaction     $fpTransaction,
        Validator             $formKeyValidator,
        Logger                $logger
    ) {
        parent::__construct(
            $resultJsonFactory,
            $resultRedirectFactory,
            $context,
            $fpTransaction,
            $formKeyValidator,
            $logger
        );
    }
}
