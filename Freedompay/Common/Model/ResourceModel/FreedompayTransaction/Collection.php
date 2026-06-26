<?php

namespace Freedompay\Common\Model\ResourceModel\FreedompayTransaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Freedompay\Common\Model\FreedompayTransaction as FreedompayTransactionModel;
use Freedompay\Common\Model\ResourceModel\FreedompayTransaction as FreedompayTransactionResource;

/**
 * Freedompay transaction collection class
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    public $_idFieldName = 'entity_id';

    /**
     * Initialize the freedompay transaction collection class
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(FreedompayTransactionModel::class, FreedompayTransactionResource::class);
    }
}
