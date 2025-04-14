<?php

namespace Minic\LunarHostedPayment\DTOs;

class PaymentPayload
{
    /**
     * Create a new instance of the PaymentPayload
     *
     * @param string $currency
     * @param int $amount
     * @param string $successUrl
     * @param string $cancelUrl
     * @param string|null $email
     */
    public function __construct(
        public string $currency,
        public int $amount,
        public string $successUrl,
        public string $cancelUrl,
        public ?string $email = null
    ) {

    }
}
