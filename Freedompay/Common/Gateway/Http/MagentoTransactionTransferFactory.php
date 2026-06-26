<?php
namespace Freedompay\Common\Gateway\Http;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * TransferFactory for magento transaction commands
 */
class MagentoTransactionTransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * AuthorizeCaptureTransferFactory constructor.
     *
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array<mixed> $request
     * @return TransferInterface
     */
    public function create(array $request):TransferInterface
    {
        return $this->transferBuilder
            ->setBody($request)
            ->setMethod(RequestHelper::API_METHOD_POST)
            ->setHeaders(
                [
                    RequestHelper::GET_TRANSACTION_ID => $request[RequestHelper::GET_TRANSACTION_ID]
                ]
            )
            ->build();
    }
}
