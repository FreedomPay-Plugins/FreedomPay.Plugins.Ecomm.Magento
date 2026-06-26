<?php

namespace Freedompay\HPP\Controller\Process;

use Freedompay\HPP\Model\CreateVerification;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Handles verify transaction
 */
class CreateVerificationTransaction implements ActionInterface
{
    /**
     * @var CreateVerification
     */
    private CreateVerification $createVerification;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $resultRedirectFactory;

    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * @param CreateVerification $createVerification
     * @param RedirectFactory $resultRedirectFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        CreateVerification $createVerification,
        RedirectFactory $resultRedirectFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->createVerification = $createVerification;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Process CreateVerification Transaction For Freedompay
     *
     * @return ResultInterface|ResponseInterface|Redirect
     * @throws LocalizedException
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        $this->dataPersistor->set('isAccountSaveCardAction', true);
        $result = $this->createVerification->process();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (is_array($result) && isset($result['CheckoutUrl'])) {
            $resultRedirect->setPath($result['CheckoutUrl']);
        }
        return $resultRedirect;
    }
}
