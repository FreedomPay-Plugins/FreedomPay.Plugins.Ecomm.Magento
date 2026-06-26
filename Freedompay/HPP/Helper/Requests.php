<?php

namespace Freedompay\HPP\Helper;

use Freedompay\Common\Helper\Requests as CommonRequestHelper;

/**
 * Helper class for requests
 */
class Requests extends CommonRequestHelper
{
    //Command constants
    public const CREATE_VERIFICATION_TRANSACTION = 'createVerificationTransaction';

    //Command end point constants
    public const END_POINT_CREATE_VERIFICATION_TRANSACTION  =   'CreateVerificationTransaction';
    public const CHALLENGE_INDICATOR    =   'ChallengeIndicator';
    public const AUTH_INDICATOR         =   'AuthenticationIndicator';
    public const DCC_ENABLED            =   'DCCenabled';
    public const DEFAULT_INDICATOR_VAL  =   '04';

    //Request/Response keys
    public const REQUEST_TOKEN          =   'RequestToken';
    public const TOKEN_TYPE             =   'TokenType';

    public const TIMEOUT_MINUTES        =   'TimeoutMinutes';
    public const FIELDS                 =   'Fields';
    public const FIELDS_KEY             =   'Key';
    public const FIELDS_VALUE           =   'Value';

    public const MASKED_CARD_NUMBER         =   'MaskedCardNumber';
    public const CARD_EXPIRATION_MONTH      =   'CardExpirationMonth';
    public const CARD_EXPIRATION_YEAR       =   'CardExpirationYear';
    public const CARD_ISSUER                =   'CardIssuer';
}
