<?php
declare(strict_types=1);

namespace Freedompay\Common\Controller\Process;

use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Freedompay\Common\Logger\Logger;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection as PaymentCollection;
use Magento\Framework\Message\ManagerInterface;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;

/**
 * Process Freedompay response after redirect
 */
class Response implements ActionInterface
{
    /**
     * @var ResultRedirectFactory
     */
    private ResultRedirectFactory $resultRedirectFactory;

    /**
     * @var Context
     */
    private Context $context;

    /**
     * @var string
     */
    protected string $method = '';

    /**
     * @var \Freedompay\Common\Logger\Logger
     */
    private \Freedompay\Common\Logger\Logger $logger;

    /**
     * @var PaymentCollection
     */
    private PaymentCollection $paymentCollection;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * Response constructor.
     *
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param PaymentCollection $paymentCollection
     * @param Context $context
     * @param Logger $logger
     * @param ManagerInterface $messageManager
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        ResultRedirectFactory  $resultRedirectFactory,
        PaymentCollection      $paymentCollection,
        Context                $context,
        Logger                 $logger,
        ManagerInterface       $messageManager,
        DataPersistorInterface $dataPersistor
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->paymentCollection = $paymentCollection;
        $this->context = $context;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Cancel order if transaction fails
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        $paymentMethod = '';
        try {
            $params = $this->context->getRequest()->getParams();
            if ($this->dataPersistor->get('isAccountSaveCardAction')) {
                return $this->resultRedirectFactory->create()->setPath(
                    'freedompayhpp/process/response',
                    ['_query' => $params]
                );
            }
            $this->paymentCollection->getSelect()
                ->where("JSON_EXTRACT(additional_information, '$.transaction_id') = ?", $params['transid']);
            $payments = $this->paymentCollection->getItems();
            foreach ($payments as $payment) {
                $paymentMethod = $payment->getData('method');
            }
            switch ($paymentMethod) {
                case 'freedompay_hpp':
                    return $this->resultRedirectFactory->create()->setPath(
                        'freedompayhpp/process/response',
                        ['_query' => $params]
                    );
                case 'citipay_hpp':
                    return $this->resultRedirectFactory->create()->setPath(
                        'citipayhpp/process/response',
                        ['_query' => $params]
                    );
            }
            $this->messageManager->addErrorMessage(CommonConfig::ERR_MSG_GENERIC);
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        } catch (Exception $e) {
            $this->logger->error(__('Error while processing transaction status:: ' . $e->getMessage()));
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
