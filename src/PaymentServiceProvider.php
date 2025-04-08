<?php

namespace Minic\LunarPaymentProcessor;

use Illuminate\Support\ServiceProvider;
use Lunar\Facades\Payments;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        Payments::extend('card', function ($app) {
            return $app->make(CardPayment::class);
        });

        $this->app->singleton(PaymentGateway::class, function ($app) {
            // Get the configured payment provider
            $provider = config('lunar-payment-processor.payment.default', 'stripe');

            // Get the corresponding driver class
            $providers = config('lunar-payment-processor.payment.providers', []);
            $providerConfig = $providers[$provider] ?? null;

            if (!$providerConfig || !isset($providerConfig['driver_class']) || !class_exists($providerConfig['driver_class'])) {
                throw new \Exception("Invalid or unsupported payment provider: {$provider}");
            }

            $driverClass = $providerConfig['driver_class'];

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
