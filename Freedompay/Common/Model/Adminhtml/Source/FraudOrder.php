<?php
namespace Freedompay\Common\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Gets environment options
 */
class FraudOrder implements OptionSourceInterface
{
    /**
     * Fraud order options array.
     *
     * @var array<mixed>
     */
    protected array $options = [
        ''  => 'Select',
        'A' => 'A (Request a pre-check before processor)',
        'P' => 'P (Request a post-check after processor)',
        'M' => 'M (Request a post-check after a decline)',
        'B' => 'B (Request both pre-check and post-check)',
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
