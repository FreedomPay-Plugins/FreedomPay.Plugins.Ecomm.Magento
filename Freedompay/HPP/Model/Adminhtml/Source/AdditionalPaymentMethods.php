<?php

namespace Freedompay\HPP\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AdditionalPaymentMethods implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array<mixed>
     */
    public function toOptionArray()
    {
        return [
            ['value' => '2', 'label' => __('2 - Card on File')],
            ['value' => '5', 'label' => __('5 - GooglePay')],
            ['value' => '6', 'label' => __('6 - ApplePay')],
            ['value' => '8', 'label' => __('8 - PayPal')],
        ];
    }
}
