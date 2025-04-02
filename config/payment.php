<?php

return [
    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY', 'your_stripe_secret_key'),
        'public_key' => env('STRIPE_PUBLIC_KEY', 'your_stripe_public_key'),
    ]
];