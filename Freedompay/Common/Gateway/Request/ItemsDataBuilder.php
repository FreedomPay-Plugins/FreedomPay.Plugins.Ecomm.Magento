<?php

namespace Freedompay\Common\Gateway\Request;

use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Quote\Model\Quote;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;
use Freedompay\Common\Model\Data\Formatter;
use Magento\Quote\Model\Quote\Address;
use Freedompay\Common\Gateway\Config\PaymentConfig;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;

/**
 * Builds items data
 */
class ItemsDataBuilder implements BuilderInterface
{
    public const CATEGORY_MISCELLANEOUS = 'Miscellaneous';
    /**
     * @var string|null
     */
    protected ?string $shippingMethod = null;

    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var CommonConfig
     */
    private CommonConfig $commonConfig;

    /**
     * @var RequestHelper
     */
    private RequestHelper $requestHelper;

    /**
     * @var float
     */
    private float $taxTotal = 0.0;

    /**
     * @var float
     */
    private float $discountTotal = 0.0;

    /**
     * @var Formatter
     */
    private Formatter $formatter;

    /**
     * @var PaymentConfig
     */
    protected PaymentConfig $config;

    /**
     * @var ProductRepository
     */
    protected ProductRepository $productRepository;

    /**
     * @var CategoryRepository
     */
    protected CategoryRepository $categoryRepository;

    /**
     * ItemsDataBuilder constructor.
     *
     * @param Session $checkoutSession
     * @param CommonConfig $commonConfig
     * @param RequestHelper $requestHelper
     * @param Formatter $formatter
     * @param PaymentConfig $config
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        Session      $checkoutSession,
        CommonConfig $commonConfig,
        RequestHelper $requestHelper,
        Formatter $formatter,
        PaymentConfig $config,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->commonConfig = $commonConfig;
        $this->requestHelper = $requestHelper;
        $this->formatter = $formatter;
        $this->config = $config;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Builds items request
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject): array
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $order = $payment->getOrder();

        /** @var Quote $quote */
        $quote = $buildSubject['quote'];
        $totalItemsCount = $quote->getItemsCount() ?: 0;

