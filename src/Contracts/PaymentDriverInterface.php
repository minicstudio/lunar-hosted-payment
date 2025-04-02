<?php

namespace Minic\LunarPaymentProcessor\Contracts;

use Illuminate\Support\Collection;
use Lunar\Models\Cart;

interface PaymentDriverInterface
{
    public function getClient();
    public function createPayment(Cart $cart, array $options = []);
    public function cancelPayment(Cart $cart, string $reason = ''): void;
    public function fetchPayment(string $paymentId): ?Collection;
    public function getTransactions(string $paymentId): Collection;
}
