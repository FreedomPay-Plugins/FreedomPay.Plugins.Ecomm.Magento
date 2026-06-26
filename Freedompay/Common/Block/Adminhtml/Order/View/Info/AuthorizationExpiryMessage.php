<?php
namespace Freedompay\Common\Block\Adminhtml\Order\View\Info;

use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info;

/**
 * Checks whether an order was placed more than three days ago
 */
class AuthorizationExpiryMessage extends Info
{

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param DateTime $dateTime
     * @param array<mixed> $data
     * @param ShippingHelper|null $shippingHelper
     * @param TaxHelper|null $taxHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        DateTime $dateTime,
        array $data = [],
        ?ShippingHelper $shippingHelper = null,
        ?TaxHelper $taxHelper = null
    ) {
        parent::__construct($context, $registry, $adminHelper, $data, $shippingHelper, $taxHelper);
        $this->dateTime = $dateTime;
    }

    /**
     * Gets current order ID status
     *
     * @return bool|void
     */
    public function getAuthorizationExpiryStatus()
    {
        if (!$this->getOrder()->hasInvoices()) {
            $currentDate = $this->dateTime->gmtDate();
            $orderCreationDate = $this->getOrder()->getCreatedAt();
            if ($currentDate && $orderCreationDate) {
                $dateDifference = (strtotime($currentDate) - strtotime($orderCreationDate)) / (60 * 60 * 24);
                if ($dateDifference >= 3) {
                    return true;
                }
            }
        }
        return false;
    }
}
