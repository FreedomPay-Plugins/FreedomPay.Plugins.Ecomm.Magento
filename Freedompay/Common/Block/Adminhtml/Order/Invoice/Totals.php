<?php

namespace Freedompay\Common\Block\Adminhtml\Order\Invoice;

use Freedompay\Common\Model\Data\DynamicCurrency as DynamicCurrencyData;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Helper\Admin;

/**
 * Adminhtml sales totals block
 * Add DCC data to order totals
 *
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals
{
    /**
     * @var DynamicCurrencyData
     */
    private DynamicCurrencyData $dynamicCurrencyData;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param DynamicCurrencyData $dynamicCurrencyData
     * @param array<mixed> $data
     */
    public function __construct(
        Context             $context,
        Registry            $registry,
        Admin               $adminHelper,
        DynamicCurrencyData $dynamicCurrencyData,
        array               $data = []
    ) {
        \Magento\Sales\Block\Adminhtml\Totals::__construct(
            $context,
            $registry,
            $adminHelper,
            $data
        );
        $this->dynamicCurrencyData = $dynamicCurrencyData;
    }

    /**
     * Initialize order totals array
     *
     * Add DCC data to order totals
     *
     * @return $this
     */
    protected function _initTotals()
    {
        \Magento\Sales\Block\Adminhtml\Totals::_initTotals();
        $order = \Magento\Sales\Block\Adminhtml\Totals::getOrder();

        $dccData = $this->dynamicCurrencyData->getDCCData($order);
        if ($dccData) {
            $this->_totals['fp_dcc_total'] = new DataObject(
                [
                    'code' => 'fp_dcc_total',
                    'strong' => false,
                    'value' => $dccData['FormattedCurrency'],
                    'base_value' => $order->getGrandTotal(),
                    'label' => __('Grand Total in ' . $dccData['CurrencyCode']),
                    'area' => 'footer',
                ]
            );
        }
        return $this;
    }
}