        if ($order->getItems() && $totalItemsCount > 0) {
            $quote = $this->checkoutSession->getQuote();
            $purchaseItems = $this->buildPurchaseItems($quote);
            $invoiceItems = $this->buildInvoiceItems($quote);
            $lvl3Items = $this->buildLvl3Items($quote);
            return $this->requestHelper->removeNullValues([
                RequestHelper::PURCHASE_ITEMS => $purchaseItems,
                RequestHelper::INVOICE_ITEMS => $invoiceItems,
                RequestHelper::LVL_3_ITEMS => $lvl3Items,
                RequestHelper::TAX_TOTAL => $this->formatter->formatPrice($this->taxTotal)
            ]);
        } else {
            return [];
        }
    }

    /**
     * Build purchase items
     *
     * @param Quote $quote
     * @return array <mixed>
     */
    protected function buildPurchaseItems(Quote $quote): array
    {
        $result = [];
        $discountTotal = 0;
        $quoteItems = $quote->getAllVisibleItems();

        foreach ($quoteItems as $item) {
            $itemDiscountTotal = $item->getBaseDiscountAmount();
            if ($item->getProductType() == Type::TYPE_BUNDLE) {
                $itemDiscountTotal = $this->getBundleProductDiscount($quote, (int)$item->getItemId());
            }
            $result[] = [
                RequestHelper::CHARGE_AMOUNT => $this->formatter->formatPrice($item->getBaseRowTotal()),
                RequestHelper::DESCRIPTION => substr((string)$item->getName(), 0, 100),
                RequestHelper::PRICE => $this->formatter->formatPrice($item->getBaseRowTotal()),
                RequestHelper::TAX_TOTAL => $this->formatter->formatPrice($item->getBaseTaxAmount()),
                RequestHelper::DISCOUNT_TOTAL => $this->formatter->formatPrice($itemDiscountTotal),
                RequestHelper::QUANTITY => $item->getQty()
            ];
            $discountTotal += $itemDiscountTotal;
        }
        $this->discountTotal = $discountTotal;
        return $result;
    }

    /**
     * Build invoice items
     *
     * @param Quote $quote
     * @return array<mixed>
     */
    protected function buildInvoiceItems(Quote $quote): array
    {
        $result = [];
        $count = 0;
        $shippingAmount = 0;

        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = $this->shippingMethod ?? $this->getShippingMethod($shippingAddress);
        if ($shippingMethod) {
            $count++;
            $shippingAmount = $shippingAddress->getBaseShippingAmount();
            $result[] = [
                RequestHelper::DISPLAY_ORDER => $count,
                RequestHelper::IS_VISIBLE => true,
                RequestHelper::LABEL => substr($shippingMethod, 0, 15),
                RequestHelper::VALUE => $this->formatter->formatPrice($shippingAmount)
            ];

            $discountAmount = $shippingAddress->getBaseDiscountAmount();
            if ($discountAmount < 0) {
                $count++;
                $result[] = [
                    RequestHelper::DISPLAY_ORDER => $count,
                    RequestHelper::IS_VISIBLE => true,
                    RequestHelper::LABEL => RequestHelper::DISCOUNT,
                    RequestHelper::VALUE => $this->formatter->formatPrice($discountAmount * -1)
                ];
            }
        } elseif ($this->discountTotal > 0) {//for virtual or downloadable products
            $count++;
            $result[] = [
                RequestHelper::DISPLAY_ORDER => $count,
                RequestHelper::IS_VISIBLE => true,
                RequestHelper::LABEL => RequestHelper::DISCOUNT,
                RequestHelper::VALUE => $this->formatter->formatPrice($this->discountTotal)
            ];
        }

        $this->taxTotal = $quote->getBaseGrandTotal() - $shippingAmount - $quote->getBaseSubtotalWithDiscount();
        if ($this->taxTotal > 0) {
            $count++;
            $result[] = [
                RequestHelper::DISPLAY_ORDER => $count,
                RequestHelper::IS_VISIBLE => true,
                RequestHelper::LABEL => RequestHelper::TAX,
                RequestHelper::VALUE => $this->formatter->formatPrice($this->taxTotal)
            ];
        }

        return $result;
    }

    /**
     * Build Lvl3 items
     *
     * @param Quote $quote
     * @return array<mixed>
     * @throws NoSuchEntityException
     */
    protected function buildLvl3Items(Quote $quote): array
    {
        $result = [];
        $quoteItems = $quote->getAllVisibleItems();
        $taxIncludedFlag = (bool)$this->commonConfig->getConfig(CommonConfig::TAX_CALCULATION_INCLUDING_TAX);
        $isFraudCheckEnabled = $this->config->isEnabled(CommonConfig::KEY_FRAUD_CHECK);
        $fraudOrder = $this->config->isEnabled(CommonConfig::KEY_FRAUD_ORDER);
        $fraudOrderVoidThreshold = $this->config->isEnabled(CommonConfig::KEY_FRAUD_VOID_THRESHOLD);
        $isFraudCheckEnabledFlag = ($isFraudCheckEnabled && $fraudOrder && $fraudOrderVoidThreshold);
        foreach ($quoteItems as $item) {
            $product = $item->getProduct();
            $categoryName = $isFraudCheckEnabledFlag ? $this->getProductCategoryName($product) : '';
            $discountAmount = 0;
            $discountFlag = false;
            $baseDiscountAmount = $item->getBaseDiscountAmount();
            $baseTaxAmount = $item->getBaseTaxAmount();

            if ($item->getProductType() == Type::TYPE_BUNDLE) {
                $discountAmount = $this->getBundleProductDiscount($quote, (int)$item->getItemId());
                if ($discountAmount > 0) {
                    $discountFlag = true;
                }
            } elseif ($item->getDiscountPercent() > 0 || $baseDiscountAmount > 0) {
                $discountFlag = true;
                $discountAmount = $baseDiscountAmount;
            }
            $itemSku = substr((string)$item->getSku(), 0, 30);
            $itemName = (string)$item->getName();
            $totalAmount = $taxIncludedFlag
                ? $item->getBaseRowTotal() + $baseTaxAmount - $discountAmount
                : $item->getBaseRowTotal() - $discountAmount;
            $itemData = [
                RequestHelper::PRODUCT_SKU => $itemSku,
                RequestHelper::PRODUCT_DESCRIPTION => substr($itemName, 0, 200),
                RequestHelper::DISCOUNT_AMOUNT => $this->formatter->formatPrice($discountAmount),
                RequestHelper::DISCOUNT_FLAG => $discountFlag,
                RequestHelper::PRODUCT_NAME => substr($itemName, 0, 35),
                RequestHelper::PRODUCT_YEAR => substr($item->getCreatedAt(), 0, 4),
                RequestHelper::QUANTITY => $item->getQty(),
                RequestHelper::SALE_CODE => 'S',
                RequestHelper::TAX_AMOUNT => $this->formatter->formatPrice($baseTaxAmount),
                RequestHelper::TOTAL_AMOUNT => $this->formatter->formatPrice($totalAmount),
                RequestHelper::UNIT_OF_MEASURE => substr(
                    $this->commonConfig->getConfig(CommonConfig::XML_PATH_WEIGHT_UNIT),
                    0,
                    3
                ),
                RequestHelper::UNIT_PRICE => $this->formatter->formatPrice($item->getPrice())
            ];
            if ($taxIncludedFlag) {
                $itemData[RequestHelper::TAX_INCLUDED_FLAG] = $taxIncludedFlag;
            }
            if (!empty($isFraudCheckEnabledFlag)) {
                $itemData[RequestHelper::CATEGORY] = $categoryName;
            }
            $result[] = $itemData;
        }

        if ($this->shippingMethod) {
            $shippingDetails = $this->buildShippingDetails($quote->getShippingAddress(), $isFraudCheckEnabledFlag);
            $result[] = $shippingDetails;
        }

        return array_filter($result);
    }

    /**
     * Get total discount amount of bundle product
     *
     * @param Quote $quote
     * @param int $itemId
     * @return float
     */
    public function getBundleProductDiscount(Quote $quote, int $itemId): float
    {
        $discount = 0;
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getParentItemId() != $itemId) {
                continue;
            }
            $discount += $quoteItem->getBaseDiscountAmount();
        }
        return $discount;
    }

    /**
     * Build shipping detail in lvl3 items
     *
     * @param Address $shippingAddress
     * @param bool $isFraudCheckEnabledFlag
     * @return array<mixed>
     */
    public function buildShippingDetails(Address $shippingAddress, bool $isFraudCheckEnabledFlag): array
    {
        $shippingMethod = $this->shippingMethod;
        $shippingTaxAmount = $shippingAddress->getBaseShippingTaxAmount();
        $shippingDiscountAmount = $shippingAddress->getBaseShippingDiscountAmount();
        $shippingAmount = $shippingAddress->getBaseShippingAmount();
        $shippingTotal = $shippingAmount + $shippingTaxAmount - $shippingDiscountAmount;
        $shippingData = [
            RequestHelper::PRODUCT_CODE => substr((string)$shippingMethod, 0, 15),
            RequestHelper::PRODUCT_NAME => substr((string)$shippingMethod, 0, 35),
            RequestHelper::QUANTITY => 1,
            RequestHelper::SALE_CODE => 'S',
            RequestHelper::TOTAL_AMOUNT => $this->formatter->formatPrice($shippingTotal),
            RequestHelper::UNIT_PRICE => $this->formatter->formatPrice($shippingAmount),
            RequestHelper::PRODUCT_DESCRIPTION => substr($shippingAddress->getShippingDescription(), 0, 200)
            ];
        if ($isFraudCheckEnabledFlag) {
            $shippingData[RequestHelper::CATEGORY] = "Shipping";
        }
        return $shippingData;
    }

    /**
     * Get Shipping method from shipping address
     *
     * @param Address $shippingAddress
     * @return string|null
     */
    public function getShippingMethod(Address $shippingAddress): string | null
    {
        if ($this->shippingMethod == null) {
            $shippingMethod = $shippingAddress->getShippingMethod();
            if ($shippingMethod !=null && str_contains($shippingMethod, '_')) {
                $this->shippingMethod = explode('_', $shippingMethod)[0];
            } else {
                $this->shippingMethod = $shippingMethod;
            }
        }
        return $this->shippingMethod;
    }

    /**
     * Get Product category name from product
     *
     * @param Product $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductCategoryName(Product $product): string
    {
        $categoryIds = $product->getCategoryIds();
        if (count($categoryIds) == 1) {
            $category = $this->categoryRepository->get($categoryIds[0]);
            $categoryName = $category->getName();
            if ($categoryName != null) {
                return $categoryName;
            }
        }
        $productName = $product->getName();
        if ($productName != null) {
            return substr($productName, 0, 100);
        }
        return self::CATEGORY_MISCELLANEOUS;
    }
}
