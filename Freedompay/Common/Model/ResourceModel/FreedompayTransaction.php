<?php

namespace Freedompay\Common\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * FreedompayTransaction resource model class
 */
class FreedompayTransaction extends AbstractDb
{
    /**
     * Table name
     */
    public const TABLE = 'fp_payment_transaction';

    /**
     * Table primary key column name
     */
    public const TABLE_PRIMARY_KEY = 'entity_id';

    /**
     * Initialize the resource model with the table name and the primary key column name.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::TABLE, self::TABLE_PRIMARY_KEY);
    }
}
