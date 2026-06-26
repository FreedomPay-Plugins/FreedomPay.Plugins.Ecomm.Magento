<?php
namespace Freedompay\Common\Model\Cart;

use Freedompay\Common\Helper\Requests as RequestHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

/**
 *
 * Manage Quote transactions
 */
class QuoteManager
{
    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @var PaymentDataObjectFactory
     */
    protected PaymentDataObjectFactory $paymentDataObjectFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $quoteRepository;

    /**
     * QuoteManager constructor.
     * @param Session $checkoutSession
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Session $checkoutSession,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Get Quote
     *
     * @return CartInterface|Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote(): CartInterface|Quote
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Reserve order id
     *
     * @param CartInterface|Quote $quote
     * @return void
     */
    public function reserveOrderId(CartInterface|Quote $quote):void
    {
        if (!$quote->getReservedOrderId()) {
            /** @var Quote $quote */
            $quote = $quote->reserveOrderId();/** @phpstan-ignore-line */
            $this->quoteRepository->save($quote);
        }
    }

    /**
     * Unset reserved order id
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function unsetReserveOrderId():void
    {
        $quote = $this->getQuote();
        $quote->setReservedOrderId(null);/** @phpstan-ignore-line */
        $this->quoteRepository->save($quote);
    }

    /**
     * Set additional information to quote payment
     *
     * @param string|null $transactionId
     * @param CartInterface|Quote|null $quote
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setPaymentAdditionalInformation(
        ?string $transactionId = null,
        CartInterface|Quote|null $quote = null
    ):void {
        if (!$quote) {
            $quote = $this->getQuote();
        }
        /** @var Quote $quote */
        $quotePayment = $quote->getPayment();
        if (!$transactionId) {
            $transactionId = $quotePayment->getId() . strtotime('now');
        }
        $quotePayment->setAdditionalInformation(RequestHelper::TRANSACTION_ID, $transactionId);
        $quote->setPayment($quotePayment);
        $this->quoteRepository->save($quote);
    }

    /**
     * Disable quote after successful payment
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function disableQuote():void
    {
        $quote = $this->getQuote();
        /** @phpstan-ignore-next-line */
        if (!$quote || !$quote->getIsActive()) {
            return;
        }
        $quote->setIsActive(false);
        $this->quoteRepository->save($quote);
    }
}
