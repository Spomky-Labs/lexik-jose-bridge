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

namespace SpomkyLabs\LexikJoseBundle\DependencyInjection;

use function array_key_exists;
use Jose\Bundle\JoseFramework\Helper\ConfigurationHelper;
use SpomkyLabs\LexikJoseBundle\Encoder\LexikJoseEncoder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SpomkyLabsLexikJoseExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'lexik_jose';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['audience'])) {
            $config['audience'] = $config['server_name'];
        }

        $container->setParameter('lexik_jose_bridge.encoder.key_index', $config['key_index']);
        $container->setParameter('lexik_jose_bridge.encoder.signature_algorithm', $config['signature_algorithm']);
        $container->setParameter('lexik_jose_bridge.encoder.issuer', $config['server_name']);
        $container->setParameter('lexik_jose_bridge.encoder.audience', $config['audience']);
        $container->setParameter('lexik_jose_bridge.encoder.ttl', $config['ttl']);
        $container->setParameter('lexik_jose_bridge.encoder.claim_checked', $config['claim_checked']);
        $container->setParameter('lexik_jose_bridge.encoder.mandatory_claims', $config['mandatory_claims']);

        $container->setParameter('lexik_jose_bridge.encoder.encryption.enabled', $config['encryption']['enabled']);
        if (true === $config['encryption']['enabled']) {
            $container->setParameter('lexik_jose_bridge.encoder.encryption.key_index', $config['encryption']['key_index']);
            $container->setParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm', $config['encryption']['key_encryption_algorithm']);
            $container->setParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm', $config['encryption']['content_encryption_algorithm']);
            $this->loadEncryptionServices($container);
        }

        $this->loadServices($container);
    }

    public function loadServices(ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');
    }

    public function loadEncryptionServices(ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('encryption_services.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $isDebug = $container->getParameter('kernel.debug');
        $bridgeConfig = current($container->getExtensionConfig($this->getAlias()));
        if (!array_key_exists('claim_checked', $bridgeConfig)) {
            $bridgeConfig['claim_checked'] = [];
        }
        $claim_aliases = array_merge(
            $bridgeConfig['claim_checked'],
            ['exp', 'iat', 'lexik_jose_audience', 'lexik_jose_issuer']
        );
        ConfigurationHelper::addJWSBuilder($container, $this->getAlias(), [$bridgeConfig['signature_algorithm']], $isDebug);
        ConfigurationHelper::addJWSVerifier($container, $this->getAlias(), [$bridgeConfig['signature_algorithm']], $isDebug);
        ConfigurationHelper::addClaimChecker($container, $this->getAlias(), $claim_aliases, $isDebug);
        ConfigurationHelper::addHeaderChecker($container, $this->getAlias().'_signature', ['lexik_jose_signature_algorithm']);

        if (isset($bridgeConfig['key_set_remote'])) {
            ConfigurationHelper::addKeyset($container, 'lexik_jose_bridge.signature', $bridgeConfig['key_set_remote']['type'], ['url' => $bridgeConfig['key_set_remote']['url'], 'is_public' => $isDebug]);
        } elseif (isset($bridgeConfig['key_set'])) {
            ConfigurationHelper::addKeyset($container, 'lexik_jose_bridge.signature', 'jwkset', ['value' => $bridgeConfig['key_set'], 'is_public' => $isDebug]);
        }

        if (isset($bridgeConfig['encryption']['enabled']) && (true === $bridgeConfig['encryption']['enabled'])) {
            $this->enableEncryptionSupport($container, $bridgeConfig, $isDebug);
        }

        $lexikConfig = ['encoder' => ['service' => LexikJoseEncoder::class]];
        $container->prependExtensionConfig('lexik_jwt_authentication', $lexikConfig);
    }

    private function enableEncryptionSupport(ContainerBuilder $container, array $bridgeConfig, bool $isDebug): void
    {
        ConfigurationHelper::addJWEBuilder($container, $this->getAlias(), [$bridgeConfig['encryption']['key_encryption_algorithm']], [$bridgeConfig['encryption']['content_encryption_algorithm']], ['DEF'], $isDebug);
        ConfigurationHelper::addJWEDecrypter($container, $this->getAlias(), [$bridgeConfig['encryption']['key_encryption_algorithm']], [$bridgeConfig['encryption']['content_encryption_algorithm']], ['DEF'], $isDebug);
        ConfigurationHelper::addHeaderChecker($container, $this->getAlias().'_encryption', ['lexik_jose_audience', 'lexik_jose_issuer', 'lexik_jose_key_encryption_algorithm', 'lexik_jose_content_encryption_algorithm']);
        ConfigurationHelper::addKeyset($container, 'lexik_jose_bridge.encryption', 'jwkset', ['value' => $bridgeConfig['encryption']['key_set'], 'is_public' => $isDebug]);
    }
}
