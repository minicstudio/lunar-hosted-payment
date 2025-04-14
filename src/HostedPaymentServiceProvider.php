<?php

namespace Minic\LunarHostedPayment;

use Illuminate\Support\ServiceProvider;
use Lunar\Facades\Payments;
use Minic\LunarHostedPayment\Exceptions\InvalidHostedPaymentProviderException;

class HostedPaymentServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Payments::extend('hosted-payment', function ($app) {
            return $app->make(HostedPaymentType::class);
        });

        $this->app->singleton(HostedPaymentGateway::class, function ($app) {
            $provider = config('lunar-hosted-payment.payment.default', 'stripe');

            // Get the corresponding driver class
            $providers = config('lunar-hosted-payment.payment.providers', []);
            $providerConfig = $providers[$provider] ?? null;

            if (!$providerConfig || !isset($providerConfig['driver_class']) || !class_exists($providerConfig['driver_class'])) {
                throw new InvalidHostedPaymentProviderException("Invalid or unsupported payment provider: {$provider}");
            }

            $driverClass = $providerConfig['driver_class'];

            return new HostedPaymentGateway(new $driverClass());
        });

        $this->publishes([
            __DIR__.'/../config/payment.php' => config_path('lunar-hosted-payment/payment.php'),
        ], 'hosted-payments-config');
    }
}
