<?php

namespace Minic\LunarPaymentProcessor;

use Exception;
use Illuminate\Support\Collection;
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
     * @param Cart $cart
     * @param array $payload
     * @return Array
     * @throws Exception
     */
    public function createPayment(Cart $cart, array $payload = []): Array
    {
        $payload['amount'] = $cart->total->value;
        $payload['currency'] = $cart->currency->code;

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
     * Update the payment intent
     *
     * @param string $intentId
     * @param array $payload
     * @return void
     */
    public function updatePayment(string $intentId, array $payload = []): void
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
    public function cancelPayment(Cart $cart, string $reason = ''): void
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
