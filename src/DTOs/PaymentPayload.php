<?php

namespace Minic\LunarStripePayment\DTOs;

class PaymentPayload
{
    /**
     * The currency of the payment
     *
     * @var string
     */
    public string $currency;

    /**
     * The amount of the payment
     *
     * @var int
     */
    public int $amount;

    /**
     * The URL to redirect to on success
     *
     * @var string
     */
    public string $successUrl;

    /**
     * The URL to redirect to on cancel
     *
     * @var string
     */
    public string $cancelUrl;

    /**
     * The email of the customer
     *
     * @var string|null
     */
    public ?string $email;

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
        string $currency,
        int $amount,
        string $successUrl,
        string $cancelUrl,
        ?string $email = null
    ) {
        $this->currency = $currency;
        $this->amount = $amount;
        $this->successUrl = $successUrl;
        $this->cancelUrl = $cancelUrl;
        $this->email = $email;
    }
}