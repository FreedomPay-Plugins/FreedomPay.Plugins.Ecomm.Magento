<?php

namespace Freedompay\Common\Block\Sales\Order;

use Freedompay\Common\Model\Data\DynamicCurrency as DynamicCurrencyData;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

/**
 * Freedompay Dynamic Currency Conversion totals block
 */
class Totals extends Template
{

    /**
     * @var DynamicCurrencyData
     */
    private DynamicCurrencyData $dynamicCurrencyData;

    /**
     * @param Template\Context $context
     * @param DynamicCurrencyData $dynamicCurrencyData
     * @param array<mixed> $data
     */
    public function __construct(
        Template\Context    $context,
        DynamicCurrencyData $dynamicCurrencyData,
        array               $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->dynamicCurrencyData = $dynamicCurrencyData;
    }

    /**
     * Get totals source object
     *
     * @return Order
     */
    public function getSource(): Order
    {
        /** @phpstan-ignore-next-line */
        return $this->getParentBlock()->getSource();
    }

    /**
     * Create the freedompay dynamic currency conversion totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $dccData = $this->dynamicCurrencyData->getDCCData($this->getSource());
        if ($dccData) {
            /** @phpstan-ignore-next-line */
            $this->getParentBlock()->addTotal(
                new DataObject(
                    [
                        'code' => 'fp_dcc_total',
                        'label' => __('Grand Total in ' . $dccData['CurrencyCode']),
                        'value' => $dccData['FormattedCurrency'],
                        'base_value' => $dccData['FormattedCurrency'],
                        'is_formated' => true,
                    ]
                )
            );
        }
        return $this;
    }
}
