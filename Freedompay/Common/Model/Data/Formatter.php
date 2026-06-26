<?php
namespace Freedompay\Common\Model\Data;

/**
 * Formats data
 */
class Formatter
{
    /**
     * Format amount to two decimal points
     *
     * @param float $amount
     * @return string
     */
    public function formatPrice(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
