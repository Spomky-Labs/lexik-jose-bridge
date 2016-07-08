<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SpomkyLabsLexikJoseExtension extends Extension
{
    /**
     * @var string
     */
    private $alias;

    /**
     * SpomkyLabsLexikJoseExtension constructor.
     *
     * @param string $alias
     */
    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->alias;
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setAlias('lexik_jose_bridge.encoder.jwt_creator', $config['jwt_creator']);
        $container->setAlias('lexik_jose_bridge.encoder.jwt_loader', $config['jwt_loader']);
        $container->setAlias('lexik_jose_bridge.encoder.signature_key', $config['signature_key']);
        $container->setAlias('lexik_jose_bridge.encoder.keyset', $config['keyset']);
        $container->setParameter('lexik_jose_bridge.encoder.signature_algorithm', $config['signature_algorithm']);

        $container->setParameter('lexik_jose_bridge.encoder.encryption.enabled', $config['encryption']['enabled']);
        if (true === $config['encryption']['enabled']) {
            $container->setAlias('lexik_jose_bridge.encoder.encryption.encryption_key', $config['encryption']['encryption_key']);
            $container->setParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm', $config['encryption']['key_encryption_algorithm']);
            $container->setParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm', $config['encryption']['content_encryption_algorithm']);
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
