<?php

namespace Citipay\HPP\Model\ResourceModel\Notification;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Citipay\HPP\Model\Notification as NotificationModel;
use Citipay\HPP\Model\ResourceModel\Notification as NotificationResource;

class Collection extends AbstractCollection
{
    /**
     * Initialize the notification collection class
     *
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(NotificationModel::class, NotificationResource::class);
    }
}
