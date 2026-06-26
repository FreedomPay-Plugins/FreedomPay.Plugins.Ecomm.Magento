<?php

namespace Citipay\HPP\Controller\Payment;

use Citipay\HPP\Logger\Logger;
use Citipay\HPP\Model\CreateTransaction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * Init payment transaction
 */
class InitPayment extends \Freedompay\Common\Controller\Payment\InitPayment
{
    /**
     * InitPayment constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param CreateTransaction $createTransactionModel
     * @param Validator $formKeyValidator
     * @param Logger $logger
     */
    //phpcs:disable
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        CreateTransaction $createTransactionModel,
        Validator $formKeyValidator,
        Logger $logger
    ) {
        parent::__construct(
            $resultJsonFactory,
            $resultRedirectFactory,
            $context,
            $createTransactionModel,
            $formKeyValidator,
            $logger
        );
    }
}
