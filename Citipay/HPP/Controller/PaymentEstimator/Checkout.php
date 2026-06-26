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
use Citipay\HPP\Model\PaymentEstimatorApi\Api\Checkout as CheckoutPaymentEstimator;
use Citipay\HPP\Gateway\Config\PaymentConfig;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Payment estimator controller for checkout page
 */
class Checkout implements ActionInterface
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
     * @var CheckoutPaymentEstimator
     */
    private CheckoutPaymentEstimator $checkoutPaymentEstimator;

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * InitPayment constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param CheckoutPaymentEstimator $checkoutPaymentEstimator
     * @param PaymentConfig $config
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        Validator $formKeyValidator,
        CheckoutPaymentEstimator $checkoutPaymentEstimator,
        PaymentConfig $config
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->context = $context;
        $this->formKeyValidator = $formKeyValidator;
        $this->checkoutPaymentEstimator = $checkoutPaymentEstimator;
        $this->config = $config;
    }

    /**
     * Calls PaymentEstimator Checkout API
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
                $isCheckout = (bool)$this->context->getRequest()->getParam('is_checkout', false);
                $price = $this->context->getRequest()->getParam('sale_amount', 0);
                $result = $this->checkoutPaymentEstimator->doRequestForCheckout($isCheckout, $price);
            } catch (Exception $e) {
                return $this->resultJsonFactory->create()->setHttpResponseCode(400)->setData([
                    'error' => $e->getMessage()
                ]);
            }
        }
        return $this->resultJsonFactory->create()->setData($result);
    }
}
