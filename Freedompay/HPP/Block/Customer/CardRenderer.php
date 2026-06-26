<?php

namespace Freedompay\HPP\Block\Customer;

use Exception;
use Freedompay\HPP\Helper\Requests as RequestHelper;
use Freedompay\HPP\Model\Ui\ConfigProvider as FPConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

/**
 * Renders customer saved cards
 */
class CardRenderer extends AbstractCardRenderer
{

    /**
     * @var array|string[]
     */
    private array $availableCardTypes = [
        'amex'                  => 'AE',
        'visa'                  => 'VI',
        'mastercard'            => 'MC',
        'discover'              => 'DI',
        'jcb'                   => 'JCB',
        'switch/maestro'        => 'SM',
        'diners'                => 'DN',
        'solo'                  => 'SO',
        'maestro international' => 'MI',
        'maestro domestic'      => 'MD',
        'hipercard'             => 'HC',
        'elo'                   => 'ELO',
        'aura'                  => 'AU',
        'other'                 => 'OT'
    ];

    /**
     * @var string
     */
    private string $cardType = '';

    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === FPConfigProvider::CODE;
    }

    /**
     * Gets card's last 4 digits
     *
     * @return string
     */
    public function getNumberLast4Digits()
    {
        $tokenCardDetails =  $this->getTokenDetails();
        if ($tokenCardDetails && isset($tokenCardDetails[RequestHelper::MASKED_CARD_NUMBER])) {
            $last4Digits = substr($tokenCardDetails[RequestHelper::MASKED_CARD_NUMBER], -4);
        }
        return !empty($last4Digits) ? $last4Digits : '';
    }

    /**
     * Gets card's expiry date
     *
     * @return string
     * @throws Exception
     */
    public function getExpDate()
    {
        $tokenCardDetails =  $this->getTokenDetails();

        if (is_array($tokenCardDetails)
            && array_key_exists(RequestHelper::CARD_EXPIRATION_MONTH, $tokenCardDetails)
            && array_key_exists(RequestHelper::CARD_EXPIRATION_YEAR, $tokenCardDetails)) {
            return $tokenCardDetails[RequestHelper::CARD_EXPIRATION_MONTH] . '/' .
                $tokenCardDetails[RequestHelper::CARD_EXPIRATION_YEAR];
        }
        return '';
    }

    /**
     * Get type of the saving card
     *
     * @return string
     */
    public function getCardType()
    {
        $tokenCardDetails = $this->getTokenDetails();
        $tokenCardIssuer = '';
        /**
         * @var mixed $tokenCardDetails
         */
        if ($tokenCardDetails) {
            $tokenCardIssuer = $tokenCardDetails[RequestHelper::CARD_ISSUER];
            foreach ($this->availableCardTypes as $key => $val) {
                if ($key == strtolower($tokenCardIssuer)) {
                    $this->cardType = $val;
                    return $val;
                }
            }
        }
        return $tokenCardIssuer;
    }

    /**
     * Gets card icon url
     *
     * @return mixed
     */
    public function getIconUrl()
    {
        return $this->getIconForType($this->cardType)['url'];
    }

    /**
     * Gets card icon height
     *
     * @return int
     */
    public function getIconHeight()
    {
        return $this->getIconForType($this->cardType)['height'];
    }

    /**
     * Gets card icon width
     *
     * @return int
     */
    public function getIconWidth()
    {
        return $this->getIconForType($this->cardType)['width'];
    }
}
