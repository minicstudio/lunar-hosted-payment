# Lunar Payment Processor

## Introduction
This package provides an extensible payment gateway integration for Lunar PHP, allowing seamless support for multiple payment providers. The package follows a driver-based architecture, making it easy to add and configure different payment processors.

## Features
- Support for multiple payment providers (starting with Stripe)
- Extensible and modular architecture
- Integration with Lunar PHP checkout process
- Standardized payment driver interface

## Installation
### Step 1: Require the Package
Install the package via Composer:
```sh
composer require minic/lunar-payment-processor
```

### Step 2: Publish the Configuration
Publish the configuration file to customize settings:
```sh
php artisan vendor:publish --tag=lunar-payment-processor-config
```

### Step 3: Register the Service Provider (if not auto-discovered)
If your Laravel project does not support package auto-discovery, add the service provider manually in `config/app.php`:
```php
'providers' => [
    Minic\LunarPaymentProcessor\PaymentServiceProvider::class,
];
```

## Configuration
Modify `config/payment.php` to define the default payment provider:
```php
return [
    'default' => env('PAYMENT_PROVIDER', 'stripe'),

    'providers' => [
        'stripe' => [
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
        ],
    ],
];
```

Set the required environment variables in your `.env` file:
```sh
PAYMENT_PROVIDER=stripe
STRIPE_KEY=your-stripe-public-key
STRIPE_SECRET=your-stripe-secret-key
```

## Usage
### Getting the Payment Gateway Instance
```php
use Minic\LunarPaymentProcessor\Facades\PaymentGateway;

$gateway = PaymentGateway::getInstance();
```

### Creating a Payment Intent
```php
$cart = Cart::find(1);
$paymentIntent = $gateway->createPayment($cart, [
    'description' => 'Order Payment',
]);
```

### Retrieving a Payment
```php
$paymentDetails = $gateway->getPayment($paymentIntentId);
```

### Confirming a Payment
```php
$gateway->confirmPayment($paymentIntentId);
```

### Cancelling a Payment
```php
$gateway->cancelPayment($paymentIntentId, 'customer_request');
```

## Extending with New Providers
To add a new payment provider, create a new driver class implementing `PaymentDriverInterface`:
```php
namespace Minic\LunarPaymentProcessor\Drivers;

use Minic\LunarPaymentProcessor\Contracts\PaymentDriverInterface;
use Lunar\Models\Cart;

class NewPaymentDriver implements PaymentDriverInterface
{
    public function createPayment(string $paymentId, array $options = []): string
    {
        // Implementation for new provider
    }
}
```
Then register it in `config/payment.php`.

## Contributing
1. Fork the repository
2. Create a new branch (`feature/new-payment-driver`)
3. Commit your changes
4. Push to the branch and submit a Pull Request

## License
This package is open-source and available under the MIT license.

