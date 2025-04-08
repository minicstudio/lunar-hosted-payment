<?php

namespace Minic\LunarPaymentProcessor\Contracts;

use Illuminate\Support\Collection;

interface PaymentDriverInterface
{
    public function getClient();
    public function createPayment(array $payload = []);
    public function updatePayment(string $intentId, array $payload): void;
    public function cancelPayment(string $paymentId, string $reason = ''): void;
    public function fetchPayment(string $intentId): ?Collection;
    public function getTransactions(string $paymentId): Collection;
}
