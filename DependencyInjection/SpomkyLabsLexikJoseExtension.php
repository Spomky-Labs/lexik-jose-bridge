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

use Assert\Assertion;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SpomkyLabsLexikJoseExtension extends Extension implements PrependExtensionInterface
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

        $container->setAlias('lexik_jose_bridge.encoder.jwt_creator', 'jose.jwt_creator.lexik');
        $container->setAlias('lexik_jose_bridge.encoder.jwt_loader', 'jose.jwt_loader.lexik');
        $container->setAlias('lexik_jose_bridge.encoder.signature_key', $config['signature_key']);
        $container->setAlias('lexik_jose_bridge.encoder.keyset', 'jose.key_set.lexik');
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

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        Assertion::keyExists($bundles, 'SpomkyLabsJoseBundle', 'The "Spomky-Labs/JoseBundle" must be enabled.');

        $jose_config = current($container->getExtensionConfig('jose'));
        $bundle_config = current($container->getExtensionConfig($this->getAlias()));
        $jose_config['signers'] = array_merge(
            $jose_config['signers'],
            [
                'lexik' => [
                    'algorithms' => [$bundle_config['signature_algorithm']],
                ],
            ]
        );
        $jose_config['encrypters'] = array_merge(
            $jose_config['encrypters'],
            [
                'lexik' => [
                    'key_encryption_algorithms' => [$bundle_config['encryption']['key_encryption_algorithm']],
                    'content_encryption_algorithms' => [$bundle_config['encryption']['content_encryption_algorithm']],
                ],
            ]
        );
        $jose_config['jwt_creators'] = array_merge(
            $jose_config['jwt_creators'],
            [
                'lexik' => [
                    'signer' => 'jose.signer.lexik',
                    'encrypter' => 'jose.encrypter.lexik',
                ],
            ]
        );
        $jose_config['verifiers'] = array_merge(
            $jose_config['verifiers'],
            [
                'lexik' => [
                    'algorithms' => [$bundle_config['signature_algorithm']],
                ],
            ]
        );
        $jose_config['decrypters'] = array_merge(
            $jose_config['decrypters'],
            [
                'lexik' => [
                    'key_encryption_algorithms' => [$bundle_config['encryption']['key_encryption_algorithm']],
                    'content_encryption_algorithms' => [$bundle_config['encryption']['content_encryption_algorithm']],
                ],
            ]
        );
        $jose_config['checkers'] = array_merge(
            $jose_config['checkers'],
            [
                'lexik' => [
                    'claims' => ['exp', 'iat', 'nbf'],
                    'headers' => ['crit'],
                ],
            ]
        );
        $jose_config['jwt_loaders'] = array_merge(
            $jose_config['jwt_loaders'],
            [
                'lexik' => [
                    'verifier' => 'jose.verifier.lexik',
                    'decrypter' => 'jose.decrypter.lexik',
                    'checker' => 'jose.checker.lexik',
                ],
            ]
        );
        $jose_config['key_sets'] = array_merge(
            $jose_config['key_sets'],
            [
                'lexik' => [
                    'keys' => [
                        'id' => [
                            $bundle_config['signature_key'],
                            $bundle_config['encryption']['encryption_key'],
                        ],
                    ],
                ],
            ]
        );

        $container->prependExtensionConfig('jose', $jose_config);
    }
}
