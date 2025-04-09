<?php

namespace Minic\LunarStripePayment;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Stripe\PaymentIntent;

class StripePaymentType extends AbstractPayment
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): ?PaymentAuthorize
    {
        if (! $this->order) {
            if (! $this->order = $this->cart->draftOrder()->first()) {
                $this->order = $this->cart->createOrder();
            }
        }
        $orderMeta = array_merge(
            (array) $this->order->meta,
            $this->data['meta'] ?? []
        );

        $this->order->update([
            'status' => PaymentIntent::STATUS_SUCCEEDED,
            'meta' => $orderMeta,
            'placed_at' => now(),
        ]);

        $response = new PaymentAuthorize(
            success: true,
            orderId: $this->order->id,
            paymentType: 'card',
        );

        PaymentAttemptEvent::dispatch($response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        return new PaymentRefund(true);
    }

    /**
     * {@inheritDoc}
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(true);
    }
}
