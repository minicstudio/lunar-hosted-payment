<?php

namespace Minic\LunarStripePayment;

use Exception;
use Lunar\Facades\Payments;
use Minic\LunarStripePayment\Contracts\StripeDriverInterface;
use Lunar\Models\Cart;
use Minic\LunarStripePayment\DTOs\PaymentPayload;
use Minic\LunarStripePayment\Exceptions\StripeException;

class StripeGateway
{
    /**
     * Create a new instance of the StripeGateway
     *
     * @param StripeDriverInterface $driver
     */
    public function __construct(protected StripeDriverInterface $driver)
    {
        // The driver is automatically set by constructor injection
    }

    /**
     * Create a payment intent
     *
     * @param PaymentPayload $payload
     * @return Array
     * @throws Exception
     */
    public function createPayment(PaymentPayload $payload): Array
    {
        try {
            $paymentIntent = $this->driver->createPayment($payload);
        } catch (Exception $e) {
            throw new StripeException('Failed to create payment intent: ' . $e->getMessage(), $e->getCode(), $e);
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
            throw new StripeException('Failed to retrieve payment: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $payment->toArray();
    }
}
