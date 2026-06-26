<?php
namespace Freedompay\HPP\Model\Api;

use Freedompay\Common\Model\Api\ResponseManager as CommonResponseManager;

/**
 *
 * Manage Freedompay api response data
 */
class ResponseManager extends CommonResponseManager
{
    /**
     * Check if token information is available
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isTokenAvailable(array $response): bool
    {
        if (!$response) {
            return false;
        }
        $originalRequest = $response['OriginalRequest'] ?? null;
        $requestToken = is_array($originalRequest)
        && isset($originalRequest['RequestToken']) ? $originalRequest['RequestToken'] : null;
        $tokenInformation = $response['TokenInformation'] ?? null;
        return $requestToken
            && is_array($tokenInformation)
            && $tokenInformation['CardExpirationMonth']
            && $tokenInformation['CardExpirationYear']
            && $tokenInformation['Token']
            && $tokenInformation['TokenExpiration'];
    }
}
