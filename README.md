# Lunar Stripe Payment Integration

## Introduction
This package provides a Stripe payment gateway integration for Lunar PHP. It allows you to handle payments seamlessly using Stripe's API, including creating payment intents and managing transactions.

This package provides a Stripe payment gateway integration for Lunar PHP. It allows you to handle payments using Stripe's API by creating payment sessions with customizable payloads, and retrieveing existing ones.

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

**Note:** The user will be redirected to the Stripe payment page for completing the transaction.

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

