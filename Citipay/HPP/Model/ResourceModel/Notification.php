<?php

namespace Citipay\HPP\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Citipay\HPP\Api\Data\NotificationInterface;

/**
 * Notification resource model class
 */
class Notification extends AbstractDb
{
    /**
     * Initialize the resource model with the table name and the primary key column name.
     *
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(NotificationInterface::TABLE_NAME, NotificationInterface::ID);
    }
}
