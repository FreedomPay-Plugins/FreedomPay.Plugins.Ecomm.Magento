<?php

namespace Freedompay\Common\Logger;

use InvalidArgumentException;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * To remove the sensitive data from the log
 */
class RedactData
{
    /**
     * @var string[]
     */
    protected array $sensitiveFields = [
        'Name',
        'Street1',
        'CustomerEmail',
        'CardIssuer',
        'MaskedCardNumber',
        'CustomerPhoneNumber',
        'NameOnCard',
        'TokenInformation',
        'CardExpirationMonth',
        'CardExpirationYear',
        'Token',
        'TokenExpiration',
        'DynExpMonth',
        'DynExpYear',
        'FormattedDynExpMonth',
        'FormattedDynExpYear',
        'DateOfBirth',
        'AddressLine1',
        'EmailAddress',
        'Phone',
        'PhoneNumber',
        'NationalNumber',
        'GivenName',
        'Surname',
        'IP address',
        'Authorization',
        'access_token',
        'client_id',
        'client_secret',
        'TokenValue',
        'tokenValue',
        'PostalCode',
        'Street2',
        'AddressLine2',
        'AdminArea1',
        'AdminArea2',
        'BirthDate',
        'StoreId',
        'TerminalId',
        'PayerId',
        'ESKey',
        'esKey',
        'CvvResponse',
        'ClientAuthenticationKey',
        'requestToken',
        'postalCode',
        'TokenInfo',
        'CheckoutUrl'
    ];

    /**
     * @var Json
     */
    protected Json $json;

    /**
     * @param Json $json
     */
    public function __construct(
        Json $json
    ) {
        $this->json = $json;
    }

    /**
     * Remove Sensitive Data
     *
     * @param mixed $logData
     * @return string
     */
    public function redact(mixed $logData): string
    {
        if ($logData instanceof Phrase) {
            $logData = (string)$logData;
        }
        try {

            $unserializedData = $this->unserializeJson($logData);
            if ($unserializedData) {
                $logData = $unserializedData;
            }
            $data = $this->decodeNestedJson((array)$logData);
            return $this->filterData($data);
        } catch (InvalidArgumentException) {
            return $logData;
        }
    }

    /**
     * Convert sensitive data into masked data
     *
     * @param array<mixed> $data
     * @return string
     */
    protected function filterData(array $data): string
    {
        $filteredData = $this->maskDataRecursive($data);
        return (string)preg_replace('/\\\\/', '', (string)$this->json->serialize($filteredData));
    }

    /**
     * Mask Data
     *
     * @param array<mixed> $data
     * @return array<mixed>
     */
    protected function maskDataRecursive(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $data[$key] = $this->maskDataRecursive($value);
            } elseif (in_array($key, $this->sensitiveFields)) {
                $data[$key] = '*****';
            }
        }
        return $data;
    }

    /**
     * Decode Nested Json
     *
     * @param array<mixed> $decodedArray
     * @return array<mixed>
     */
    protected function decodeNestedJson(array $decodedArray): array
    {
        foreach ($decodedArray as $key => $value) {
            if (is_array($value)) {
                $decodedArray[$key] = $this->decodeNestedJson($value);
            } elseif (is_string($value)) {
                try {
                    $decodedArray[$key] = $this->json->unserialize($value);
                } catch (InvalidArgumentException) {
                    $decodedArray[$key] = $value;
                }
            }
        }
        return $decodedArray;
    }

    /**
     * Decode input parameter if it is valid JSON or return null
     *
     * @param mixed $data
     * @return mixed
     */
    protected function unserializeJson(mixed $data): mixed
    {
        if (!is_string($data)) {
            return null;
        }
        try {
            return $this->json->unserialize($data);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Redact XML string
     *
     * @param mixed $xml_string
     * @return string
     */
    public function redactXMLData(mixed $xml_string): string
    {
        $redact = '******';
        foreach ($this->sensitiveFields as $tag) {
            // Handle tag with namespace prefix (optional).
            $pattern   = '/<' . $tag . '>(.*?)<\/' . $tag . '>/i';
            $xml_string = preg_replace($pattern, "<$tag>$redact</$tag>", $xml_string);
        }
        return (string)$xml_string;
    }
}
