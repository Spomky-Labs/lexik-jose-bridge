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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lexik_jose');

        $rootNode
            ->addDefaultsIfNotSet()
            ->validate()->ifTrue(self::verifyDirectoryExistsAndIsWritable())->thenInvalid('The key storage folder does not exist or is not writable.')->end()
            ->children()
                ->scalarNode('server_name')->isRequired()->end()
                ->integerNode('ttl')->min(0)->defaultValue(3600)->end()
                ->scalarNode('key_storage_folder')->isRequired()->end()
                ->scalarNode('signature_algorithm')->defaultValue('RS512')->end()
                ->arrayNode('signature_key_configuration')->isRequired()->useAttributeAsKey('key')->prototype('variable')->end()->end()
            ->end();

        $this->addEncryptionSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node
     */
    private function addEncryptionSection(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('encryption')
                    ->addDefaultsIfNotSet()
                    ->validate()->ifTrue(self::verifyEncryptionOptions())->thenInvalid('The configuration options for encryption are invalid.')->end()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->arrayNode('encryption_key_configuration')->isRequired()->useAttributeAsKey('key')->prototype('variable')->end()->end()
                        ->scalarNode('key_encryption_algorithm')->defaultValue('RSA-OAEP-256')->end()
                        ->scalarNode('content_encryption_algorithm')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end();
    }

    private static function verifyDirectoryExistsAndIsWritable()
    {
        return function ($value) {
            return !(is_dir($value['key_storage_folder']) && is_writable($value['key_storage_folder']));
        };
    }

    private static function verifyEncryptionOptions()
    {
        return function ($value) {
            if (false === $value['enabled']) {
                return false;
            }

            return empty($value['key_encryption_algorithm']) || empty($value['content_encryption_algorithm']);
        };
    }
}
