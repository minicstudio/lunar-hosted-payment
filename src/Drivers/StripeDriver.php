<?php

namespace Minic\LunarPaymentProcessor\Drivers;

use Illuminate\Support\Collection;
use Minic\LunarPaymentProcessor\Contracts\PaymentDriverInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripeDriver implements PaymentDriverInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('lunar-payment-processor.payment.providers.stripe.secret_key'));
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

    /**
     * Create a payment intent from a Cart
     * 
     * @param array $payload
     * @return Session
     */
    public function createPayment(array $payload = []): Session
    {
        $payment = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $payload['currency'],
                    'product_data' => [
                        'name' => 'Total payment',
                    ],
                    'unit_amount' => $payload['amount'],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $payload['success_url'],
            'cancel_url' => $payload['cancel_url'],
        ]);

        return $payment;
    }

    /**
     * Update a payment intent
     * 
     * @param string $intentId
     * @param array $payload
     */
    public function updatePayment(string $intentId, array $payload): void
    {
        $this->getClient()->paymentIntents->update(
            $intentId,
            $payload
        );
    }

    /**
     * Cancel a payment intent
     * 
     * @param string $paymentId
     * @param string $reason
     */
    public function cancelPayment(string $paymentId, string $reason = ''): void
    {
        $this->getClient()->paymentIntents->cancel(
            $paymentId,
            ['cancellation_reason' => $reason]
        );
        
    }

    /**
     * Fetch an intent from the Stripe API.
     * 
     * @param string $intentId
     */
    public function fetchPayment(string $intentId): ?Collection
    {
        try {
            $payment = PaymentIntent::retrieve($intentId);
        } catch (InvalidRequestException $e) {
            return null;
        }

        return collect($payment);
    }

    public function getTransactions(string $paymentId): Collection
    {
        try {
            return collect(
                $this->getClient()->charges->all([
                    'payment_intent' => $paymentId,
                ])['data'] ?? null
            );
        } catch (\Exception $e) {
            //
        }

        return collect();
    }
}
