<?php

namespace Minic\LunarStripePayment\Contracts;

interface StripeDriverInterface
{
    public function getClient();
    public function createPayment(array $payload = []);
    public function retrievePayment(string $sessionId);
}
