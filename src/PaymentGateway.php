<?php

namespace Minic\LunarPaymentProcessor;

use Lunar\Facades\Payments;
use Minic\LunarPaymentProcessor\Contracts\PaymentDriverInterface;
use Lunar\Models\Cart;
use Stripe\PaymentIntent;

class PaymentGateway
{
    /**
     * The payment driver instance
     *
     * @var PaymentDriverInterface
     */
    protected PaymentDriverInterface $driver;

    /**
     * Create a new instance of the PaymentGateway
     *
     * @param PaymentDriverInterface $driver
     */
    public function __construct(PaymentDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Create a payment intent
     *
     * @param array $payload
     * @return Array
     */
    public function createPayment(Cart $cart, array $payload = []): Array
    {
        $existingId = $this->getCartIntentId($cart);

        if ($existingId) {
            return $this->fetchPayment($existingId);
        }

        $payload['amount'] = $cart->total->value;
        $payload['currency'] = $cart->currency->code;

        $paymentIntent = $this->driver->createPayment($payload);

        $cart->paymentIntents()->create([
            'intent_id' => $paymentIntent->id,
            'status' => $paymentIntent->status,
        ]);

        $payment = Payments::driver('card')->cart($cart)->withData([
            'payment_intent' => $paymentIntent->id,
            'authorized' => $paymentIntent->status,
        ])->authorize();

        return [
            'orderId' => $payment->orderId,
            'redirectUrl' => $paymentIntent->url,
        ];
    }

    /**
     * Update the payment intent
     *
     * @param string $intentId
     * @param array $payload
     * @return void
     */
    public function updatePayment(string $intentId, array $payload = [])
    {
        $this->driver->updatePayment($intentId, $payload);
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
        $paymentId = $this->getCartIntentId($cart);

        if (! $paymentId) {
            return;
        }

        $this->driver->cancelPayment($cart, $reason);

        $cart->paymentIntents()->where('intent_id', $paymentId)->update([
            'status' => PaymentIntent::STATUS_CANCELED,
            'processing_at' => now(),
            'processed_at' => now(),
        ]);
    }

    /**
     * Fetch a payment intent by ID
     *
     * @param string $paymentId
     * @return mixed
     */
    public function fetchPayment(string $paymentId)
    {
        return $this->driver->fetchPayment($paymentId);
    }

    /**
     * Get the list of transactions for a specific payment intent
     *
     * @param string $paymentId
     * @return mixed
     */
    public function getTransactions(string $paymentId)
    {
        return $this->driver->getTransactions($paymentId);
    }

    /**
     * Get the payment intent ID from the cart
     *
     * @param Cart $cart
     * @return string|null
     */
    public function getCartIntentId(Cart $cart): ?string
    {
        return $cart->meta['payment_intent'] ?? $cart->paymentIntents()->active()->first()?->intent_id;
    }
}
