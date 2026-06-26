<?php
namespace Citipay\HPP\Model\Api;

use Citipay\HPP\Helper\Requests as RequestHelper;
use Freedompay\Common\Helper\Requests as CommonRequestHelper;
use Freedompay\Common\Model\Api\ResponseManager as CommonResponseManager;

/**
 *
 * Manage Citipay api response data
 */
class ResponseManager extends CommonResponseManager
{
    /**
     * Check if response is valid
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isValidResponse(array $response): bool
    {
        if ($this->isStatusReview($response)) {
            return true;
        }
        if ($response && isset($response['OriginalRequest']) && $response['OriginalRequest']) {
            if ($response['OriginalRequest'][CommonRequestHelper::CAPTURE_MODE]) {
                return $this->isCaptured($response);
            } else {
                return $this->isAuthorized($response);
            }
        }
        return false;
    }

    /**
     * Method to check if StatusFlag is Accept or not
     *
     * @param array<mixed> $data
     * @return bool
     */
    public function isStatusReview(array $data): bool
    {
        if (isset($data['CreditApplicationInformation']['StatusFlag'])) {
            $statusFlag = $data['CreditApplicationInformation']['StatusFlag'];
            if ($statusFlag != RequestHelper::STATUS_ACCEPT_CODE) {
                return true;
            }
        }
        return false;
    }
}
