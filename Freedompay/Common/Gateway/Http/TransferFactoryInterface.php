<?php

namespace Freedompay\Common\Gateway\Http;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

interface TransferFactoryInterface
{
    /**
     * Build gateway transfer object
     *
     * @param array<mixed> $request
     * @param PaymentDataObjectInterface $payment
     * @return mixed
     */
    public function create(array $request, PaymentDataObjectInterface $payment): mixed;
}
