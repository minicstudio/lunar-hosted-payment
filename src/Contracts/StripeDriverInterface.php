<?php

namespace Minic\LunarStripePayment\Contracts;

use Minic\LunarStripePayment\DTOs\PaymentPayload;

interface StripeDriverInterface
{
    /**
     * Return the Stripe client
     *
     * @return \Stripe\StripeClient
     */
    public function getClient();

    /**
     * Create a payment session
     *
     * @param PaymentPayload $payload
     * @return \Stripe\Checkout\Session
     */
    public function createPayment(PaymentPayload $payload);

    /**
     * Retrieve a payment session
     *
     * @param string $sessionId
     * @return \Stripe\Checkout\Session
     */
    public function retrievePayment(string $sessionId);
}
