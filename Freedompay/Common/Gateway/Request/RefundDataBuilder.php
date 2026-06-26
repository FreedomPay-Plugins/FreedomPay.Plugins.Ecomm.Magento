<?php

namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Model\Data\Formatter;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Sales\Model\Order\Payment;

/**
 * Builds refund request data
 */
class RefundDataBuilder implements BuilderInterface
{
    /**
     * @var Formatter
     */
    private Formatter $formatter;

    /**
     * RefundDataBuilder constructor.
     *
     * @param Formatter $formatter
     */
    public function __construct(
        Formatter $formatter
    ) {
        $this->formatter = $formatter;
    }

    /**
     * Builds refund request data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();

        $creditMemo = $payment->getCreditMemo();

        if ($creditMemo) {
            return array_filter(
                [
                    RequestHelper::PURCHASE_TOTALS =>
                        [
                            RequestHelper::CHARGE_AMOUNT =>
                                $this->formatter->formatPrice($creditMemo->getBaseGrandTotal())
                        ]
                ]
            );
        }

        return [];
    }
}
