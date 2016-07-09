Jose Bridge for the LexikJWTAuthenticationBundle
================================================

[![Build Status](https://travis-ci.org/Spomky-Labs/lexik-jose-bridge.svg?branch=master)](https://travis-ci.org/Spomky-Labs/lexik-jose-bridge)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Spomky-Labs/lexik-jose-bridge/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Spomky-Labs/lexik-jose-bridge/?branch=master)

[![HHVM Status](http://hhvm.h4cc.de/badge/spomky-labs/lexik-jose-bridge.png)](http://hhvm.h4cc.de/package/spomky-labs/lexik-jose-bridge)
[![PHP 7 ready](http://php7ready.timesplinter.ch/Spomky-Labs/lexik-jose-bridge/badge.svg)](https://travis-ci.org/Spomky-Labs/lexik-jose-bridge)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b351c9ca-b49f-4f22-925a-8e0cab6b8cb2/big.png)](https://insight.sensiolabs.com/projects/b351c9ca-b49f-4f22-925a-8e0cab6b8cb2)

[![Latest Stable Version](https://poser.pugx.org/spomky-labs/lexik-jose-bridge/v/stable.png)](https://packagist.org/packages/spomky-labs/lexik-jose-bridge)
[![Total Downloads](https://poser.pugx.org/spomky-labs/lexik-jose-bridge/downloads.png)](https://packagist.org/packages/spomky-labs/lexik-jose-bridge)
[![Latest Unstable Version](https://poser.pugx.org/spomky-labs/lexik-jose-bridge/v/unstable.png)](https://packagist.org/packages/spomky-labs/lexik-jose-bridge)
[![License](https://poser.pugx.org/spomky-labs/lexik-jose-bridge/license.png)](https://packagist.org/packages/spomky-labs/lexik-jose-bridge)

This Symfony Bundle provides a JWT Encoder that uses the [Spomky-Labs/JoseBundle](https://github.com/Spomky-Labs/JoseBundle) as JWT Creator/Loader.

# The Release Process

The release process [is described here](Resources/doc/Release.md).

# Prerequisites

This library needs at least:
* ![PHP 5.6+](https://img.shields.io/badge/PHP-5.6%2B-ff69b4.svg)
* Symfony 2.8+ or Symfony 3.0+

# Continuous Integration

It has been successfully tested using `PHP 5.6`, `PHP 7` and `HHVM` on Symfony `2.8`, `3.0` and `3.1`.

We also track bugs and code quality using [Scrutinizer-CI]() and [Sensio Insight]().

Coding Standards are verified by [StyleCI]().

Code coverage is not performed, but `Behavior driven development` (BDD) is used to test this bundle. 

# Installation

The preferred way to install this bundle is to rely on Composer:

```sh
composer require spomky-labs/lexik-jose-bridge
```

Then, add the bundle and the Spomky-Labs/JoseBundle into your kernel:

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
            new SpomkyLabs\JoseBundle\SpomkyLabsJoseBundle(),
            new SpomkyLabs\LexikJoseBundle\SpomkyLabsLexikJoseBundle(),
        ];

        return $bundles;
    }
}
```

# Configuration

This bundle needs to be configured. Please [see this page](Resources/doc/Configuration.md) to know how to configure it.

# How to use

Have a look at [this page](Resources/doc/Use.md) to know hot to use this bundle.

# Contributing

Requests for new features, bug fixed and all other ideas to make this library useful are welcome. [Please follow these best practices](Resources/doc/Contributing.md).

# Licence

This software is release under [MIT licence](LICENSE).
