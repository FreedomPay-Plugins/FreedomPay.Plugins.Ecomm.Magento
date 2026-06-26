<?php
namespace Freedompay\HPP\Block\Adminhtml\System\Config\Form\Field\Logos;

use Freedompay\HPP\Block\Widget\Logos;

/**
 * Frontend template for uploading multiple logos for Cc
 */
class Upload extends \Freedompay\Common\Block\Adminhtml\Form\Field\Logos\Upload
{
    /**
     * Get logo template
     *
     * @return Logos
     */
    public function getCardLogos(): Logos
    {
        /** @phpstan-ignore-next-line */
        return $this->getForm()->getParent()->getLayout()->createBlock(
            Logos::class
        );
    }
}
