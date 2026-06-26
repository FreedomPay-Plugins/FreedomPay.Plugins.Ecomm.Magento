<?php
namespace Freedompay\Common\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Gets environment options
 */
class Environment implements OptionSourceInterface
{
    /**
     * Environment option constants.
     */
    public const VALUE_ENVIRONMENT_SANDBOX      =   'test';
    public const VALUE_ENVIRONMENT_PRODUCTION   =   'live';

    public const LABEL_ENVIRONMENT_SANDBOX      =   'Test';
    public const LABEL_ENVIRONMENT_PRODUCTION   =   'Live';

    /**
     * Possible environment types
     *
     * @return array <mixed>
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::VALUE_ENVIRONMENT_SANDBOX,
                'label' => self::LABEL_ENVIRONMENT_SANDBOX
            ],
            [
                'value' => self::VALUE_ENVIRONMENT_PRODUCTION,
                'label' => self::LABEL_ENVIRONMENT_PRODUCTION
            ]
        ];
    }
}
