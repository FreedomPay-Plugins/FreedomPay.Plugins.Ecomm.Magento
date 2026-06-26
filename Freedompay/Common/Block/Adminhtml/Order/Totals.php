<?php

namespace Freedompay\Common\Block\Adminhtml\Order;

use Freedompay\Common\Model\Data\DynamicCurrency as DynamicCurrencyData;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Helper\Admin;

/**
 * Adminhtml order totals block
 * Add DCC data to order totals
 *
 * @api
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Order\Totals
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
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        DynamicCurrencyData $dynamicCurrencyData,
        array $data = []
    ) {
        parent::__construct(
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
        parent::_initTotals();
        $order = $this->getSource();
        $dccData = $this->dynamicCurrencyData->getDCCData($order);
        if ($dccData) {
            $this->_totals['fp_dcc_total'] = new DataObject(
                [
                    'code' => 'fp_dcc_total',
                    'strong' => true,
                    'value' => $dccData['FormattedCurrency'],
                    'base_value' => $order->getGrandTotal(),
                    'label' =>__('Grand Total in ' . $dccData['CurrencyCode']),
                    'area' => 'footer',
                ]
            );
            \Magento\Sales\Block\Adminhtml\Totals::addTotalBefore($this->_totals['fp_dcc_total'], 'paid');
        }
        return $this;
    }
}
