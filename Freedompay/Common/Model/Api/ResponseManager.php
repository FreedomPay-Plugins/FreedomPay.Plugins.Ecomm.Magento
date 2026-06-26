<?php
namespace Freedompay\Common\Model\Api;

use Freedompay\Common\Helper\Requests as RequestHelper;

/**
 *
 * Manage Freedompay api response data
 */
class ResponseManager
{
    /**
     * Check if response is valid
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isValidResponse(array $response): bool
    {
        if ($response && isset($response['OriginalRequest']) && $response['OriginalRequest']) {
            if ($response['OriginalRequest'][RequestHelper::CAPTURE_MODE]) {
                return $this->isCaptured($response);
            } else {
                return $this->isAuthorized($response);
            }
        }
        return false;
    }

    /**
     * Check if transaction is authorized
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isAuthorized(array $response): bool
    {
        if (isset($response['AuthResponse']['FreewayResponse']['Decision']) &&
            $response['AuthResponse']['FreewayResponse']['Decision'] == RequestHelper::STATUS_ACCEPT
        ) {
            return true;
        }
        return $this->isAcceptFromFailedResponse($response);
    }

    /**
     * Check if transaction is captured
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isCaptured(array $response): bool
    {
        if (isset($response['CaptureResponse']['FreewayResponse']['Decision']) &&
            $response['CaptureResponse']['FreewayResponse']['Decision'] == RequestHelper::STATUS_ACCEPT
        ) {
            return true;
        }
        return $this->isAcceptFromFailedResponse($response);
    }

    /**
     * Check if failed response in the array has a FreewayResponse Decision of ACCEPT.
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isAcceptFromFailedResponse(array $response): bool
    {
        $freewayData = $this->getFreewayRequestIdFromFailedResponse($response);
        if (!empty($freewayData['decision'])
            && !empty($freewayData['freewayRequestId'])
            && $freewayData['decision'] == RequestHelper::STATUS_ACCEPT
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get FreewayRequestId and FreewayDecision from failedResponses
     *
     * @param array<mixed> $response
     * @return array<mixed>
     */
    public function getFreewayRequestIdFromFailedResponse(array $response): array
    {
        if (empty($response['FailedResponses']) || !is_array($response['FailedResponses'])) {
            return [];
        }
        $failedResponses = $response['FailedResponses'];
        foreach ($failedResponses as $failedResponse) {
            if (isset($failedResponse['FreewayResponse']['FreewayRequestId']) &&
                isset($failedResponse['FreewayResponse']['Decision'])
            ) {
                return [
                    'decision' => $failedResponse['FreewayResponse']['Decision'],
                    'freewayRequestId' => $failedResponse['FreewayResponse']['FreewayRequestId']
                ];
            }
        }
        return [];
    }

    /**
     * Get freewaydata from getTransaction response
     *
     * @param array<mixed> $response
     * @return array<mixed>
     */
    public function getFreewayDataFromResponse(array $response): array
    {
        $freewayResponse = null;
        if (isset($response['AuthResponse']['FreewayResponse']['FreewayRequestId'])) {
            $freewayResponse = $response['AuthResponse']['FreewayResponse'];
        } elseif (isset($response['CaptureResponse']['FreewayResponse']['FreewayRequestId'])) {
            $freewayResponse = $response['CaptureResponse']['FreewayResponse'];
        } elseif ($this->getFreewayRequestIdFromFailedResponse($response)) {
            $failedResponseFreewayData = $this->getFreewayRequestIdFromFailedResponse($response);
            if (isset($response['FreewayResponseCode'])) {
                return [
                    'freewayResponseCode' => $response['FreewayResponseCode'],
                    'freewayRequestId' => $failedResponseFreewayData['freewayRequestId']
                ];
            }
            return [];
        }
        if ($freewayResponse && isset($response['FreewayResponseCode'])) {
            return [
                'freewayResponseCode' => $response['FreewayResponseCode'],
                'freewayRequestId' => $freewayResponse['FreewayRequestId']
            ];
        }
        return [
            'freewayResponseCode' => 'null',
            'freewayRequestId' => 'null'
        ];
    }

    /**
     * Check if transaction is captured
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isAuthOnly(array $response): bool
    {
        return $this->isAuthorized($response) && !$this->isCaptured($response);
    }

    /**
     * Check if dynamic currency conversion is opted
     *
     * @param array<mixed> $response
     * @return array<mixed>
     */
    public function checkAndGetDCCData(array $response): array
    {
        $dccData = [];
        $key = '';

        $isAuthOnly = $this->isAuthOnly($response);
        $isCaptured = $this->isCaptured($response);

        if ($isCaptured) {
            $key = 'CaptureResponse';
        } elseif ($isAuthOnly) {
            $key = 'AuthResponse';
        }

        if ($key) {
            $isDccOptIn = isset($response[$key]['DCCOptIn']) && $response[$key]['DCCOptIn'];
            if ($isDccOptIn) {
                return $this->getDCCData($response, $isCaptured, $isAuthOnly);
            }
        }
        return $dccData;
    }

    /**
     * Check dynamic currency conversion data from response
     *
     * @param array<mixed> $response
     * @param bool $isCaptured
     * @param bool $isauthOnly
     * @return array<mixed>
     */
    public function getDCCData(array $response, bool $isCaptured = false, bool $isauthOnly = false): array
    {
        if ($isauthOnly) {
            return $response['AuthResponse']['DCCInfo'];
        } elseif ($isCaptured) {
            return $response['CaptureResponse']['DCCInfo'];
        }
        return [];
    }

    /**
     * Check if billing address update is required
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isBillingAddressUpdateRequired(array $response):bool
    {
        $originalRequest = $response['OriginalRequest'];
        if ($originalRequest[RequestHelper::ADDRESS_REQUIRED]
            && $originalRequest[RequestHelper::INVOICE_NUMBER]) {
            return true;
        }
        return false;
    }

    /**
     * Get FreewayRequestId of failed transactions
     *
     * @param array<mixed> $response
     * @return string
     */
    public function getFreewayRequestId(array $response): string
    {
        $freewayRequestIds = [];
        $failedResponses = $response['FailedResponses'] ?? [];
        if ($failedResponses) {
            foreach ($failedResponses as $failedResponse) {
                if (isset($failedResponse['FreewayResponse']['FreewayRequestId'])) {
                    $freewayRequestIds[] = $failedResponse['FreewayResponse']['FreewayRequestId'];
                }
            }
            return implode(', ', $freewayRequestIds);
        }
        return '';
    }
}
