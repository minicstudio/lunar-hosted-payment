<?php

return [
    'default' => env('PAYMENT_PROVIDER', 'stripe'),
    'providers' => [
        'stripe' => [
            'driver_class' => Minic\LunarHostedPayment\Drivers\StripeDriver::class,
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'public_key' => env('STRIPE_PUBLIC_KEY'),
        ]
    ]
];
