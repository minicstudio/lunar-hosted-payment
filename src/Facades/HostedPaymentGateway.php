<?php

namespace Minic\LunarHostedPayment\Facades;

use Illuminate\Support\Facades\Facade;
use Minic\LunarHostedPayment\HostedPaymentGateway as LunarHostedPaymentGateway;

class HostedPaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LunarHostedPaymentGateway::class;
    }
}
