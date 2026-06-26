<?php

namespace Freedompay\Common\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Config\Config as PaymentConfig;
use Freedompay\Common\Gateway\Config\Config as CommonConfig;
use Freedompay\Common\Model\Adminhtml\Source\PaymentAction;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Helper class for requests
 */
class Requests extends AbstractHelper
{
    //API Method constants
    public const API_METHOD_POST   = 'POST';

    //Command constants
    public const CREATE_TRANSACTION     =   'createTransaction';
    public const GET_TRANSACTION        =   'getTransaction';
    public const GET_WEBHOOK_TRANSACTION        =   'getTransactionWebhook';

    //Service constants
    public const SERVICE_VOID     =   'void';
    public const SERVICE_CAPTURE  =   'capture';
    public const SERVICE_REFUND   =   'refund';

    //Content type constants
    public const CONTENT_TYPE_XML   = 'text/xml';
    public const CONTENT_TYPE_JSON  = 'application/json';

    //Response constants
    public const STATUS_ACCEPT          =   'ACCEPT';
    public const RESPONSE_SUCCESS_CODE  =   200;

    //Command end point constants

    public const END_POINT_CREATE_TRANSACTION               =   'CreateTransaction';
    public const END_POINT_GET_TRANSACTION                  =   'GetTransactionJSON';

    //Request/Response keys
    public const STORE_ID               =   'StoreId';
    public const TERMINAL_ID            =   'TerminalId';
    public const CSS_ID                 =   'CSSID';
    public const TRANSACTION_TOTAL      =   'TransactionTotal';
    public const ADDRESS_REQUIRED       =   'AddressRequired';
    public const ALLOW_INTRNL_ADDR      =   'AllowInternationalAddresses';
    public const CAPTURE_MODE           =   'CaptureMode';
    public const CULTURE_CODE           =   'CultureCode';
    public const INVOICE_NUMBER         =   'InvoiceNumber';
    public const MERCHANT_REF_CODE      =   'MerchantReferenceCode';
    public const TOKEN_VALUE            =   'TokenValue';
    public const CURRENCY_CODE          =   'CurrencyCode';
    public const SHOW_ADDRESS           =   'ShowAddress';
    public const BILLING_ADDRESS    =   'BillingAddress';
    public const SHIP_TO_ADDRESS    =   'ShipToAddress';

    public const CITY               =   'City';
    public const COUNTRY_CODE       =   'CountryCode';
    public const NAME               =   'Name';
    public const POSTAL_CODE        =   'PostalCode';
    public const STATE              =   'State';
    public const STREET_1           =   'Street1';
    public const STREET_2           =   'Street2';

    public const FRAUD_CHECK     =   'FraudCheck';
    public const FRAUD_CHECK_DATA     =   'FraudCheckData';
    public const PURCHASE_ITEMS     =   'PurchaseItems';
    public const DESCRIPTION        =   'Description';
    public const PRICE              =   'Price';
    public const TAX_TOTAL          =   'taxTotal';
    public const DISCOUNT_TOTAL     =   'discountTotal';

    public const INVOICE_ITEMS      =   'InvoiceItems';
    public const CHARGE_AMOUNT      =   'chargeAmount';
    public const DISPLAY_ORDER      =   'DisplayOrder';
    public const IS_VISIBLE         =   'IsVisible';
    public const LABEL              =   'Label';
    public const VALUE              =   'Value';

    public const LVL_3_ITEMS            =   'LevelThreeItems';
    public const DISCOUNT_AMOUNT        =   'DiscountAmount';
    public const ID                     =   'Id';
    public const PRODUCT_CODE           =   'ProductCode';
    public const PRODUCT_SKU            =   'ProductSKU';
    public const PRODUCT_DESCRIPTION    =   'ProductDescription';
    public const DISCOUNT_FLAG          =   'discountFlag';
    public const PRODUCT_NAME           =   'ProductName';
    public const PRODUCT_YEAR           =   'ProductYear';
    public const QUANTITY               =   'Quantity';
    public const SALE_CODE              =   'SaleCode';
    public const TAX_AMOUNT             =   'TaxAmount';
    public const TAX_INCLUDED_FLAG      =   'TaxIncludedFlag';
    public const TOTAL_AMOUNT           =   'TotalAmount';
    public const UNIT_OF_MEASURE        =   'UnitOfMeasure';
    public const UNIT_PRICE             =   'UnitPrice';
    public const CATEGORY               =   'Category';
    public const SYSTEM_NAME                =   'SystemName';
    public const SYSTEM_VERSION             =   'SystemVersion';
    public const MIDDLEWARE_NAME            =   'MiddlewareName';
    public const MIDDLEWARE_VERSION         =   'MiddlewareVersion';
    public const GET_TRANSACTION_ID         =   'TransactionId';
    public const CHECKOUT_TRANSACTION_ID    =   'CheckoutTransactionId';

