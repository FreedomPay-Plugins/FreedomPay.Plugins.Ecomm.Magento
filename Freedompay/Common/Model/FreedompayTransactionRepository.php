<?php
declare(strict_types=1);

namespace Freedompay\Common\Model;

use Freedompay\Common\Api\FreedompayTransactionRepositoryInterface;
use Freedompay\Common\Api\Data\FreedompayTransactionInterface;
use Freedompay\Common\Model\ResourceModel\FreedompayTransaction as ResourceModel;
use Magento\Framework\Exception\CouldNotSaveException;
use Freedompay\Common\Model\ResourceModel\FreedompayTransaction\CollectionFactory;

/**
 * Class FreedompayTransactionRepository
 * Repository class for Freedompay transaction
 */
class FreedompayTransactionRepository implements FreedompayTransactionRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    protected $resource;

    /**
     * @var FreedompayTransactionFactory
     */
    protected $modelFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * FreedompayTransactionRepository constructor.
     * @param ResourceModel $resource
     * @param FreedompayTransactionFactory $modelFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ResourceModel $resource,
        FreedompayTransactionFactory $modelFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Save data to fp_payment_transaction table
     *
     * @param FreedompayTransactionInterface $freedompayTransaction
     * @return mixed|FreedompayTransactionInterface
     * @throws CouldNotSaveException
     */
    public function save(FreedompayTransactionInterface $freedompayTransaction)
    {
        try {
            /** @var FreedompayTransaction $freedompayTransaction */
            $this->resource->save($freedompayTransaction);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $freedompayTransaction;
    }

    /**
     * Create model
     *
     * @return mixed|FreedompayTransaction
     */
    public function create()
    {
        return $this->modelFactory->create();
    }

    /**
     * Get data by transaction id
     *
     * @param string $transactionId
     * @return mixed
     */
    public function getByTransactionId(string $transactionId): mixed
    {
        $transactions = $this->collectionFactory->create();
        $transactions->addFieldToFilter(FreedompayTransactionInterface::TRANSACTION_ID, (string)$transactionId);
        if ($transactions->count() > 0) {
            /** @var FreedompayTransaction $transaction */
            $transaction = $transactions->getFirstItem();
            return $transaction;
        }
        return null;
    }
}
