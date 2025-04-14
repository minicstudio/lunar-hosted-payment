<?php

namespace Minic\LunarHostedPayment;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;
use Minic\LunarHostedPayment\DTOs\PaymentPayload;
use Minic\LunarHostedPayment\Facades\HostedPaymentGateway;

class HostedPaymentType extends AbstractPayment
{
    /** 
     * Initiate payment
     * 
     * @return Array
     */
    public function initiatePayment(): Array
    {
        $payload = new PaymentPayload(
            currency: $this->cart->currency->code,
            amount: $this->cart->total->value,
            successUrl: $this->data['successUrl'],
            cancelUrl: $this->data['cancelUrl'],
            email: $this->cart->billingAddress->contact_email,
        );

        $payment = HostedPaymentGateway::createPayment($payload);

        return [
            'redirectUrl' => $payment['url'],
            'paymentId' => $payment['id'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function authorize(): ?PaymentAuthorize
    {
        $paymentIsCompleted = HostedPaymentGateway::paymentIsCompleted($this->data['sessionId']);

        if (!$paymentIsCompleted) {
            return new PaymentAuthorize(
                success: false,
                orderId: null,
                paymentType: 'hosted-payment',
            );
        }

        $this->order = $this->order ?? $this->cart->draftOrder()->first();

        if (! $this->order) {
            $this->order = $this->cart->createOrder();
        }

        $orderMeta = array_merge(
            (array) $this->order->meta,
            $this->data['meta'] ?? []
        );

        $this->cart->paymentIntents()->create([
            'intent_id' => $this->data['sessionId'],
            'status' => $this->config['authorized'] ?? 'payment-received',
            'order_id' => $this->order->id,
        ]);

        $this->order->update([
            'status' => $this->config['authorized'] ?? 'payment-received',
            'meta' => $orderMeta,
            'placed_at' => now(),
        ]);

        $response = new PaymentAuthorize(
            success: true,
            orderId: $this->order->id,
            paymentType: 'hosted-payment',
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
