<?php
namespace Freedompay\Common\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Gets payment action options
 */
class PaymentAction implements OptionSourceInterface
{
    /**
     * Different payment actions.
     */
    public const ACTION_AUTHORIZE       =   'authorize';
    public const ACTION_SALE            =   'capture';

    public const DEFAULT_LABEL          =   '--Please Select--';
    public const ACTION_AUTHORIZE_LABEL =   'Auth Only';
    public const ACTION_SALE_LABEL      =   'Sale';

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
                'value' => self::ACTION_AUTHORIZE,
                'label' => self::ACTION_AUTHORIZE_LABEL
            ],
            [
                'value' => self::ACTION_SALE,
                'label' => self::ACTION_SALE_LABEL
            ]
        ];
    }
}
