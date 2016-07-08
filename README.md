Jose Bridge for the LexikJWTAuthenticationBundle
================================================

# The Release Process

The release process [is described here](doc/Release.md).

# Prerequisites

This library needs at least:
* ![PHP 5.6+](https://img.shields.io/badge/PHP-5.6%2B-ff69b4.svg)
* Symfony 2.7+ or Symfony 3.0+

# Continuous Integration

It has been successfully tested using `PHP 5.6`, `PHP 7` and `HHVM`.

We also track bugs and code quality using [Scrutinizer-CI]() and [Sensio Insight]().

Coding Standards are verified by [StyleCI]().

Code coverage is not performed, but `Behavior driven development` (BDD) are used to test this bundle. 

# Installation

The preferred way to install this bundle is to rely on Composer:

```sh
composer require spomky-labs/lexik-jose-bridge
```

Then, add the bundle into your kernel:

```php
<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            ...
            new SpomkyLabs\LexikJoseBundle\SpomkyLabsLexikJoseBundle(),
        ];

        return $bundles;
    }
}
```

# Configuration

This bundle needs to be configured. Please [see this page](Resources/doc/Configuration.md) to know how to configure it.

# How to use

Have a look at [this page](Resources/doc/Use.md) to know hot to configure and use this bundle.

# Contributing

Requests for new features, bug fixed and all other ideas to make this library useful are welcome. [Please follow these best practices](Resources/doc/Contributing.md).

# Licence

This software is release under [MIT licence](LICENSE).
