<?php
namespace Citipay\HPP\Gateway\Http;

use Citipay\HPP\Gateway\Config\PaymentConfig;
use Freedompay\Common\Helper\Requests;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * TransferFactory for Citipay webhook transaction commands
 */
class CitipayWebhookTransferFactory implements TransferFactoryInterface
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
     * @var string
     */
    private string $transactionType;

    /**
     * @param TransferBuilder $transferBuilder
     * @param PaymentConfig $config
     * @param string $transactionType
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        PaymentConfig $config,
        string $transactionType = Requests::END_POINT_GET_TRANSACTION
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config= $config;
        $this->transactionType = $transactionType;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array<mixed> $request
     * @return TransferInterface
     */
    public function create(array $request):TransferInterface
    {
        return $this->transferBuilder
            ->setUri($this->config->getApiEndPoint() . '/' . $this->transactionType)
            ->setBody($request)
            ->setHeaders([ 'Content-Type' => Requests::CONTENT_TYPE_JSON])
            ->build();
    }
}
