# Symfony framework bundle for Omnipay library
The following bundle
is based on [Omnipay package](https://github.com/thephpleague/omnipay) and its components.<br>
It supports Symfony 4.4, 5.x, 6.x and PHP 7.4+, 8.0.x, 8.1.x

[![Unit Tests](https://github.com/janwebdev/symfony-omnipay-bundle/actions/workflows/run-tests.yml/badge.svg)](https://github.com/janwebdev/symfony-omnipay-bundle/actions/workflows/run-tests.yml)
[![Latest Stable Version](https://poser.pugx.org/janwebdev/symfony-omnipay-bundle/v)](//packagist.org/packages/janwebdev/symfony-omnipay-bundle) [![Total Downloads](https://poser.pugx.org/janwebdev/symfony-omnipay-bundle/downloads)](//packagist.org/packages/janwebdev/symfony-omnipay-bundle) [![Latest Unstable Version](https://poser.pugx.org/janwebdev/omnipay-cardinity/v/unstable)](//packagist.org/packages/janwebdev/symfony-omnipay-bundle) [![License](https://poser.pugx.org/janwebdev/omnipay-cardinity/license)](//packagist.org/packages/janwebdev/symfony-omnipay-bundle)

##Installation
Via Composer

``` bash
$ composer require janwebdev/symfony-omnipay-bundle
```
Make sure it is enabled in `./config/bundles.php` file:

```php
// ...
Janwebdev\OmnipayBundle\OmnipayBundle::class => ['all' => true],
```
## Usage

This bundle provides a new service called `OmnipayManager`.  It contains a single method `get()`, which returns a fully-configured gateway. Use dependency injection to use service:

``` php
<?php

// ...

public function makePayment(OmnipayManager $omnipay)
{
    $stripe = $omnipay->get('Stripe');
    $paypal = $omnipay->get('PayPal_Express');
// ...
```
`get()` receives payment integration name as string, as it is called in [Omnipay package](https://github.com/thephpleague/omnipay)
You can then use these gateways like usual.

**Note:** Gateways are "cached" - calling `get('Some_Gateway')` multiple times will always return the same object.

## Configuration

Create config file `./config/packages/omnipay.yaml` or copy-paste from [example](config.example.yaml).<br>
Gateways can be configured in this file, i.e.:

``` yml
omnipay:
    gateways:
        # Your config goes here
```

For example, to configure the [Stripe](https://github.com/thephpleague/omnipay-stripe) and [PayPal Express](https://github.com/thephpleague/omnipay-paypal) gateways:

``` yml
omnipay:
    gateways:
        Stripe:
            apiKey: sk_test_BQokikJOvBiI2HlWgH4olfQ2

        PayPal_Express:
            username:     test-facilitator_api1.example.com
            password:     3MPI3VB4NVQ3XSVF
            signature:    6fB0XmM3ODhbVdfev2hUXL2x7QWxXlb1dERTKhtWaABmpiCK1wtfcWd.
            testMode:     false
            solutionType: Sole
            landingPage:  Login
```
**NOTE:** Consider using parameters and/or ENV variables instead of storing credentials directly in your `omnipay.yaml` file like that.

The method names should be whatever you'd typically pass into `Omnipay::create()`.<br>
The configuration settings vary per gateway - see
[Configuring Gateways](http://omnipay.thephpleague.com/gateways/configuring/) in the Omnipay documentation for more details.

## Registering Custom Gateway

Custom gateways can be registered via the container by tagging them with `omnipay.gateway`:

```yml
# services.yaml
services:
    my.custom.gateway:
        class: Path\To\CustomGateway
        tags:
            - { name: omnipay.gateway, alias: CustomGateway }

# omnipay.yaml
omnipay:
    methods:
        # Reference the gateway alias here
        CustomGateway:
            apiKey: pa$$w0rd
```

You can then obtain the fully-configured gateway by its alias:

```php
// ...
private function getCustomGateway(OmnipayManager $omnipay): GatewayInteface
{
    return $omnipay->get('CustomGateway');
}
// ...
```

## Additional configuration and customization

### Default gateway

Add default gateway key to your config:
```yml
# omnipay.yaml
omnipay:
    gateways:
        MyGateway1:
            apiKey: pa$$w0rd
        MyGateway2:
            apiKey: pa$$w0rd

    default: MyGateway1
```

You can now get default gateway instance:
```php
$omnipay->getDefaultGateway();
```

### Disabling gateways

If need to disable a gateway but want to keep all the configuration add `disabled` key to the config:
```yml
# omnipay.yaml
omnipay:
    gateways:
        MyGateway1:
            apiKey: pa$$w0rd
        MyGateway2:
            apiKey: pa$$w0rd

    disabled: [ MyGateway1 ]
```

`MyGateway1` gateway will be skipped during gateway registration now.

### Customizing Omnipay service

If you need specific gateway selection mechanism or need to get multiple gateways at once consider to extend
default bundle's Omnipay service. Create your custom `App\Omnipay\MyService` class, extend it from base class and add custom getters.<br> For
example, you might want to get all gateways which implement some interface.

```php
<?php

// App/Omnipay/MyService.php

namespace App\Omnipay;

use App\Payment\Processing\Gateway\CardSavingInterface;
use Janwebdev\OmnipayBundle\Manager\OmnipayManager as BaseOmnipay;
use Omnipay\Common\GatewayInterface;

class MyService extends BaseOmnipay
{
    /**
     * @return CardSavingInterface[]
     */
    public function getCardSavingGateways(): array
    {
        return array_filter($this->registeredGateways, function (GatewayInterface $gateway) {
            return $gateway instanceof CardSavingInterface;
        });
    }
}
```

```yml
#services.yaml
parameters:
    omnipay.class: App\Omnipay\MyService
```
Now you should be able to get "card-saving"-aware gateways in your application:
```php
// ...
foreach ($omnipay->getCardSavingGateways() as $gateway) {
    $gateway->saveCreditCard($creditCard); // assuming saveCreditCard is a part of CardSavingInterface interface
}
// ...
```

### Initialize gateways on registration

By default gateway is initialized only when you call `get()` method. If you use custom getters (like
`getCardSavingGateways` from example above) with `$this->registeredGateways` inside you might want to initialize them
automatically during registration. Simply add appropriate config key:
```yml
# omnipay.yaml
omnipay:
    gateways:
        MyGateway1:
            apiKey: @pa$$w0rd#

    init_on_boot: true
```
## Unit tests

``` bash
$ phpunit
```
## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.