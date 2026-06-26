<?php

namespace Citipay\HPP\Gateway\Http;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

interface TransferFactoryInterface
{
    /**
     * Build gateway transfer object
     *
     * @param array<mixed> $request
     * @return mixed
     */
    public function create(array $request): mixed;
}
