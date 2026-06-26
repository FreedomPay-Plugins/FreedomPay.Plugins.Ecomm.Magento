<?php

namespace Citipay\HPP\Controller\PaymentEstimator;

use Exception;
use Citipay\HPP\Gateway\Config\PaymentConfig;
use Citipay\HPP\Model\PaymentEstimatorApi\Api\Product as PaymentEstimatorProductApi;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product;

/**
 * Payment estimator controller for PDP
 */
class ProductPage implements ActionInterface
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
     * @var PaymentEstimatorProductApi
     */
    private PaymentEstimatorProductApi $paymentEstimatorProductApi;

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * InitPayment constructor.
     * @param JsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param PaymentEstimatorProductApi $paymentEstimatorProductApi
     * @param PaymentConfig $config
     * @param ProductRepository $productRepository
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Context $context,
        Validator $formKeyValidator,
        PaymentEstimatorProductApi $paymentEstimatorProductApi,
        PaymentConfig $config,
        ProductRepository $productRepository,
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->context = $context;
        $this->formKeyValidator = $formKeyValidator;
        $this->paymentEstimatorProductApi = $paymentEstimatorProductApi;
        $this->config = $config;
        $this->productRepository = $productRepository;
    }

    /**
     * Calls PaymentEstimator API for PDP
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
        $result = [];
        if (!$this->config->isPaymentEstimatorEnabled() || !$this->config->isPdpMessagingEnabled()) {
            $result = [
                'error' => __('Payment estimator is disabled.')
            ];
        } else {
            try {
                $productId = $this->context->getRequest()->getParam('productId', 0);
                if ($productId) {
                    try {
                        /** @var Product $product */
                        $product = $this->productRepository->getById($productId);

                        if ($product->getTypeId() === Type::TYPE_SIMPLE) {
                            $price = $product->getFinalPrice();
                        } else {
                            $price = 0;
                        }
                        $result = $this->paymentEstimatorProductApi->doRequestForPDP($price);
                    } catch (NoSuchEntityException $e) {
                        $result = [
                            'error' => __('An exception occurred while processing your request.' . $e->getMessage())
                        ];
                    }
                }
            } catch (Exception $e) {
                return $this->resultJsonFactory->create()->setHttpResponseCode(400)->setData([
                    'error' => $e->getMessage()
                ]);
            }
        }
        return $this->resultJsonFactory->create()->setData($result);
    }
}
