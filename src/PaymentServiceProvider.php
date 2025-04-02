<?php

namespace Minic\LunarPaymentProcessor;

use Illuminate\Support\ServiceProvider;
use Minic\LunarPaymentProcessor\Drivers\StripeDriver;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge the default config to allow users to override it
        $this->mergeConfigFrom(__DIR__.'/../config/payment.php', 'payment');

        $this->app->singleton(PaymentGateway::class, function ($app) {
            // Get the configured payment provider
            $provider = config('payment.default', 'stripe');

            // Get the corresponding driver class
            $drivers = config('payment.drivers', []);
            $driverClass = $drivers[$provider] ?? null;

            if (!$driverClass || !class_exists($driverClass)) {
                throw new \Exception("Invalid or unsupported payment provider: {$provider}");
            }

            return new PaymentGateway(new $driverClass());
        });
    }

    public function boot()
    {
        // Publish the config file
        $this->publishes([
            __DIR__.'/../config/payment.php' => config_path('payment.php'),
        ], 'config');
    }
}
