<?php
namespace Freedompay\Common\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Gets environment options
 */
class FraudVoidThreshold implements OptionSourceInterface
{
    /**
     * Fraud void threshold options array.
     *
     * @var array<mixed>
     */
    protected array $options = [
        ''  => 'Select',
        'A' => 'A (Automatically Reject a transaction for a “Review” or “Reject” response)',
        'R' => 'R (Transaction will be rejected but void won’t happen)',
        'I' => 'I (Automatically Reject a transaction for a “Reject” response)',
    ];

    /**
     * Return options as an array suitable for dropdown
     *
     * @return array<mixed>
     */
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->options as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $result;
    }
}
