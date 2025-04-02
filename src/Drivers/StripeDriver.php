<?php

namespace Minic\LunarPaymentProcessor\Drivers;

use Illuminate\Support\Collection;
use Lunar\Models\Cart;
use Minic\LunarPaymentProcessor\Contracts\PaymentDriverInterface;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripeDriver implements PaymentDriverInterface
{
    public function __construct()
    {
        // Set your secret Stripe API key
        Stripe::setApiKey(config('payment.stripe.secret_key'));
    }

    /**
     * Return the Stripe client
     */
    public function getClient(): StripeClient
    {
        return new StripeClient([
            'api_key' => config('payment.stripe.secret_key'),
        ]);
    }

    protected function getCartIntentId(Cart $cart): ?string
    {
        return $cartModel->meta['payment_intent'] ?? $cart->paymentIntents()->active()->first()?->intent_id;
    }

    public function fetchOrCreateIntent(Cart $cart, array $createOptions = []): PaymentIntent
    {
        $existingIntentId = $this->getCartIntentId($cart);

        $intent = $existingIntentId ? $this->fetchPayment($existingIntentId) : $this->createPayment($cart, $createOptions);

        /**
         * If the payment intent is stored in the meta, we don't have a linked payment intent
         * then it's a "legacy" cart, we should make a new record.
         */
        if (! empty($cart->meta['payment_intent']) && ! $cart->paymentIntents->first()) {
            $cart->paymentIntents()->create([
                'intent_id' => $intent->id,
                'status' => $intent->status,
            ]);
        }

        return $intent;
    }

    /**
     * Create a payment intent from a Cart
     */
    public function createPayment(Cart $cart, array $opts = [])
    {
        $existingId = $this->getCartIntentId($cart);

        if (
            $existingId &&
            $intent = $this->fetchPayment(
                $existingId
            )
        ) {
            return $intent;
        }

        $paymentIntent = $this->buildIntent(
            $cart->total->value,
            $cart->currency->code,
            $opts
        );

        $cart->paymentIntents()->create([
            'intent_id' => $paymentIntent->id,
            'status' => $paymentIntent->status,
        ]);

        return $paymentIntent;
    }

    public function updateShippingAddress(Cart $cart): void
    {
        $address = $cart->shippingAddress;

        if (! $address) {
            $this->updateIntent($cart, [
                'shipping' => [
                    'name' => "{$address->first_name} {$address->last_name}",
                    'phone' => $address->contact_phone,
                    'address' => [
                        'city' => $address->city,
                        'country' => $address->country->iso2,
                        'line1' => $address->line_one,
                        'line2' => $address->line_two,
                        'postal_code' => $address->postcode,
                        'state' => $address->state,
                    ],
                ],
            ]);
        }
    }

    public function updateIntent(Cart $cart, array $values): void
    {
        $intentId = $this->getCartIntentId($cart);

        if (! $intentId) {
            return;
        }

        $this->updateIntentById($intentId, $values);
    }

    public function updateIntentById(string $id, array $values): void
    {
        $this->getClient()->paymentIntents->update(
            $id,
            $values
        );
    }

    public function syncIntent(Cart $cart): void
    {
        $intentId = $this->getCartIntentId($cart);

        if (! $intentId) {
            return;
        }

        $cart = $cart->calculate();

        $this->getClient()->paymentIntents->update(
            $intentId,
            ['amount' => $cart->total->value]
        );
    }

    public function cancelPayment(Cart $cart, string $reason = ''): void
    {
        $intentId = $this->getCartIntentId($cart);

        $this->getClient()->paymentIntents->cancel(
            $intentId,
            ['cancellation_reason' => $reason]
        );
        $cart->paymentIntents()->where('intent_id', $intentId)->update([
            'status' => PaymentIntent::STATUS_CANCELED,
            'processing_at' => now(),
            'processed_at' => now(),
        ]);
    }

    /**
     * Fetch an intent from the Stripe API.
     */
    public function fetchPayment(string $intentId): ?Collection
    {
        try {
            $intent = PaymentIntent::retrieve($intentId);
        } catch (InvalidRequestException $e) {
            return null;
        }

        return collect($intent);
    }

    public function getTransactions(string $paymentIntentId): Collection
    {
        try {
            return collect(
                $this->getClient()->charges->all([
                    'payment_intent' => $paymentIntentId,
                ])['data'] ?? null
            );
        } catch (\Exception $e) {
            //
        }

        return collect();
    }

    public function getCharge(string $chargeId): Charge
    {
        return $this->getClient()->charges->retrieve($chargeId);
    }

    /**
     * Build the intent
     */
    protected function buildIntent(int $value, string $currencyCode, array $opts = []): PaymentIntent
    {
        $params = [
            'amount' => $value,
            'currency' => $currencyCode,
            'automatic_payment_methods' => ['enabled' => true],
            'capture_method' => config('lunar.stripe.policy', 'automatic'),
        ];

        return PaymentIntent::create([
            ...$params,
            ...$opts,
        ]);
    }
}
