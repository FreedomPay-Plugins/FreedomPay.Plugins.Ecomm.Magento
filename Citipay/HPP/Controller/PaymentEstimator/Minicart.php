<?php

namespace Citipay\HPP\Controller\PaymentEstimator;

use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Citipay\HPP\Model\PaymentEstimatorApi\Api\Minicart as MinicartPaymentEstimator;
use Citipay\HPP\Gateway\Config\PaymentConfig;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Payment estimator controller for Minicart
 */
class Minicart implements ActionInterface
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
     * @var Validator
     */
    private Validator $formKeyValidator;

    /**
     * @var MinicartPaymentEstimator
     */
    private MinicartPaymentEstimator $minicartPaymentEstimator;

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * Minicart constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param MinicartPaymentEstimator $minicartPaymentEstimator
     * @param PaymentConfig $config
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        Validator $formKeyValidator,
        MinicartPaymentEstimator $minicartPaymentEstimator,
        PaymentConfig $config
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->context = $context;
        $this->formKeyValidator = $formKeyValidator;
        $this->minicartPaymentEstimator = $minicartPaymentEstimator;
        $this->config = $config;
    }

    /**
     * Calls PaymentEstimator API in minicart
     *
     * @return Json|Redirect
     * @throws NoSuchEntityException
     */
    public function execute(): Json|Redirect
    {
        if (!$this->formKeyValidator->validate($this->context->getRequest())) {

            return $this->resultRedirectFactory->create()
                ->setPath('/');
        }
        if (!$this->config->isPaymentEstimatorEnabled()) {
            $result = [
                'error' => __('Payment estimator is disabled.')
            ];
        } else {
            try {
                $result = $this->minicartPaymentEstimator->doRequestForMinicart();
            } catch (Exception $e) {
                return $this->resultJsonFactory->create()->setHttpResponseCode(400)->setData([
                    'error' => $e->getMessage()
                ]);
            }
        }
        return $this->resultJsonFactory->create()->setData($result);
    }
}
