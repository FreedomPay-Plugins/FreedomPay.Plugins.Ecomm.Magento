<?php

namespace Freedompay\Common\Gateway\Request;

use Freedompay\Common\Gateway\Config\Config as CommonConfig;
use Freedompay\Common\Gateway\Config\PaymentConfig;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Quote\Model\Quote;

/**
 * Class AddressDataBuilder
 * Class to build address data
 */
class AddressDataBuilder implements BuilderInterface
{
    public const COUNTRY_CODE_USA           = 'USA';
    public const COUNTRY_CODE_US            = 'US';

    public const BILLING_CITY               = 'city';
    public const BILLING_COUNTRY_ID         = 'countryId';
    public const BILLING_CUST_FIRST_NAME    = 'firstname';
    public const BILLING_CUST_LAST_NAME     = 'lastname';
    public const BILLING_POST_CODE          = 'postcode';
    public const BILLING_REGION_CODE        = 'regionCode';
    public const BILLING_STREET             = 'street';
    /**
     * @var PaymentConfig
     */
    private PaymentConfig $config;

    /**
     * @var RequestHelper
     */
    private RequestHelper $requestHelper;

    /**
     * @param PaymentConfig $config
     * @param RequestHelper $requestHelper
     */
    public function __construct(
        PaymentConfig $config,
        RequestHelper $requestHelper
    ) {
        $this->config = $config;
        $this->requestHelper = $requestHelper;
    }

    /**
     * Builds address data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $payment = SubjectReader::readPayment($buildSubject);

        $addressData = [];
        /** @var Quote $quote */
        $quote = $buildSubject['quote'];
        $order = $payment->getOrder();
        $billingAddress = $order->getBillingAddress();
        $isAddressRequired = $this->config->isEnabled(CommonConfig::KEY_ADDRESS_REQUIRED);
        if ($this->config->isEnabled(CommonConfig::KEY_SHOW_ADDRESS)) {
            $addressData[RequestHelper::SHOW_ADDRESS] = true;
        }
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getCountryId()) {
            $addressData[RequestHelper::SHIP_TO_ADDRESS] = $this->getAddressData($shippingAddress);
        }
        //If AddressRequired is true, no need to pass billing address information in the request
        if ($isAddressRequired) {
            $addressData[RequestHelper::ADDRESS_REQUIRED] = true;
            return $addressData;
        }
        //If quote is virtual, billing address will be empty in order object.
        //Hence, get billing address from the request param
        if ($quote->isVirtual()) {
            $address = $buildSubject['billingAddress'] ?? null;
            if ($address) {
                $addressData[RequestHelper::BILLING_ADDRESS] =
                    $this->getBillingAddressData(json_decode($address, true));
            }
        } elseif ($billingAddress) {
            $addressData[RequestHelper::BILLING_ADDRESS] = $this->getAddressData($billingAddress);
        }
        return $this->requestHelper->removeNullValues($addressData);
    }

    /**
     * Get address data
     *
     * @param AddressAdapterInterface $address
     * @return array<mixed>
     */
    public function getAddressData(AddressAdapterInterface $address): array
    {
        return [
            RequestHelper::CITY => $address->getCity(),
            RequestHelper::COUNTRY_CODE => $address->getCountryId() == self::COUNTRY_CODE_US ?
                self::COUNTRY_CODE_USA : $address->getCountryId(),
            RequestHelper::NAME => $address->getFirstname(),
            RequestHelper::POSTAL_CODE => $address->getPostcode(),
            RequestHelper::STATE => $address->getRegionCode(),
            RequestHelper::STREET_1 => $address->getStreetLine1(),
            RequestHelper::STREET_2 => $address->getStreetLine2(),
        ];
    }

    /**
     * Format the billing address from request params
     *
     * @param array<mixed> $address
     * @return array<mixed>
     */
    public function getBillingAddressData(array $address): array
    {
        $countryId = $address[self::BILLING_COUNTRY_ID];
        $count = count($address[self::BILLING_STREET]);
        return [
            RequestHelper::CITY => $address[self::BILLING_CITY],
            RequestHelper::COUNTRY_CODE => $countryId == self::COUNTRY_CODE_US ?
                self::COUNTRY_CODE_USA : $countryId,
            RequestHelper::NAME =>
                $address[self::BILLING_CUST_FIRST_NAME] . ' ' . $address[self::BILLING_CUST_LAST_NAME],
            RequestHelper::POSTAL_CODE => $address[self::BILLING_POST_CODE],
            RequestHelper::STATE => $address[self::BILLING_REGION_CODE],
            RequestHelper::STREET_1 => $address[self::BILLING_STREET][0],
            RequestHelper::STREET_2 => $count > 1 ? $address[self::BILLING_STREET][1] : ''
        ];
    }
}