    //Request keys for backoffice transactions
    public const BO_STORE_ID                = 'storeId';
    public const BO_TERMINAL_ID             = 'terminalId';
    public const BO_REQUEST_ID              = 'orderRequestID';
    public const BO_MERCHANT_REFERENCE_CODE = 'merchantReferenceCode';
    public const BO_INVOICE_HEADER          = 'invoiceHeader';
    public const BO_INVOICE_NUMBER          = 'invoiceNumber';
    public const CLIENT_METADATA            = 'ClientMetadata';
    public const CLIENT_METADATA_FREEWAY    = 'clientMetadata';
    public const SELLING_SYSTEM_NAME        = 'sellingSystemName';
    public const SELLING_SYSTEM_VERSION     = 'sellingSystemVersion';
    public const SELLING_MIDDLEWARE_NAME    = 'sellingMiddlewareName';
    public const SELLING_MIDDLEWARE_VERSION = 'sellingMiddlewareVersion';
    public const PURCHASE_TOTALS            = 'purchaseTotals';
    public const ITEMS                      = 'items';
    public const ITEM                       = 'item';

    //Custom constants
    public const TRANSACTION_ID             =   'transaction_id';
    public const DISCOUNT                   =   'Discount';
    public const TAX                        =   'Tax';
    public const GET_TRANSACTION_RETRY_COUNT =   3;
    public const WEBHOOK_GET_TRANSACTION_RETRY_COUNT =   1;
    public const GET_TRANSACTION_RESPONSE   =   'get_transaction_response';
    public const KEY_FREEWAY_VOID_RESPONSE      =   'freewayVoidResponse';
    public const KEY_FREEWAY_CAPTURE_RESPONSE   =   'freewayCaptureResponse';
    public const KEY_FREEWAY_REFUND_RESPONSE    =   'freewayRefundResponse';
    public const STATUS_PENDING         =   'PENDING';
    public const AUTH_RESPONSE = 'AuthResponse';
    public const FREEWAY_RESPONSE = 'FreewayResponse';
    public const DECISION = 'Decision';
    public const ORIGINAL_REQUEST = 'OriginalRequest';
    public const AMOUNT = 'Amount';

    public const REQUEST_PARAM_ESKEY = 'ESKey';
    public const REQUEST_FREEWAY_ESKEY = 'esKey';

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $paymentConfig;

    /**
     * @var CommonConfig
     */
    private CommonConfig $commonConfig;

    /**
     * @var ProductMetadataInterface
     */
    private ProductMetadataInterface $productMetadata;

    /**
     * @var ComponentRegistrarInterface
     */
    private ComponentRegistrarInterface $componentRegistrar;

    /**
     * @var ReadFactory
     */
    private ReadFactory $readFactory;

    /**
     * @param Context $context
     * @param PaymentConfig $paymentConfig
     * @param CommonConfig $commonConfig
     * @param ProductMetadataInterface $productMetadata
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     */
    public function __construct(
        Context $context,
        PaymentConfig $paymentConfig,
        CommonConfig $commonConfig,
        ProductMetadataInterface $productMetadata,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory
    ) {
        parent::__construct($context);
        $this->paymentConfig = $paymentConfig;
        $this->commonConfig = $commonConfig;
        $this->productMetadata = $productMetadata;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
    }

    /**
     * Get capture mode flag
     *
     * @return bool
     */
    public function isCaptureMode(): bool
    {
        return $this->paymentConfig->getValue(CommonConfig::KEY_PAYMENT_ACTION) == PaymentAction::ACTION_SALE;
    }

    /**
     * Get culture code
     *
     * @return string
     */
    public function getCultureCode(): string
    {
        return $this->commonConfig->getConfig(CommonConfig::XML_PATH_DEFAULT_LOCALE);
    }

    /**
     * Get Magento Edition
     *
     * @return string
     */
    public function getMagentoEdition(): string
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * Get magento version
     *
     * @return string
     */
    public function getMagentoVersion(): string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Get module version
     *
     * @param string $moduleName
     * @return string
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function getMagentoModuleVersion(string $moduleName): string
    {
        $path = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            $moduleName
        );
        if ($path) {
            $directoryRead = $this->readFactory->create($path);
            $composerJsonData = '';
            if ($directoryRead->isFile('composer.json')) {
                $composerJsonData = $directoryRead->readFile('composer.json');
            }
            $data = json_decode($composerJsonData);
        }
        return !empty($data->version) ? $data->version : '';
    }

    /**
     * Filter null values from array
     *
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public function removeNullValues(array $data): array
    {
        return array_filter($data, function ($val) {
            return $val !== null && $val !== '';
        });
    }
}
