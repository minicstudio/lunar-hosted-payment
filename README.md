# Lunar Hosted Payment Integration

## Introduction
This package provides hosted payment gateway integration for Lunar PHP. It allows you to handle payments seamlessly using payment APIs like Stripe, including creating payment intents and managing transactions.

## Features
- Stripe payment gateway integration
- Support for card payments
- Easy configuration and setup
- Extensible architecture for additional payment methods

## Minimum requirements
- Lunar 1.x
- A Stripe account with secret and public keys

## Setting Up Your Stripe Account
To use this package, you need to have a Stripe account properly configured. Follow these steps to set up your Stripe account:

1. **Create a Stripe Account**  
   If you don't already have a Stripe account, sign up at [https://stripe.com](https://stripe.com).

2. **Obtain API Keys**  
   - Log in to your Stripe Dashboard.
   - Navigate to the **Developers** section and select **API Keys**.
   - Copy your **Publishable Key** and **Secret Key**.

3. **Set Up Your Environment Variables**  
   Add the following keys to your `.env` file:
   ```env
   STRIPE_PUBLIC_KEY=your-publishable-key
   STRIPE_SECRET_KEY=your-secret-key


## Installation
### Install the Package
Install the package via Composer:
```bash
composer require minic/lunar-hosted-payment
```

### Publish the Configuration
Publish the configuration file:

```bash
php artisan vendor:publish --tag=hosted-payments-config
```

### Register the Service Provider (if not auto-discovered)
If your Laravel project does not support package auto-discovery, add the service provider manually in `config/app.php`:
```php
'providers' => [
    Minic\LunarHostedPayment\HostedPaymentServiceProvider::class,
];
```

### Add your payment provider credentials
Make sure you have the provider credentials set in `config/lunar-hosted-payment/payment.php`. E.g.:

```php
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
```

### Lunar payments configuration file
Your lunar payments.php config file should look like this:
```php
<?php

return [

    'default' => env('PAYMENTS_TYPE', 'offline'),

    'types' => [
        'offline' => [
            'driver' => 'offline',
            'authorized' => 'payment-offline',
        ],
        'hosted-payment' => [
            'driver' => 'hosted-payment',
            'authorized' => 'payment-received',
        ],
    ],

];
```

## Usage

### Create a Payment
```php
$response = Lunar\Facades\Payments::driver('hosted-payment')->cart($cart)->withData([
    'successUrl' => $successUrl,
    'cancelUrl' => $cancelUrl,
    'paymentOption' => 'hosted-payment',
    'orderId' => $order->id,
])->initiatePayment();
```

This method will create a payment session and return the created payment session. You can then redirect the user to the provider's payment page:

```php
return redirect($response['redirectUrl']);
```

**Note:** The user will be redirected to the provider's payment page for completing the transaction.

### Authorize a Payment (create order)

Following the lunarphp's pattern of authorizing the payment you can create the actual order by calling the driver's authorize method. This will return the newly created order id.

```php
Lunar\Facades\Payments::driver('hosted-payment')->cart($cart)->withData(
    ['sessionId' => $transaction->reference]
)->authorize();
```

## Contributing
1. Fork the repository
2. Create a new branch (`feature/new-payment-driver`)
3. Commit your changes
4. Push to the branch and submit a Pull Request

## License
This package is open-source and available under the MIT license.

