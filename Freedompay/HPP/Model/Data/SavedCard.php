<?php
namespace Freedompay\HPP\Model\Data;

use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenRepository;
use Magento\Vault\Model\ResourceModel\PaymentToken as PaymentTokenResourceModel;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;

/**
 *
 * Get saved cards information of the customer
 */
class SavedCard
{
    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $tokenCollectionFactory;

    /**
     * @var PaymentTokenRepository
     */
    private PaymentTokenRepository $paymentTokenRepository;

    /**
     * @var PaymentTokenFactoryInterface
     */
    private PaymentTokenFactoryInterface $paymentTokenFactory;

    /**
     * @var PaymentTokenResourceModel
     */
    protected $paymentTokenResourceModel;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * SavedCard constructor.
     * @param CustomerSession $customerSession
     * @param CollectionFactory $collectionFactory
     * @param PaymentTokenRepository $paymentTokenRepository
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param PaymentTokenResourceModel $paymentTokenResourceModel
     * @param EncryptorInterface $encryptor
     * @param Json $json
     */
    public function __construct(
        CustomerSession $customerSession,
        CollectionFactory $collectionFactory,
        PaymentTokenRepository $paymentTokenRepository,
        PaymentTokenFactoryInterface $paymentTokenFactory,
        PaymentTokenResourceModel $paymentTokenResourceModel,
        EncryptorInterface $encryptor,
        Json $json
    ) {
        $this->customerSession = $customerSession;
        $this->tokenCollectionFactory = $collectionFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenResourceModel = $paymentTokenResourceModel;
        $this->encryptor = $encryptor;
        $this->json = $json;
    }

    /**
     * Get customer saved cards
     *
     * @param string $methodCode
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function getCustomerSavedCards(string $methodCode):array
    {
        try {
            $customerCards = [];
            $customerId = $this->customerSession->getCustomerId();
            if ($customerId) {
                $collection = $this->tokenCollectionFactory->create();
                $collection
                    ->addFieldToFilter('customer_id', ['eq' => $customerId])
                    ->addFieldToFilter('payment_method_code', ['eq' => $methodCode])
                    ->addFieldToFilter('is_active', ['eq' => 1])
                    ->addFieldToFilter('is_visible', ['eq' => 1]);

                if ($collection->getSize()) {
                    $customerCards[] = [
                        'value' => '',
                        'text' => __('Or Add New Card')
                    ];
                    foreach ($collection->getItems() as $cardInfo) {
                        $cardDetails = $cardInfo->getData('details');
                        $cardValues = $this->json->unserialize($cardDetails);
                        $customerCards[] = [
                            'value' => $cardInfo->getData('gateway_token'),
                            'text' => $cardValues['MaskedCardNumber'] ?? ''
                        ];
                    }
                }
                return $customerCards;
            }
            return $customerCards;
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Save token details
     *
     * @param int|null $customerId
     * @param array<mixed> $data
     * @param string $methodCode
     * @return void
     */
    public function savePaymentToken(int|null $customerId, array $data, string $methodCode):void
    {
        if (!$customerId) {
            $customerId = $this->getCustomerId();
        }
        $cardIssuer = $data['CardIssuer'];
        $tokenInformation = $data['TokenInformation'];
        $tokenInformation['MaskedCardNumber'] = $data['MaskedCardNumber'];
        $token = $tokenInformation['Token'];
        $tokenInformation['CardIssuer'] = $cardIssuer;

        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setCustomerId($customerId);
        $paymentToken->setPublicHash($this->encryptor->getHash($token));
        $paymentToken->setPaymentMethodCode($methodCode);
        $paymentToken->setType(strtolower($data['PaymentType']));
        $paymentToken->setExpiresAt($tokenInformation['TokenExpiration']);
        $paymentToken->setGatewayToken($token);
        $paymentToken->setTokenDetails((string)json_encode($tokenInformation));
        $this->paymentTokenRepository->save($paymentToken);
    }

    /**
     * Checks if token is available and save/update token details
     *
     * @param mixed $response
     * @param int $customerId
     * @param string $method
     * @return void
     * @throws LocalizedException
     */
    public function savePaymentCard(mixed $response, int $customerId, string $method): void
    {
        if (!$customerId) {
            $customerId = $this->getCustomerId();
        }
        //Check if card with same token exists
        $existingTokenData = $this->getSavedCardByGatewayToken(
            $response['TokenInformation']['Token'],
            $method,
            $customerId
        );
        if (is_array($existingTokenData)) {
            $this->updateSavedCardData($existingTokenData['entity_id']);
        } else {
            $this->savePaymentToken(
                $customerId,
                $response,
                $method
            );
        }
    }

    /**
     * Update existing token
     *
     * @param int $entityId
     * @return void
     */
    public function updateSavedCardData(int $entityId): void
    {
        /** @var PaymentToken $token */
        $token = $this->paymentTokenRepository->getById($entityId);
        $token->setIsActive(true);
        $token->setIsVisible(true);
        $this->paymentTokenRepository->save($token);
    }

    /**
     * Get saved card data by gateway token
     *
     * @param string $token
     * @param string $paymentMethodCode
     * @param int $customerId
     * @return array<mixed>|bool
     * @throws LocalizedException
     */
    public function getSavedCardByGatewayToken(string $token, string $paymentMethodCode, int $customerId): array|bool
    {
        return $this->paymentTokenResourceModel->getByGatewayToken($token, $paymentMethodCode, $customerId);
    }

    /**
     * Get customer id from session
     *
     * @return mixed
     */
    public function getCustomerId(): mixed
    {
        return $this->customerSession->getCustomer()->getId();
    }
}
