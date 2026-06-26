<?php
namespace Freedompay\Common\Gateway\Http;

use Freedompay\Common\Helper\Requests;
use Freedompay\Common\Model\Data\XmlParser;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Freedompay\Common\Gateway\Config\PaymentConfig;

/**
 * TransferFactory for Freedompay backoffice transaction commands
 */
class FreedomPayBackOfficeTransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private TransferBuilder $transferBuilder;

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;
    /**
     * @var XmlParser
     */
    private XmlParser $xmlParser;
    /**
     * @var string
     */
    private string $serviceType;

    /**
     * @param TransferBuilder $transferBuilder
     * @param PaymentConfig $config
     * @param XmlParser $xmlParser
     * @param string $serviceType
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        PaymentConfig $config,
        XmlParser $xmlParser,
        string $serviceType
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config= $config;
        $this->xmlParser = $xmlParser;
        $this->serviceType = $serviceType;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array<mixed> $request
     * @param PaymentDataObjectInterface $payment
     * @return TransferInterface
     * @throws LocalizedException
     */
    public function create(array $request, PaymentDataObjectInterface $payment):TransferInterface
    {
        $order = $payment->getOrder();
        $orderId = $order->getId();
        return $this->transferBuilder
            ->setUri((string)$this->config->getSoapApiEndPoint())
            ->setBody($this->xmlParser->generateXmlBody($request, $this->serviceType, $orderId))
            ->setHeaders([ 'Content-Type' => Requests::CONTENT_TYPE_XML,
                'X-Service-Type' => $this->serviceType])
            ->build();
    }
}
