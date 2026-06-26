<?php
namespace Freedompay\Common\Model\Data;

use Exception;
use Freedompay\Common\Helper\Requests;
use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Convert\Xml as ConvertXml;
use Magento\Framework\Exception\LocalizedException;

/**
 * Parse XML response
 */
class XmlParser
{
    /**
     * @var ConvertArray
     */
    private ConvertArray $arrayConverter;

    /**
     * @var ConvertXml
     */
    private ConvertXml $xmlConverter;

    /**
     * @param ConvertArray $arrayConverter
     * @param ConvertXml $xmlConverter
     */
    public function __construct(
        ConvertArray $arrayConverter,
        ConvertXml $xmlConverter
    ) {
        $this->arrayConverter = $arrayConverter;
        $this->xmlConverter = $xmlConverter;
    }

    /**
     * Generate SOAP XML body
     *
     * @param array<mixed> $request
     * @param string $serviceType
     * @param int $orderId
     * @return string
     * @throws LocalizedException
     */
    public function generateXmlBody(array $request, string $serviceType, int $orderId): string
    {
        $itemsFlag = false;
        $itemsXml = '';
        if (isset($request['items'])) {
            $itemsXml = $this->buildItemsXml($request['items']);
            unset($request['items']);
            $itemsFlag = true;
        }
        $request = ['Body' =>['Submit' =>['request' => $request]]];
        $xmlData = $this->arrayConverter->assocToXml($request, 'Envelope');
        $xmlData = (string) $xmlData->saveXML();
        if ($itemsFlag) {
            $xmlData = str_replace(
                '</request>',
                $itemsXml . '</request>',
                $xmlData
            );
        }
        $replace = [
            '<Envelope>' => '<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">',
            '<Body>' => '<S:Body>',
            '<Submit>' => '<Submit xmlns="http://freeway.freedompay.com/">',
            '</Body>' => '</S:Body>',
            '</Envelope>' => '</S:Envelope>',
        ];
        $xmlData = $this->replaceXmlData($replace, $xmlData);
        switch ($serviceType) {
            case Requests::SERVICE_VOID:
                $replace =  [
                    '</request>' => '<voidService run="true"/></request>'
                ];
                break;
            case Requests::SERVICE_CAPTURE:
                if ($this->isSplitCaptureEnabled($orderId)) {
                    $replace =  [
                        '</request>' =>
                            '<ccCaptureService run="true">
                            <isSplitTransaction>true</isSplitTransaction>
                        </ccCaptureService></request>'
                    ];
                } else {
                    $replace =  [
                        '</request>' =>
                            '<ccCaptureService run="true">
                            <isSplitTransaction>false</isSplitTransaction>
                        </ccCaptureService></request>'
                    ];
                }

                break;
            case Requests::SERVICE_REFUND:
                $replace =  [
                    '</request>' => '<ccCreditService run="true"/></request>'
                ];
                break;
        }
        return $this->replaceXmlData($replace, $xmlData);
    }

    /**
     * Build Items XML
     *
     * @param array<mixed> $itemsArray
     * @return string
     */
    private function buildItemsXml(array $itemsArray): string
    {
        $itemsXml = "<items>";
        foreach ($itemsArray as $item) {
            $itemsXml .= "<item>";
            foreach ($item as $key => $value) {
                $itemsXml .= "<{$key}>{$value}</{$key}>";
            }
            $itemsXml .= "</item>";
        }
        $itemsXml .= "</items>";
        return $itemsXml;
    }

    /**
     * Replace xml data with required values
     *
     * @param array<mixed> $newData
     * @param string $xmlData
     * @return string
     */
    public function replaceXmlData(array $newData, string $xmlData): string
    {
        return str_replace(array_keys($newData), array_values($newData), $xmlData);
    }

    /**
     * Parse XML response
     *
     * @param string $response
     * @return array<mixed>
     * @throws Exception
     */
    public function parseResponse(string $response): array
    {
        $replace = [
            '<soap:Envelope' => '<Envelope',
            '<soap:Body>' => '<Body>',
            '</soap:Body>' => '</Body>',
            '</soap:Envelope>' => '</Envelope>',
        ];
        $response = $this->replaceXmlData($replace, $response);

        return $this->xmlConverter->xmlToAssoc(new \SimpleXMLElement($response));
    }

    /**
     * Add Tor service field to parameters
     *
     * @param string $params
     * @return string|null
     */
    public function addTorServiceField(string $params): string|null
    {
        $torServiceField = "<torService run=\"true\"/>";
        return preg_replace('/<\/request>/', "$torServiceField</request>", $params, 1);
    }

    /**
     * Check if split capture is enabled
     *
     * @param int $orderId
     * @return bool
     */
    public function isSplitCaptureEnabled(int $orderId): bool
    {
        return false;
    }
}
