Jose Bridge for the LexikJWTAuthenticationBundle
================================================

[![Build Status](https://travis-ci.org/Spomky-Labs/lexik-jose-bridge.svg?branch=v3.0)](https://travis-ci.org/Spomky-Labs/lexik-jose-bridge)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Spomky-Labs/lexik-jose-bridge/badges/quality-score.png?b=v3.0)](https://scrutinizer-ci.com/g/Spomky-Labs/lexik-jose-bridge/?branch=v3.0)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b351c9ca-b49f-4f22-925a-8e0cab6b8cb2/big.png)](https://insight.sensiolabs.com/projects/b351c9ca-b49f-4f22-925a-8e0cab6b8cb2)

[![Latest Stable Version](https://poser.pugx.org/spomky-labs/lexik-jose-bridge/v/stable.png)](https://packagist.org/packages/spomky-labs/lexik-jose-bridge)
[![Total Downloads](https://poser.pugx.org/spomky-labs/lexik-jose-bridge/downloads.png)](https://packagist.org/packages/spomky-labs/lexik-jose-bridge)
[![Latest Unstable Version](https://poser.pugx.org/spomky-labs/lexik-jose-bridge/v/unstable.png)](https://packagist.org/packages/spomky-labs/lexik-jose-bridge)
[![License](https://poser.pugx.org/spomky-labs/lexik-jose-bridge/license.png)](https://packagist.org/packages/spomky-labs/lexik-jose-bridge)

This Symfony Bundle provides a JWT Encoder for the [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle) that uses the [web-token/jwt-framework](https://github.com/web-token/jwt-framework) as JWT Creator/Loader.

# The Release Process

The release process [is described here](Resources/doc/Release.md).

# Prerequisites

This library needs at least:
* ![PHP 7.2+](https://img.shields.io/badge/PHP-7.2%2B-ff69b4.svg)
* Symfony 4.3+.

# Continuous Integration

It has been successfully tested using `PHP 7.2` and `PHP 7.3`.

We also track bugs and code quality using [Scrutinizer-CI](https://scrutinizer-ci.com/g/Spomky-Labs/lexik-jose-bridge) and [Sensio Insight](https://insight.sensiolabs.com/projects/b351c9ca-b49f-4f22-925a-8e0cab6b8cb2).

Coding Standards are verified by [StyleCI](https://styleci.io/repos/61054893).

Code coverage is not performed, but `Behavior driven development` (BDD) is used to test this bundle. 

# Installation


## Symfony Flex

The preferred way to install this bundle is to rely on Symfony Flex:

```sh
composer req "spomky-labs/lexik-jose-bridge:^3.0"
```

## Manual Installation

If you do not use Symfony Flex, then use Composer and install the bundle manually.

```sh
composer require spomky-labs/lexik-jose-bridge
```

Then, add this bundle and the `web-token/jwt-framework` bundles into your kernel:

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
            new Jose\Bundle\JoseFramework\JoseFrameworkBundle(),
            new SpomkyLabs\LexikJoseBundle\SpomkyLabsLexikJoseBundle(),
        ];

        return $bundles;
    }
}
```

# Signature/Encryption Algorithms

This bundle only installs the RSA based signature algorithms (`RS256`, `RS384` and `RS512`).
If you need other signature algorithms (e.g EC based, HMAC) or if you want to use the encryption feature,
you must install the corresponding packages:

* Signature Algorithms
    * All: `composer require web-token/signature-pack`
    * HMAC: `composer require web-token/jwt-signature-algorithm-hmac`
    * ECDSA: `composer require web-token/jwt-signature-algorithm-ecdsa`
    * EdDSA: `composer require web-token/jwt-signature-algorithm-eddsa`
    * None: `composer require web-token/jwt-signature-algorithm-none` (not recommended)
    * Experimental: `composer require web-token/jwt-signature-algorithm-experimental` (not recommended)
* Encryption Algorithms
    * All: `composer require web-token/encryption-pack`
    * Key Encryption:
        * ECDH-ES: `composer require web-token/jwt-encryption-algorithm-ecdh-es`
        * AES Key Wrapping: `composer require web-token/jwt-encryption-algorithm-aeskw`
        * RSA: `composer require web-token/jwt-encryption-algorithm-rsa`
        * AES GCM Key Wrapping: `composer require web-token/jwt-encryption-algorithm-aesgcmkw`
        * Direct: `composer require web-token/jwt-encryption-algorithm-dir` (not recommended)
        * PBES 2: `composer require web-token/jwt-encryption-algorithm-pbes2` (not recommended)
    * Content Encryption:
        * AES GCM: `composer require web-token/jwt-encryption-algorithm-aesgcm`
        * AES CBC: `composer require web-token/jwt-encryption-algorithm-aescbc`
    * Experimental: `composer require web-token/jwt-encryption-algorithm-experimental` (not recommended)

# Configuration

This bundle needs to be configured. Please [see this page](Resources/doc/Configuration.md) to know how to configure it.

# How to use

There is nothing to do. Just use your application as usual.

# Support

I bring solutions to your problems and answer your questions.

If you really love that project and the work I have done or if you want I prioritize your issues, then you can help me out for a couple of :beers: or more!

[![Become a Patreon](https://c5.patreon.com/external/logo/become_a_patron_button.png)](https://www.patreon.com/FlorentMorselli)

# Contributing

Requests for new features, bug fixes and all other ideas to make this library useful are welcome. [Please follow these best practices](Resources/doc/Contributing.md).

# Licence

This software is release under [MIT licence](LICENSE).
