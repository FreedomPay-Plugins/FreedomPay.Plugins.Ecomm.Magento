<?php

namespace Citipay\HPP\Helper;

/**
 * Helper class for notification module
 */
class Constants
{
    public const API_METHOD_GET     =   'GET';
    public const CUST_SESSION_ID = 'CUST_SESSION_ID';
    public const NOTIFICATION_STATUS_INVALID    =   'invalid';
    public const NOTIFICATION_STATUS_SUCCESS    =   'success';
    public const NOTIFICATION_STATUS_PROCESSING_ERROR      =   'processing_error';
    public const STATUS_ACCEPT          =   'ACCEPT';
    public const STATUS_PENDING         =   'PENDING';
    public const CITIPAY_MIL_VALUE = 10;
    public const CITIPAY_DLOC_VALUE = 11;
    public const CITIPAY_TYPE = 'citipay_type';
}
