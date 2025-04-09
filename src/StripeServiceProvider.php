<?php

namespace Minic\LunarStripePayment;

use Illuminate\Support\ServiceProvider;
use Lunar\Facades\Payments;
use Minic\LunarStripePayment\Drivers\StripeDriver;

class StripeServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Payments::extend('card', function ($app) {
            return $app->make(StripePaymentType::class);
        });

        $this->app->singleton(StripeGateway::class, function ($app) {
            return new StripeGateway(new StripeDriver);
        });

        $this->mergeConfigFrom(__DIR__.'/../config/stripe.php', 'lunar.stripe');

        $this->publishes([
            __DIR__.'/../config/stripe.php' => config_path('lunar/stripe.php'),
        ], 'lunar.stripe.config');
    }
}
