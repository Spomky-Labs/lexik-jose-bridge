<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\DependencyInjection\Compiler;

use SpomkyLabs\LexikJoseBundle\Encoder\LexikJoseEncoder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class EncryptionSupportCompilerPass.
 */
final class EncryptionSupportCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition(LexikJoseEncoder::class) || false === $container->getParameter('lexik_jose_bridge.encoder.encryption.enabled')) {
            return;
        }

        $definition = $container->getDefinition(LexikJoseEncoder::class);

        $definition->addMethodCall('enableEncryptionSupport', [
            new Reference('jose.jwe_builder.lexik_jose'),
            new Reference('jose.jwe_decrypter.lexik_jose'),
            new Reference('jose.header_checker.lexik_jose_encryption'),
            new Reference('jose.key_set.lexik_jose_bridge.encryption'),
            $container->getParameter('lexik_jose_bridge.encoder.encryption.key_index'),
            $container->getParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm'),
            $container->getParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm'),
        ]);
    }
}
