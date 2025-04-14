<?php

namespace Minic\LunarHostedPayment\Drivers;

use Minic\LunarHostedPayment\Contracts\HostedPaymentDriverInterface;
use Minic\LunarHostedPayment\DTOs\PaymentPayload;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeDriver implements HostedPaymentDriverInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('lunar-hosted-payment.payment.providers.stripe.secret_key'));
    }

    /**
     * Return the Stripe client
     * 
     * @return StripeClient
     */
    public function getClient(): StripeClient
    {
        return new StripeClient([
            'api_key' => config('lunar-hosted-payment.payment.providers.stripe.secret_key'),
        ]);
    }

    /**
     * Create a payment intent from a Cart
     * 
     * @param PaymentPayload $payload
     * @return Session
     */
    public function createPayment(PaymentPayload $payload): Session
    {
        $payment = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $payload->currency,
                    'product_data' => [
                        'name' => 'Total payment',
                    ],
                    'unit_amount' => $payload->amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $payload->successUrl,
            'cancel_url' => $payload->cancelUrl,
            'customer_email' => $payload->email ?? null,
        ]);

        return $payment;
    }

    /**
     * Retrieve a payment intent from Stripe
     * 
     * @param string $sessionId
     * @return Session
     */
    public function retrievePayment(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }

    /**
     * Validate the payment session by its status
     * 
     * @param string $sessionId
     * @return bool
     */
    public function paymentIsCompleted(string $sessionId): bool
    {
        $payment = $this->retrievePayment($sessionId);

        return $payment->status === 'complete';
    }
}
