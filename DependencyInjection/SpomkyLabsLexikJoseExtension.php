<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\DependencyInjection;

use SpomkyLabs\JoseBundle\Helper\ConfigurationHelper;
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

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setAlias('lexik_jose_bridge.encoder.jwt_creator', sprintf('jose.jwt_creator.%s', $this->getAlias()));
        $container->setAlias('lexik_jose_bridge.encoder.jwt_loader', sprintf('jose.jwt_loader.%s', $this->getAlias()));
        $container->setParameter('lexik_jose_bridge.encoder.key_storage_folder', $config['key_storage_folder']);
        $container->setParameter('lexik_jose_bridge.encoder.signature_key_configuration', $config['signature_key_configuration']);
        $container->setParameter('lexik_jose_bridge.encoder.issuer', $config['server_name']);
        $container->setParameter('lexik_jose_bridge.encoder.ttl', $config['ttl']);
        $container->setParameter('lexik_jose_bridge.encoder.signature_algorithm', $config['signature_algorithm']);

        $container->setParameter('lexik_jose_bridge.encoder.encryption.enabled', $config['encryption']['enabled']);
        if (true === $config['encryption']['enabled']) {
            $container->setParameter('lexik_jose_bridge.encoder.encryption.encryption_key_configuration', $config['encryption']['encryption_key_configuration']);
            $container->setParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm', $config['encryption']['key_encryption_algorithm']);
            $container->setParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm', $config['encryption']['content_encryption_algorithm']);
        }

        $this->loadServices($container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function loadServices(ContainerBuilder $container)
    {
        $files = ['services', 'warmup'];
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach ($files as $file) {
            $filename = sprintf('%s.xml', $file);
            $loader->load($filename);
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundle_config = current($container->getExtensionConfig($this->getAlias()));

        $this->addJWKSets($container, $bundle_config);
        ConfigurationHelper::addSigner($container, $this->getAlias(), [$bundle_config['signature_algorithm']], false, false);
        ConfigurationHelper::addVerifier($container, $this->getAlias(), [$bundle_config['signature_algorithm']], false);

        if (true === $bundle_config['encryption']['enabled']) {
            ConfigurationHelper::addEncrypter($container, $this->getAlias(), [$bundle_config['encryption']['key_encryption_algorithm']], [$bundle_config['encryption']['content_encryption_algorithm']], ['DEF'], false, false);
            ConfigurationHelper::addDecrypter($container, $this->getAlias(), [$bundle_config['encryption']['key_encryption_algorithm']], [$bundle_config['encryption']['content_encryption_algorithm']], ['DEF'], false);
            $encrypter = sprintf('jose.encrypter.%s', $this->getAlias());
            $decrypter = sprintf('jose.decrypter.%s', $this->getAlias());
        } else {
            $encrypter = null;
            $decrypter = null;
        }
        ConfigurationHelper::addChecker($container, $this->getAlias(), ['crit'], ['exp', 'iat', 'nbf', 'lexik_iss', 'lexik_aud'], false);
        ConfigurationHelper::addJWTCreator($container, $this->getAlias(), sprintf('jose.signer.%s', $this->getAlias()), $encrypter);
        ConfigurationHelper::addJWTLoader($container, $this->getAlias(), sprintf('jose.verifier.%s', $this->getAlias()), sprintf('jose.checker.%s', $this->getAlias()), $decrypter, false);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $bundle_config
     */
    private function addJWKSets(ContainerBuilder $container, array $bundle_config)
    {
        $signature_key_configuration = $bundle_config['signature_key_configuration'];
        $signature_key_configuration['use'] = 'sig';
        $signature_key_configuration['alg'] = $bundle_config['signature_algorithm'];
        $signature_storage_path = sprintf('%s/signature.jwkset', $bundle_config['key_storage_folder']);

        ConfigurationHelper::addRandomJWKSet($container, sprintf('%s_signature_keyset', $this->getAlias()), $signature_storage_path, 3, $signature_key_configuration, true, true);

        if (true === $bundle_config['encryption']['enabled']) {
            $encryption_key_configuration = $bundle_config['encryption']['encryption_key_configuration'];
            $encryption_key_configuration['use'] = 'enc';
            $encryption_key_configuration['alg'] = $bundle_config['encryption']['key_encryption_algorithm'];

            $encryption_storage_path = sprintf('%s/encryption.jwkset', $bundle_config['key_storage_folder']);

            ConfigurationHelper::addRandomJWKSet($container, sprintf('%s_encryption_keyset', $this->getAlias()), $encryption_storage_path, 3, $encryption_key_configuration, true, true);
        }
    }
}
