<?php
namespace Freedompay\HPP\Model\Api;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Freedompay\Common\Model\Cart\QuoteManager;
use Freedompay\Common\Logger\Logger;
use Freedompay\Common\Model\Api\RequestManager as CommonRequestManager;

/**
 *
 * Manage Freedompay HPP api request transactions
 */
class RequestManager extends CommonRequestManager
{
    /**
     * RequestManager constructor.
     * @param CommandPoolInterface $commandPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param QuoteManager $quoteManager
     * @param Logger $logger
     * @param string $commandName
     */
    //phpcs:disable
    public function __construct(
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        QuoteManager $quoteManager,
        Logger $logger,
        string $commandName = ''
    ) {
        parent::__construct(
            $commandPool,
            $paymentDataObjectFactory,
            $quoteManager,
            $logger,
            $commandName
        );
    }

    /**
     * Add token details to payment command parameters
     *
     * @param array<mixed> $requestParams
     * @return array<mixed>
     */
    public function getTokenDetails($requestParams): array
    {
        $requestToken = $requestParams['request_token'] ?? false;
        $tokenValue = $requestParams['token_value'] ?? '';
        return [
            'requestToken' => (int)$requestToken,
            'tokenValue' => $tokenValue,
        ];
    }
}
