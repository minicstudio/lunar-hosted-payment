<?php

namespace Minic\LunarPaymentProcessor;

use Minic\LunarPaymentProcessor\Contracts\PaymentDriverInterface;

class PaymentGateway
{
    protected $driver;

    public function __construct(PaymentDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function charge(array $paymentDetails)
    {
        return $this->driver->charge($paymentDetails);
    }

    public function refund(string $transactionId, float $amount)
    {
        return $this->driver->refund($transactionId, $amount);
    }
}
