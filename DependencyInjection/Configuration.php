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

use RuntimeException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lexik_jose');
        $rootNode = $treeBuilder->getRootNode();
        if (!$rootNode instanceof ArrayNodeDefinition) {
            throw new RuntimeException('Invalid root node');
        }
        $rootNode
            ->validate()
            ->ifTrue(static function (array $config): bool {
                return !isset($config['key_set']) && !isset($config['key_set_remote']);
            })
            ->thenInvalid('You must either configure a "key_set" or a "key_set_remote".')
            ->end()
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('server_name')
            ->info('The name of the server. The recommended value is the server URL. This value will be used to check the issuer of the token.')
            ->isRequired()
            ->end()
            ->scalarNode('audience')
            ->info('The audience of the token. If not set `server_name` will be used.')
            ->end()
            ->integerNode('ttl')
            ->info('The lifetime of a token (in second). For security reasons, a value below 1 hour (3600 sec) is recommended.')
            ->min(0)
            ->defaultValue(1800)
            ->end()
            ->scalarNode('key_set')
            ->info('Private/Shared keys used by this server to validate signed tokens. Must be a JWKSet object.')
            ->end()
            ->arrayNode('key_set_remote')
            ->children()
            ->scalarNode('type')
            ->info('The type of the remote key set, either `jku` or `x5u`.')
            ->end()
            ->scalarNode('url')
            ->info('The URL from where the key set should be downloaded.')
            ->end()
            ->end()
            ->end()
            ->scalarNode('key_index')
            ->info('Index of the key in the key set used to sign the tokens. Could be an integer or the key ID.')
            ->isRequired()
            ->end()
            ->scalarNode('signature_algorithm')
            ->info('Signature algorithm used to sign the tokens.')
            ->isRequired()
            ->end()
            ->arrayNode('claim_checked')
            ->info('List of aliases to claim checkers.')
            ->useAttributeAsKey('name')
            ->prototype('scalar')->end()
            ->treatNullLike([])
            ->treatFalseLike([])
            ->end()
            ->arrayNode('mandatory_claims')
            ->info('List of claims that must be present.')
            ->useAttributeAsKey('name')
            ->prototype('scalar')->end()
            ->defaultValue([])
            ->treatNullLike([])
            ->treatFalseLike([])
            ->end()
            ->end()
        ;

        $this->addEncryptionSection($rootNode);

        return $treeBuilder;
    }

    private function addEncryptionSection(ArrayNodeDefinition $node): void
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('encryption')
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
            ->scalarNode('key_set')
            ->info('Private/ Shared keys used by this server to decrypt the tokens. Must be a JWKSet object.')
            ->isRequired()
            ->end()
            ->scalarNode('key_index')
            ->isRequired()
            ->info('Index of the key in the key set used to encrypt the tokens. Could be an integer or the key ID.')
            ->end()
            ->scalarNode('key_encryption_algorithm')
            ->isRequired()
            ->info('Key encryption algorithm used to encrypt the tokens.')
            ->end()
            ->scalarNode('content_encryption_algorithm')
            ->info('Content encryption algorithm used to encrypt the tokens.')
            ->isRequired()
            ->end()
            ->end()
            ->end()
            ->end()
        ;
    }
}
