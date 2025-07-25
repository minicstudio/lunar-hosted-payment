<?php

namespace Minic\LunarHostedPayment;

use Illuminate\Support\Facades\DB;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Models\Order;
use Lunar\Models\Contracts\Transaction;
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

        // create transaction entry
        Order::find($this->data['orderId'])->transactions()->create([
            'type' => 'capture',
            'amount' => $this->cart->total->value,
            'driver' => config('lunar-hosted-payment.payment.default', 'stripe'),
            'reference' => $payment['id'],
            'status' => $payment['status'],
            'success' => false,
            'card_type' => '',

        ]);

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

        DB::transaction(function () use ($orderMeta) {
            $this->order->update([
                'status' => $this->config['authorized'] ?? 'payment-received',
                'meta' => $orderMeta,
                'placed_at' => now(),
            ]);

            // update transaction
            $this->order->transactions()->where('reference', $this->data['sessionId'])->update([
                'status' => 'complete',
                'success' => true,
            ]);
        });

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
