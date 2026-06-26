<?php

namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Gateway\Config\Config as CommonConfig;
use Magento\Catalog\Model\Product\Type;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item;
use Magento\Sales\Model\Order\Payment;
use Freedompay\Common\Model\Data\Formatter;

/**
 * Builds capture request data
 */
class CaptureDataBuilder implements BuilderInterface
{
    public const PRODUCT_NAME           =   'productName';
    public const PRODUCT_CODE           =   'productCode';
    public const QUANTITY               =   'quantity';
    public const PRODUCT_DESCRIPTION    =   'productDescription';
    public const UNIT_PRICE             =   'unitPrice';
    public const SALE_CODE              =   'saleCode';
    public const TOTAL_AMOUNT           =   'totalAmount';
    public const PRODUCT_SKU            =   'productSKU';
    public const DISCOUNT_AMOUNT        =   'discountAmount';
    public const DISCOUNT_FLAG          =   'discountFlag';
    public const TAX_AMOUNT             =   'taxAmount';
    public const TAX_INCLUDED_FLAG      =   'taxIncludedFlag';
    /**
     * @var Formatter
     */
    private Formatter $formatter;

    /**
     * @var CommonConfig
     */
    private CommonConfig $config;

    /**
     * CaptureDataBuilder constructor.
     *
     * @param Formatter $formatter
     * @param CommonConfig $config
     */
    public function __construct(
        Formatter $formatter,
        CommonConfig $config
    ) {
        $this->formatter = $formatter;
        $this->config = $config;
    }

