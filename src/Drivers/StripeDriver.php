<?php

namespace Minic\LunarStripePayment\Drivers;

use Minic\LunarStripePayment\Contracts\StripeDriverInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeDriver implements StripeDriverInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret_key'));
    }

    /**
     * Return the Stripe client
     * 
     * @return StripeClient
     */
    public function getClient(): StripeClient
    {
        return new StripeClient([
            'api_key' => config('services.stripe.secret_key'),
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
            'customer_email' => $payload['email'] ?? null,
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
}