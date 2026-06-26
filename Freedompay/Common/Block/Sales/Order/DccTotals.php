<?php

namespace Freedompay\Common\Block\Sales\Order;

use Freedompay\Common\Model\Data\DynamicCurrency as DynamicCurrencyData;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Invoice\Items;

/**
 * Freedompay Dynamic Currency Conversion totals block
 */
class DccTotals extends Items
{
    /**
     * @var DynamicCurrencyData
     */
    private DynamicCurrencyData $dynamicCurrencyData;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param DynamicCurrencyData $dynamicCurrencyData
     * @param array<mixed> $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DynamicCurrencyData $dynamicCurrencyData,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $data
        );
        $this->dynamicCurrencyData = $dynamicCurrencyData;
    }

    /**
     * Get html of DCC totals comments block
     *
     * @return array<mixed>
     */
    public function getDCCData(): array
    {
        return $this->dynamicCurrencyData->getDCCData($this->getOrder());
    }
}
