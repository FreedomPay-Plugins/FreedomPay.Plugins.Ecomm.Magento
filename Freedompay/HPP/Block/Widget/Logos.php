<?php
namespace Freedompay\HPP\Block\Widget;

use Magento\Backend\Block\Widget;
use Freedompay\Common\Block\Widget\Logos as CommonLogos;

/**
 * Widget for uploading multiple logos
 */
class Logos extends CommonLogos
{
    /**
     * Define block template for cc payment
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->setTemplate('Freedompay_HPP::widget/logos.phtml');
        Widget::_construct();
    }
}
