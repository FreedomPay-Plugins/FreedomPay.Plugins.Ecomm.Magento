<?php
namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Builds payment additional information data
 */
class AdditionalInformationDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private string $transactionType;

    /**
     * @param string $transactionType
     */
    public function __construct(
        string $transactionType = ''
    ) {
        $this->transactionType = $transactionType;
    }
    /**
     * Builds ENV request
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject):array
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $additionalInformation = $payment->getPayment()->getAdditionalInformation();

        $freewayRequestId = '';
        $response = $additionalInformation['get_transaction_response'];
        switch ($this->transactionType) {
            case RequestHelper::SERVICE_VOID:
            case RequestHelper::SERVICE_CAPTURE:
                $freewayRequestId = $response['AuthResponse']['FreewayResponse']['FreewayRequestId'];
                break;
            case RequestHelper::SERVICE_REFUND:
                $freewayRequestId = $this->getRefundRequestId($additionalInformation);
        };

        return [
            RequestHelper::BO_REQUEST_ID => $freewayRequestId,
            RequestHelper::BO_MERCHANT_REFERENCE_CODE =>
                $response['MerchantReferenceCode'],
            RequestHelper:: BO_INVOICE_HEADER => [
                RequestHelper:: BO_INVOICE_NUMBER => $response['InvoiceNumber'],
            ]
        ];
    }

    /**
     * Get freeway request id for refund transaction
     *
     * @param array<mixed> $data
     * @return string
     */
    public function getRefundRequestId(array $data): string
    {
        if (isset($data[RequestHelper::KEY_FREEWAY_CAPTURE_RESPONSE])) {
            return $data[RequestHelper::KEY_FREEWAY_CAPTURE_RESPONSE]['requestID'];
        } elseif (isset($data['get_transaction_response']['CaptureResponse'])) {
            return $data['get_transaction_response']['CaptureResponse']['FreewayResponse']['FreewayRequestId'];
        }

        return '';
    }
}
