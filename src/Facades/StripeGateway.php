<?php

namespace Minic\LunarStripePayment\Facades;

use Illuminate\Support\Facades\Facade;
use Minic\LunarStripePayment\StripeGateway as LunarPaymentStripeGateway;

class StripeGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LunarPaymentStripeGateway::class;
    }
}
