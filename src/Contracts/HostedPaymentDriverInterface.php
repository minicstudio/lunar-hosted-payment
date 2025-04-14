<?php

namespace Minic\LunarHostedPayment\Contracts;

use Minic\LunarHostedPayment\DTOs\PaymentPayload;

interface HostedPaymentDriverInterface
{
    /**
     * Return the payment provider client
     *
     * @return @mixed
     */
    public function getClient();

    /**
     * Create a payment session
     *
     * @param PaymentPayload $payload
     * @return @mixed
     */
    public function createPayment(PaymentPayload $payload);

    /**
     * Retrieve a payment session
     *
     * @param string $sessionId
     * @return @mixed
     */
    public function retrievePayment(string $sessionId);

    /**
     * Validate the payment session by its status
     *
     * @param string $sessionId
     * @return bool
     */
    public function paymentIsCompleted(string $sessionId): bool;
}
