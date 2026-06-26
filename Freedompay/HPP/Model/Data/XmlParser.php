<?php
namespace Freedompay\HPP\Model\Data;

use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Convert\Xml as ConvertXml;
use Freedompay\Common\Model\Data\XmlParser as CommonXmlParser;
use Freedompay\HPP\Gateway\Config\PaymentConfig;
use Magento\Sales\Api\OrderRepositoryInterface;
use Freedompay\HPP\Model\Ui\ConfigProvider;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

/**
 * Parse XML response
 */
class XmlParser extends CommonXmlParser
{
    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @param ConvertArray $arrayConverter
     * @param ConvertXml $xmlConverter
     * @param PaymentConfig $config
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ConvertArray $arrayConverter,
        ConvertXml $xmlConverter,
        PaymentConfig $config,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        parent::__construct(
            $arrayConverter,
            $xmlConverter
        );
    }

    /**
     * Check if split capture is enabled in freedompay config
     *
     * @param int $orderId
     * @return bool
     */
    public function isSplitCaptureEnabled(int $orderId): bool
    {
        $order = $this->orderRepository->get($orderId);
        /** @var Payment $payment */
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethod();
        if ($paymentMethod == ConfigProvider::CODE) {
            return (bool) $this->config->getValue(PaymentConfig::KEY_SPLIT_CAPTURE);
        }
        return false;
    }
}
