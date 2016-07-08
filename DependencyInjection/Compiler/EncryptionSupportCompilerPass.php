<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class EncryptionSupportCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (
            false === $container->hasDefinition('lexik_jose_bridge.encoder') ||
            false === $container->hasParameter('lexik_jose_bridge.encoder.encryption.enabled') ||
            false === $container->getParameter('lexik_jose_bridge.encoder.encryption.enabled')
        ) {
            return;
        }

        $definition = $container->getDefinition('lexik_jose_bridge.encoder');

        $definition->addMethodCall('enableEncryptionSupport', [
            new Reference($container->getAlias('lexik_jose_bridge.encoder.encryption.encryption_key')),
            $container->getParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm'),
            $container->getParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm'),
        ]);
    }
}
