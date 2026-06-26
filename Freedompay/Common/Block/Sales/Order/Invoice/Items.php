<?php

namespace Freedompay\Common\Block\Sales\Order\Invoice;

/**
 * Freedompay Dynamic Currency Conversion totals block
 */
class Items extends \Magento\Sales\Block\Order\Invoice\Items
{
    /**
     * Get html of invoice comments block
     *
     * @return string
     */
    public function getDCCTotalsHtml(): string
    {
        $html = '';
        $totals = $this->getChildBlock('fp_dcc_invoice_totals');
        if ($totals) {
            $html = $totals->toHtml();/** @phpstan-ignore-line */
        }
        return $html;
    }
}
