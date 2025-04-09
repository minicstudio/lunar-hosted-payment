# Lunar Stripe Payment Integration

## Introduction
This package provides a Stripe payment gateway integration for Lunar PHP. It allows you to handle payments seamlessly using Stripe's API, including creating payment intents and managing transactions.

## Features
- Stripe payment gateway integration
- Support for card payments
- Easy configuration and setup
- Extensible architecture for additional payment methods

## Minimum requirements
- Lunar 1.x
- A Stripe account with secret and public keys


## Installation
### Install the Package
Install the package via Composer:
```bash
composer require minic/lunar-stripe-payment
```

### Publish the Configuration
Publish the configuration file:
```sh
php artisan vendor:publish --tag=lunar.stripe.config
```

### Register the Service Provider (if not auto-discovered)
If your Laravel project does not support package auto-discovery, add the service provider manually in `config/app.php`:
```php
'providers' => [
    Minic\LunarStripePayment\StripeServiceProvider::class,
];
```

### Add your Stripe credentials
Make sure you have the Stripe credentials set in `config/services.php`

```php
'stripe' => [
    'key' => env('STRIPE_SECRET_KEY'),
    'public_key' => env('STRIPE_PUBLIC_KEY'),
],
```

## Usage

### Create a Payment
```php
use Minic\LunarPaymentProcessor\Facades\StripeGateway;

$payment = StripeGateway::createPayment(\Lunar\Models\Cart $cart, $payload = []);
```

This method will create a Stripe checkout session and return the created payment session. You can then redirect the user to the Stripe's payment page:

```php
return redirect($payment['url']);
```

### Authorize a Payment (create order)

Following the lunarphp's pattern of authorizing the payment you can create the actual order by calling the gateway's authorize method. This will return the newly created order id.

```php
$orderId = StripeGateway::authorize($cart, $cart->meta->intent_id);
```

## Contributing
1. Fork the repository
2. Create a new branch (`feature/new-payment-driver`)
3. Commit your changes
4. Push to the branch and submit a Pull Request

## License
This package is open-source and available under the MIT license.

