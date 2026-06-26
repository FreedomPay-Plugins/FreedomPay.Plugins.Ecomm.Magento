<?php

namespace Freedompay\HPP\Controller\Payment;

use Freedompay\HPP\Logger\Logger;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Freedompay\HPP\Model\CreateTransaction;
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
