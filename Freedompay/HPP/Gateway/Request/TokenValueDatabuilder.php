<?php

namespace Freedompay\HPP\Gateway\Request;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Builds Token Value
 */
class TokenValueDatabuilder implements BuilderInterface
{
    /**
     * @var RequestHelper
     */
    private RequestHelper $requestHelper;

    /**
     * TokenValueDatabuilder constructor.
     *
     * @param RequestHelper $requestHelper
     */
    public function __construct(
        RequestHelper $requestHelper
    ) {
        $this->requestHelper = $requestHelper;
    }

    /**
     * Builds ENV request
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $checkoutData = [
            RequestHelper::TOKEN_VALUE => $buildSubject['tokenValue'] ?? null
        ];
        return $this->requestHelper->removeNullValues($checkoutData);
    }
}
