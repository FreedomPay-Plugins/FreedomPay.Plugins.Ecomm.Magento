<?php

namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Freedompay\Common\Model\Data\Formatter;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Quote\Model\Quote;

/**
 * Builds checkout data
 */
class CheckoutDataBuilder implements BuilderInterface
{
    /**
     * @var RequestHelper
     */
    private RequestHelper $requestHelper;

    /**
     * @var Formatter
     */
    private Formatter $formatter;

    /**
     * BaseRequestDataBuilder constructor.
     *
     * @param RequestHelper $requestHelper
     * @param Formatter $formatter
     */
    public function __construct(
        RequestHelper $requestHelper,
        Formatter $formatter
    ) {
        $this->requestHelper = $requestHelper;
        $this->formatter = $formatter;
    }

    /**
     * Builds ENV request
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        if (!isset($buildSubject['quote'])) {
            throw new \InvalidArgumentException('Quote data object should be provided');
        }

        /** @var Quote $quote */
        $quote = $buildSubject['quote'];

        $checkoutData = [
            RequestHelper::TRANSACTION_TOTAL => $this->formatter->formatPrice($quote->getBaseGrandTotal()),
            RequestHelper::INVOICE_NUMBER => $quote->getReservedOrderId(),
            RequestHelper::CURRENCY_CODE => $quote->getBaseCurrencyCode()
        ];

        if ($this->isCaptureMode()) {
            $checkoutData[RequestHelper::CAPTURE_MODE] = true;
        }

        return $this->requestHelper->removeNullValues($checkoutData);
    }

    /**
     * Check payment action in configuration
     *
     * @return bool
     */
    public function isCaptureMode(): bool
    {
        return $this->requestHelper->isCaptureMode();
    }
}
