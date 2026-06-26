<?php

namespace Freedompay\HPP\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Freedompay\HPP\Model\Ui\ConfigProvider;

/**
 * Set quote as active
 */
class SubmitQuoteObserver implements ObserverInterface
{
    /**
     * Keep cart active
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();/** @phpstan-ignore-line */
        $paymentMethod = $quote->getPayment()->getMethod();
        if ($paymentMethod !== \Freedompay\HPP\Model\Ui\ConfigProvider::CODE) {
            return;
        }
        // Keep cart active until such actions are taken
        $quote->setIsActive(true);
    }
}
