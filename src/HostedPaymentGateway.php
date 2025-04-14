<?php

namespace Minic\LunarHostedPayment;

use Exception;
use Minic\LunarHostedPayment\Contracts\HostedPaymentDriverInterface;
use Minic\LunarHostedPayment\DTOs\PaymentPayload;
use Minic\LunarHostedPayment\Exceptions\HostedPaymentException;

class HostedPaymentGateway
{
    /**
     * Create a new instance of the HostedPaymentGateway
     *
     * @param HostedPaymentDriverInterface $driver
     */
    public function __construct(protected HostedPaymentDriverInterface $driver)
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
            throw new HostedPaymentException('Failed to initiate payment: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $paymentIntent->toArray();
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
            throw new HostedPaymentException('Failed to retrieve payment: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $payment->toArray();
    }

    /**
     * Validate the payment based on its status
     *
     * @param string $sessionId
     * @return bool
     */
    public function paymentIsCompleted(string $sessionId): bool
    {
        try {
            $valid = $this->driver->paymentIsCompleted($sessionId);
        } catch (Exception $e) {
            throw new HostedPaymentException('Failed to validate payment: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $valid;
    }
}
