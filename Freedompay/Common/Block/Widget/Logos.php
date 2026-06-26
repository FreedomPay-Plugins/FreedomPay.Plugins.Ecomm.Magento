<?php
namespace Freedompay\Common\Block\Widget;

use Magento\Backend\Block\Widget;

/**
 * Common widget for logo upload
 */
class Logos extends Widget
{
    /**
     * Get saved images
     *
     * @return array|mixed
     */
    public function getSavedImages(): mixed
    {
        $savedImages = $this->getData('images');
        if ($savedImages) {
            return json_decode($savedImages, true);
        }
        return [];
    }
}
