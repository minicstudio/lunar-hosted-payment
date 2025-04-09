<?php

namespace Minic\LunarStripePayment;

use Exception;
use Lunar\Facades\Payments;
use Minic\LunarStripePayment\Contracts\StripeDriverInterface;
use Lunar\Models\Cart;

class StripeGateway
{
    /**
     * The payment driver instance
     *
     * @var StripeDriverInterface
     */
    protected StripeDriverInterface $driver;

    /**
     * Create a new instance of the StripeGateway
     *
     * @param StripeDriverInterface $driver
     */
    public function __construct(StripeDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Create a payment intent
     *
     * @param Cart $cart
     * @param array $payload
     * @return Array
     * @throws Exception
     */
    public function createPayment(Cart $cart, array $payload = []): Array
    {
        $payload['amount'] = $cart->total->value;
        $payload['currency'] = $cart->currency->code;

        try {
            $paymentIntent = $this->driver->createPayment($payload);
        } catch (Exception $e) {
            // throwing general exception because there can be several different errors from the payment provider
            throw new Exception('Failed to create payment intent: ' . $e->getMessage());
        }

        return $paymentIntent->toArray();
    }

    /**
     * Authorize the payment, create order
     *
     * @param Cart $cart
     * @param string $intentId
     * @return string
     */
    public function authorize(Cart $cart, string $intentId): string
    {
        $cart->paymentIntents()->create([
            'intent_id' => $intentId,
            'status' => $this->config['authorized'] ?? 'payment-received',
        ]);

        $payment = Payments::driver('card')->cart($cart)->withData([
            'payment_intent' => $intentId,
        ])->authorize();

        return $payment->orderId;
    }

    /**
     * Retrieve the payment
     *
     * @param string $sessionId
     * @return Array
     */
    public function retrievePayment(string $sessionId): Array
    {
        try {
            $payment = $this->driver->retrievePayment($sessionId);
        } catch (Exception $e) {
            // throwing general exception because there can be several different errors from the payment provider
            throw new Exception('Failed to retrieve payment: ' . $e->getMessage());
        }

        return $payment->toArray();
    }
}
