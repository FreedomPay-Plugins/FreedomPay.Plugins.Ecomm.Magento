<?php
declare(strict_types=1);

namespace Freedompay\Common\Api;

use Freedompay\Common\Api\Data\FreedompayTransactionInterface;

/**
 * Grid CRUD interface.
 * @api
 */
interface FreedompayTransactionRepositoryInterface
{

    /**
     * Save data
     *
     * @param FreedompayTransactionInterface $freedompayTransaction
     * @return mixed
     */
    public function save(FreedompayTransactionInterface $freedompayTransaction);

    /**
     * Get data by transaction id
     *
     * @param string $transactionId
     * @return mixed
     */
    public function getByTransactionId(string $transactionId): mixed;

    /**
     * Create model
     *
     * @return mixed
     */
    public function create();
}
