<?php
namespace Citipay\HPP\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Citipay\HPP\Helper\Constants;

/**
 * Gets payment channel options
 */
class PaymentChannel implements OptionSourceInterface
{
    /**
     * Different payment actions.
     */
    public const CHANNEL_DLOC       =   Constants::CITIPAY_DLOC_VALUE;
    public const CHANNEL_MIL        =   Constants::CITIPAY_MIL_VALUE;

    public const DEFAULT_LABEL      =   '--Please Select--';
    public const CHANNEL_MIL_LABEL =   "10 - MIL";
    public const CHANNEL_DLOC_LABEL  =   '11 - DLOC';

    /**
     * Get the list of options
     *
     * @return array<mixed>
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => '',
                'label' => self::DEFAULT_LABEL
            ],
            [
                'value' => self::CHANNEL_MIL,
                'label' => self::CHANNEL_MIL_LABEL
            ],
            [
                'value' => self::CHANNEL_DLOC,
                'label' => self::CHANNEL_DLOC_LABEL
            ]
        ];
    }
}
