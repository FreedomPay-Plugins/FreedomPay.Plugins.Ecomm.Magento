<?php

namespace Citipay\HPP\Model\PaymentEstimatorApi;

/**
 * Class for Payment estimator API constants
 */
class Constants
{

    // API Status Code Constants
    public const API_STATUS_401 = 401;

    //API Key Constants
    public const API_KEY_TOKEN = 'access_token';
    public const API_PARAM_SALE_AMOUNT = 'sale_amount';
    public const API_PARAM_GRANT_TYPE_NAME = 'grant_type';
    public const API_PARAM_GRANT_TYPE_VALUE = 'client_credentials';
    public const API_PARAM_CLIENT_ID = 'client_id';
    public const API_PARAM_CLIENT_SECRET = 'client_secret';
    public const API_PARAM_PROGRAM_NAME = 'program_name';
    public const API_PARAM_STORE_ID = 'storeId';
    public const API_PARAM_TERMINAL_ID = 'terminalId';
    public const API_SUCCESS_STATUS = 200;
    public const API_SUCCESS_STATUS_NON_QUALIFYING_AMOUNT = 206;
}
