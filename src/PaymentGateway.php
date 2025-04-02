<?php

namespace Minic\LunarPaymentProcessor;

use Minic\LunarPaymentProcessor\Contracts\PaymentDriverInterface;
use Lunar\Models\Cart;

class PaymentGateway
{
    protected $driver;

    public function __construct(PaymentDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Create a payment intent
     *
     * @param Cart $cart
     * @param array $options
     * @return mixed
     */
    public function createPayment(Cart $cart, array $options = [])
    {
        return $this->driver->createPayment($cart, $options);
    }

    /**
     * Cancel the payment
     *
     * @param Cart $cart
     * @param string $reason
     * @return void
     */
    public function cancelPayment(Cart $cart, string $reason = '')
    {
        $this->driver->cancelPayment($cart, $reason);
    }

    /**
     * Fetch a payment intent by ID
     *
     * @param string $paymentIntentId
     * @return mixed
     */
    public function fetchPayment(string $paymentIntentId)
    {
        return $this->driver->fetchPayment($paymentIntentId);
    }

    /**
     * Get the list of transactions for a specific payment intent
     *
     * @param string $paymentIntentId
     * @return mixed
     */
    public function getTransactions(string $paymentIntentId)
    {
        return $this->driver->getTransactions($paymentIntentId);
    }
}
