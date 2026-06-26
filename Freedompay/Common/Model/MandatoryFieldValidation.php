<?php
namespace Freedompay\Common\Model;

use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * class MandatoryFieldValidation - Check all mandatory fields are present in CreateTransaction request.
 */
class MandatoryFieldValidation
{
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Validate whether the request array has mandatory data
     *
     * @param array<mixed> $data
     * @param string $methodCode
     * @return void
     */
    public function validateRequestData(array $data, string $methodCode): void
    {
        $this->clearMissingFieldDataFromSession();
        $missingRequiredKeys = $this->validateRequiredKeys($methodCode, $data);
        $missingLevelThreeRequiredKeys = $this->validateRequiredKeysLevelThreeItems($data);
        $missingKeys = array_filter(array_merge($missingRequiredKeys, $missingLevelThreeRequiredKeys));
        if (!empty($missingKeys)) {
            $this->storeMissingFieldDataInSession($missingKeys);
        }
    }

    /**
     * Clear missing field data from session
     *
     * @return void
     */
    public function clearMissingFieldDataFromSession(): void
    {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->unsFreedomPayMissingFields();
    }

    /**
     * Store missing fields data in session
     *
     * @param array<mixed> $missingKeys
     * @return void
     */
    public function storeMissingFieldDataInSession(array $missingKeys): void
    {
        /** @phpstan-ignore-next-line */
        $this->checkoutSession->setFreedomPayMissingFields($missingKeys);
    }

    /**
     * Get missing field data from session
     *
     * @return array<mixed>|null
     */
    public function getMissingFieldDataFromSession(): ?array
    {
        /** @phpstan-ignore-next-line */
        return $this->checkoutSession->getFreedomPayMissingFields() ?? null;
    }

    /**
     * Check if request has required keys in it
     *
     * @param string $methodCode
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public function validateRequiredKeys(string $methodCode, array $data): array
    {
        $requiredKeys = [
            'StoreId',
            'TerminalId',
            'MerchantReferenceCode',
            'InvoiceNumber',
            'ClientMetadata.SystemName',
            'ClientMetadata.SystemVersion',
            'ClientMetadata.MiddlewareName',
            'ClientMetadata.MiddlewareVersion',
            'TransactionTotal',
            'taxTotal'
        ];
        if ($methodCode == 'freedompay_hpp') {
            $requiredKeys[] = 'TimeoutMinutes';
        }
        $missingKeys = [];
        foreach ($requiredKeys as $requiredKey) {
            if (str_contains($requiredKey, '.')) {
                $segments = explode('.', $requiredKey);
                $current = $data;
                foreach ($segments as $segment) {
                    if (!is_array($current) ||
                        !array_key_exists($segment, $current) ||
                        $current[$segment] == '' || $current[$segment] == null
                    ) {
                        $missingKeys[] = $requiredKey;
                        break;
                    }
                    $current = $current[$segment];
                }
            } else {
                if (!array_key_exists($requiredKey, $data) ||
                    $data[$requiredKey] == '' ||
                    $data[$requiredKey] == null
                ) {
                    $missingKeys[] = $requiredKey;
                }
            }
        }
        return $missingKeys;
    }

    /**
     * Check if request has required level three keys in it
     *
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public function validateRequiredKeysLevelThreeItems(array $data): array
    {
        // Shipping LevelThreeItem should not be considered for mandatory field check.
        // If the last LevelThreeItem does not have productSku in it, it is shipping LevelThreeItem and is removed.
        if (isset($data['LevelThreeItems']) && is_array($data['LevelThreeItems'])) {
            $lastIndex = array_key_last($data['LevelThreeItems']);
            if (isset($data['LevelThreeItems'][$lastIndex]) &&
                is_array($data['LevelThreeItems'][$lastIndex]) &&
                !isset($data['LevelThreeItems'][$lastIndex]['ProductSKU'])
            ) {
                unset($data['LevelThreeItems'][$lastIndex]);
            }
        }
        $requiredKeysLevelThreeItems = [
            'LevelThreeItems.*.ProductSKU',
            'LevelThreeItems.*.ProductName',
            'LevelThreeItems.*.ProductDescription',
            'LevelThreeItems.*.UnitPrice',
            'LevelThreeItems.*.Quantity',
            'LevelThreeItems.*.TotalAmount',
            'LevelThreeItems.*.TaxAmount',
            'LevelThreeItems.*.SaleCode'
        ];
        if (array_key_exists('FraudCheckData', $data) && array_key_exists('FraudCheck', $data)) {
            $requiredKeysLevelThreeItems[] = 'LevelThreeItems.*.Category';
        }
        $missingKeys = [];
        foreach ($requiredKeysLevelThreeItems as $requiredKeysLevelThreeItem) {
            [$levelThreeItem, $childKey] = explode('.*.', $requiredKeysLevelThreeItem);
            if (!isset($data[$levelThreeItem]) || !is_array($data[$levelThreeItem])) {
                $missingKeys[] = $requiredKeysLevelThreeItem;
                continue;
            }
            foreach ($data[$levelThreeItem] as $index => $item) {
                if (!array_key_exists($childKey, $item) || !$item[$childKey]) {
                    $missingKeys[] = "{$levelThreeItem}[{$index}].{$childKey}";
                }
            }
        }
        return $missingKeys;
    }
}
