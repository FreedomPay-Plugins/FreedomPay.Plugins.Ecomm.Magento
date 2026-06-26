<?php

namespace Freedompay\HPP\Observer;

use Freedompay\Common\Model\Order\OrderManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Freedompay\Common\Model\Cart\QuoteManager;
use Freedompay\HPP\Model\Ui\ConfigProvider;
use Exception;

/**
 * Update Freedompay order status after place order
 */
class CheckoutSubmitAllAfter implements ObserverInterface
{
    public const NEW_ORDER_STATUS = Order::STATE_PENDING_PAYMENT;

    /**
     * @var OrderManager
     */
    private OrderManager $orderManager;

    /**
     * @var QuoteManager
     */
    private QuoteManager $quoteManager;

    /**
     * CheckoutSubmitAllAfter constructor.
     * @param OrderManager $orderManager
     * @param QuoteManager $quoteManager
     */
    public function __construct(
        OrderManager $orderManager,
        QuoteManager $quoteManager
    ) {
        $this->orderManager = $orderManager;
        $this->quoteManager = $quoteManager;
    }

    /**
     * Update order status after placing order
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        try {
            $orders = $observer->getEvent()->getData('orders');
            if ($orders && is_array($orders)) {
                foreach ($orders as $order) {
                    $this->processOrder($order);
                }
            } else {
                $order  = $observer->getEvent()->getData('order');
                $this->processOrder($order);
            }
            $this->quoteManager->unsetReserveOrderId();
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Process a single order and update its status
     *
     * @param Order $order
     * @return void
     */
    private function processOrder(Order $order): void
    {
        $paymentMethod = $order->getPayment() ? $order->getPayment()->getMethod() : null;
        if ($paymentMethod != ConfigProvider::CODE) {
            return;
        }
        $this->orderManager->updateOrderStatus(
            __('Order has been placed by Magento.'),
            self::NEW_ORDER_STATUS,
            self::NEW_ORDER_STATUS,
            (int)$order->getEntityId()
        );
    }
}
