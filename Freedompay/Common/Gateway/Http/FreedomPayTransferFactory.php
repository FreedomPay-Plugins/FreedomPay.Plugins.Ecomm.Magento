<?php
namespace Freedompay\Common\Gateway\Http;

use Freedompay\Common\Gateway\Config\PaymentConfig;
use Freedompay\Common\Helper\Requests;
use Freedompay\Common\Model\MandatoryFieldValidation;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * TransferFactory for Freedompay storefront transaction commands
 */
class FreedomPayTransferFactory implements TransferFactoryInterface
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
     * @var MandatoryFieldValidation
     */
    protected MandatoryFieldValidation $mandatoryFieldValidation;

    /**
     * @param TransferBuilder $transferBuilder
     * @param PaymentConfig $config
     * @param MandatoryFieldValidation $mandatoryFieldValidation
     * @param string $transactionType
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        PaymentConfig $config,
        MandatoryFieldValidation $mandatoryFieldValidation,
        string $transactionType = ''
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config= $config;
        $this->mandatoryFieldValidation = $mandatoryFieldValidation;
        $this->transactionType = $transactionType;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array<mixed> $request
     * @param PaymentDataObjectInterface $payment
     * @return TransferInterface
     */
    public function create(array $request, PaymentDataObjectInterface $payment):TransferInterface
    {
        if ($this->transactionType == Requests::END_POINT_CREATE_TRANSACTION &&
            $this->config->getValue('mandatory_validation')) {
            $this->mandatoryFieldValidation
                ->validateRequestData($request, $payment->getPayment()->getMethodInstance()->getCode());
        }

        return $this->transferBuilder
            ->setUri($this->config->getApiEndPoint() . '/' . $this->transactionType)
            ->setBody($request)
            ->setHeaders([ 'Content-Type' => Requests::CONTENT_TYPE_JSON])
            ->build();
    }
}
