<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Jose\Bundle\JoseFramework\Services\JWEBuilder;
use Jose\Bundle\JoseFramework\Services\JWSBuilder;
use Jose\Component\Core\JWKSet;
use SpomkyLabs\LexikJoseBundle\Encoder\LexikJoseEncoder;
use SpomkyLabs\TestBundle\EventListener\JWTListener;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Behat context class.
 */
final class FeatureContext extends MinkContext implements SnippetAcceptingContext
{
    use ResponseContext;
    use LoginContext;
    use RequestContext;

    public function __construct(private ContainerInterface $container)
    {
    }

    public function getJWTListener(): JWTListener
    {
        return $this->container->get('acme_api.event.jwt_created_listener');
    }

    public function getJWSBuilder(): JWSBuilder
    {
        return $this->container->get('jose.jws_builder.lexik_jose');
    }

    public function getJWEBuilder(): JWEBuilder
    {
        return $this->container->get('jose.jwe_builder.lexik_jose');
    }

    public function getEncoder(): LexikJoseEncoder
    {
        return $this->container->get('lexik_jwt_authentication.encoder');
    }

    public function getJWKSetSignature(): JWKSet
    {
        return $this->container->get('jose.key_set.lexik_jose_bridge.signature');
    }

    public function getJWKSetEncryption(): JWKSet
    {
        return $this->container->get('jose.key_set.lexik_jose_bridge.encryption');
    }

    public function getIssuer(): string
    {
        return $this->container->getParameter('lexik_jose_bridge.encoder.issuer');
    }

    public function getAudience(): string
    {
        return $this->container->getParameter('lexik_jose_bridge.encoder.audience');
    }

    public function getSignatureAlgorithm(): string
    {
        return $this->container->getParameter('lexik_jose_bridge.encoder.signature_algorithm');
    }

    public function getKeyEncryptionAlgorithm(): string
    {
        return $this->container->getParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm');
    }

    public function getContentEncryptionAlgorithm(): string
    {
        return $this->container->getParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm');
    }

    public function getEncoderKeyIndex(): string
    {
        return $this->container->getParameter('lexik_jose_bridge.encoder.key_index');
    }

    public function getEncoderEncryptionKeyIndex(): string
    {
        return $this->container->getParameter('lexik_jose_bridge.encoder.encryption.key_index');
    }
}