    /**
     * Builds capture request data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();
        $order = $payment->getOrder();
        /** @var Invoice $latestInvoice */
        $latestInvoice = $order->getInvoiceCollection()->getLastItem();
        $isPartialCapture = $order->getBaseGrandTotal() != $latestInvoice->getBaseGrandTotal();
        $items = $this->buildL3Items($order, $isPartialCapture);
        $returnArray[RequestHelper::PURCHASE_TOTALS] = [
            RequestHelper::CHARGE_AMOUNT =>
                $this->formatter->formatPrice((float)$latestInvoice->getBaseGrandTotal())
        ];
        if (!$isPartialCapture) {
            $returnArray[RequestHelper::PURCHASE_TOTALS][RequestHelper::TAX_TOTAL] =
                $this->formatter->formatPrice((float)$latestInvoice->getBaseTaxAmount());
        }
        $returnArray[RequestHelper::ITEMS] = $items;
        return $returnArray;
    }

    /**
     * Build level 3 items
     *
     * @param Order $order
     * @param bool $isPartialCapture
     * @return array<mixed>
     */
    private function buildL3Items(Order $order, bool $isPartialCapture): array
    {
        /** @var Invoice $latestInvoice */
        $latestInvoice = $order->getInvoiceCollection()->getLastItem();
        $invoiceItems = $latestInvoice->getAllItems();
        $result = [];
        $taxIncludedFlag = (bool)$this->config->getConfig(CommonConfig::TAX_CALCULATION_INCLUDING_TAX);

        /** @var Item $item */
        foreach ($invoiceItems as $item) {

            if (!$item->getOrderItem()->getParentItemId()) {
                $discountFlag = false;
                $discountAmount = 0;
                $baseDiscountAmount = $item->getBaseDiscountAmount();
                if ($item->getOrderItem()->getProductType() == Type::TYPE_BUNDLE) {
                    $bundleResult = $this->getInvoiceDetailsForBundle(
                        $invoiceItems,
                        (int)$item->getOrderItem()->getItemId()
                    );
                    if ($bundleResult["invoiceDiscount"] > 0) {
                        $discountAmount = $bundleResult["invoiceDiscount"];
                        $discountFlag = true;
                    }
                    $totalAmount = $bundleResult["invoiceTotal"];
                    $taxAmount = $bundleResult["invoiceTax"];
                    $unitPrice = $bundleResult["invoiceUnitPrice"];
                } else {
                    if ($item->getDiscountAmount() > 0) {
                        $discountFlag = true;
                        $discountAmount = $baseDiscountAmount;
                    }
                    $totalAmount = $taxIncludedFlag
                        ? $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $discountAmount
                        : $item->getBaseRowTotal() - $discountAmount;
                    $taxAmount = $item->getBaseTaxAmount();
                    $unitPrice = $item->getBasePrice();
                }
                $itemSku = substr($item->getSku(), 0, 30);
                $itemName = (string)$item->getName();
                $itemData = [
                    self::PRODUCT_SKU => $itemSku,
                    self::PRODUCT_DESCRIPTION => $itemName,
                    self::DISCOUNT_AMOUNT => $this->formatter->formatPrice((float)$discountAmount),
                    self::DISCOUNT_FLAG => $discountFlag ? 'Y' : 'N',
                    self::PRODUCT_NAME => $itemName,
                    self::QUANTITY => $item->getQty(),
                    self::SALE_CODE => 'S',
                    self::TOTAL_AMOUNT => $this->formatter->formatPrice((float)$totalAmount),
                    self::UNIT_PRICE => $this->formatter->formatPrice((float)$unitPrice)
                ];
                if (!$isPartialCapture) {
                    $itemData[self::TAX_AMOUNT] = $this->formatter->formatPrice((float)$taxAmount);
                    if ($taxIncludedFlag) {
                        $itemData[self::TAX_INCLUDED_FLAG] = 'Y';
                    }
                }
                $result[] = $itemData;
            }
        }
        $shippingDetails = $this->buildShippingDetails($order);
        if (!empty($shippingDetails)) {
            $result[] = $shippingDetails;
        }
        return $result;
    }

    /**
     * Get total discount amount of bundle product
     *
     * @param array<mixed> $invoiceItems
     * @param int $itemId
     * @return array<mixed>
     */
    public function getInvoiceDetailsForBundle(array $invoiceItems, int $itemId): array
    {
        $result = [
            "invoiceTotal" => 0,
            "invoiceDiscount" => 0,
            "invoiceTax" => 0,
            "invoiceUnitPrice" => 0
        ];

        /** @var Item $item */
        foreach ($invoiceItems as $item) {
            if ($item->getOrderItem()->getParentItemId() != $itemId) {
                continue;
            }
            $baseDiscountAmount = $item->getBaseDiscountAmount();
            $baseTaxAmount = $item->getBaseTaxAmount();
            $result["invoiceDiscount"] += $baseDiscountAmount;
            $totalAmount = $item->getBaseRowTotal() + $baseTaxAmount - $baseDiscountAmount;
            $result["invoiceTotal"] += $totalAmount;
            $result["invoiceTax"] += $baseTaxAmount;
            $result["invoiceUnitPrice"] += $item->getPrice();
        }
        return $result;
    }

    /**
     * Add shipping detail to request
     *
     * @param Order $order
     * @return array<mixed>
     */
    public function buildShippingDetails(Order $order): array
    {
        $shippingAmount = $order->getBaseShippingAmount() ?? 0.0;
        $shippingTaxAmount = $order->getBaseShippingTaxAmount()?? 0.0;
        $shippingDiscountAmount = $order->getBaseShippingDiscountAmount()?? 0.0;
        $shippingTotal = $shippingAmount + $shippingTaxAmount - $shippingDiscountAmount;
        $shippingMethod = $order->getShippingMethod();
        if (!is_string($shippingMethod)) {
            $shippingMethod = '';
        }
        if ($shippingMethod) {
            return [
                self::PRODUCT_CODE => substr($shippingMethod, 0, 15),
                self::PRODUCT_NAME => $shippingMethod,
                self::QUANTITY => 1,
                self::SALE_CODE => 'S',
                self::TOTAL_AMOUNT => $this->formatter->formatPrice($shippingTotal),
                self::UNIT_PRICE => $this->formatter->formatPrice($shippingAmount),
                self::PRODUCT_DESCRIPTION => (string)$order->getShippingDescription()
            ];
        }
        return [];
    }
}
