<?php

namespace Minic\LunarPaymentProcessor\Facades;

use Illuminate\Support\Facades\Facade;
use Minic\LunarPaymentProcessor\PaymentGateway as LunarPaymentProcessorPaymentGateway;

class PaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LunarPaymentProcessorPaymentGateway::class;
    }
}
