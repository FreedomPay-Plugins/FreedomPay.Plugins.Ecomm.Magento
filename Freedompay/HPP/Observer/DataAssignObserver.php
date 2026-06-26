<?php
namespace Freedompay\HPP\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

/**
 * Gets additional data from frontend and sets to the backend
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * Sets the payment additional information
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $data = $this->readDataArgument($observer);

        $paymentInfo = $this->readPaymentModelArgument($observer);
        if ($data->getDataByKey('transaction_id') !== null) {
            $paymentInfo->setAdditionalInformation(
                'transaction_id',
                $data->getDataByKey('transaction_id')
            );
        }
    }
}
