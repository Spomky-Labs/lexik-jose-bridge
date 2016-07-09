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

        $container->setAlias('lexik_jose_bridge.encoder.jwt_creator', sprintf('jose.jwt_creator.%s', $this->getAlias()));
        $container->setAlias('lexik_jose_bridge.encoder.jwt_loader', sprintf('jose.jwt_loader.%s', $this->getAlias()));
        $container->setAlias('lexik_jose_bridge.encoder.signature_key', $config['signature_key']);
        $container->setAlias('lexik_jose_bridge.encoder.keyset', sprintf('jose.key_set.%s', $this->getAlias()));
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

        $jose_config = $this->updateJoseBundleConfigurationForSigner($jose_config, $bundle_config);
        $jose_config = $this->updateJoseBundleConfigurationForEncrypter($jose_config, $bundle_config);
        $jose_config = $this->updateJoseBundleConfigurationForJWTCreator($jose_config, $bundle_config);
        $jose_config = $this->updateJoseBundleConfigurationForVerifier($jose_config, $bundle_config);
        $jose_config = $this->updateJoseBundleConfigurationForDecrypter($jose_config, $bundle_config);
        $jose_config = $this->updateJoseBundleConfigurationForChecker($jose_config);
        $jose_config = $this->updateJoseBundleConfigurationForKeySet($jose_config, $bundle_config);
        $jose_config = $this->updateJoseBundleConfigurationForJWTLoader($jose_config, $bundle_config);

        $container->prependExtensionConfig('jose', $jose_config);
    }

    /**
     * @param $jose_config
     * @param $bundle_config
     *
     * @return array
     */
    private function updateJoseBundleConfigurationForSigner($jose_config, $bundle_config)
    {
        $jose_config['signers'] = array_merge(
            $jose_config['signers'],
            [
                $this->getAlias() => [
                    'algorithms' => [$bundle_config['signature_algorithm']],
                ],
            ]
        );

        return $jose_config;
    }

    /**
     * @param $jose_config
     * @param $bundle_config
     *
     * @return array
     */
    private function updateJoseBundleConfigurationForEncrypter($jose_config, $bundle_config)
    {
        if (true === $bundle_config['encryption']['enabled']) {
            $jose_config['encrypters'] = array_merge(
                $jose_config['encrypters'],
                [
                    $this->getAlias() => [
                        'key_encryption_algorithms' => [$bundle_config['encryption']['key_encryption_algorithm']],
                        'content_encryption_algorithms' => [$bundle_config['encryption']['content_encryption_algorithm']],
                    ],
                ]
            );
        }

        return $jose_config;
    }

    /**
     * @param $jose_config
     * @param $bundle_config
     *
     * @return array
     */
    private function updateJoseBundleConfigurationForJWTCreator($jose_config, $bundle_config)
    {
        $jose_config['jwt_creators'] = array_merge(
            $jose_config['jwt_creators'],
            [
                $this->getAlias() => [
                    'signer' => sprintf('jose.signer.%s', $this->getAlias()),
                ],
            ]
        );
        if (true === $bundle_config['encryption']['enabled']) {
            $jose_config['jwt_creators'][$this->getAlias()]['encrypter'] = sprintf('jose.encrypter.%s', $this->getAlias());
        }

        return $jose_config;
    }

    /**
     * @param $jose_config
     * @param $bundle_config
     *
     * @return array
     */
    private function updateJoseBundleConfigurationForVerifier($jose_config, $bundle_config)
    {
        $jose_config['verifiers'] = array_merge(
            $jose_config['verifiers'],
            [
                $this->getAlias() => [
                    'algorithms' => [$bundle_config['signature_algorithm']],
                ],
            ]
        );

        return $jose_config;
    }

    /**
     * @param $jose_config
     * @param $bundle_config
     *
     * @return array
     */
    private function updateJoseBundleConfigurationForDecrypter($jose_config, $bundle_config)
    {
        if (true === $bundle_config['encryption']['enabled']) {
            $jose_config['decrypters'] = array_merge(
                $jose_config['decrypters'],
                [
                    $this->getAlias() => [
                        'key_encryption_algorithms' => [$bundle_config['encryption']['key_encryption_algorithm']],
                        'content_encryption_algorithms' => [$bundle_config['encryption']['content_encryption_algorithm']],
                    ],
                ]
            );
        }

        return $jose_config;
    }

    /**
     * @param $jose_config
     *
     * @return array
     */
    private function updateJoseBundleConfigurationForChecker($jose_config)
    {
        $jose_config['checkers'] = array_merge(
            $jose_config['checkers'],
            [
                $this->getAlias() => [
                    'claims' => ['exp', 'iat', 'nbf'],
                    'headers' => ['crit'],
                ],
            ]
        );

        return $jose_config;
    }

    /**
     * @param $jose_config
     * @param $bundle_config
     *
     * @return array
     */
    private function updateJoseBundleConfigurationForJWTLoader($jose_config, $bundle_config)
    {
        $jose_config['jwt_loaders'] = array_merge(
            $jose_config['jwt_loaders'],
            [
                $this->getAlias() => [
                    'verifier' => sprintf('jose.verifier.%s', $this->getAlias()),
                    'checker' => sprintf('jose.checker.%s', $this->getAlias()),
                ],
            ]
        );
        if (true === $bundle_config['encryption']['enabled']) {
            $jose_config['jwt_loaders'][$this->getAlias()]['decrypter'] = sprintf('jose.decrypter.%s', $this->getAlias());
        }

        return $jose_config;
    }

    /**
     * @param $jose_config
     * @param $bundle_config
     *
     * @return array
     */
    private function updateJoseBundleConfigurationForKeySet($jose_config, $bundle_config)
    {
        $jose_config['key_sets'] = array_merge(
            $jose_config['key_sets'],
            [
                $this->getAlias() => [
                    'keys' => [
                        'id' => [
                            $bundle_config['signature_key'],
                        ],
                    ],
                ],
            ]
        );
        if (true === $bundle_config['encryption']['enabled']) {
            $jose_config['key_sets'][$this->getAlias()]['keys']['id'][] = $bundle_config['encryption']['encryption_key'];
        }

        return $jose_config;
    }
}
