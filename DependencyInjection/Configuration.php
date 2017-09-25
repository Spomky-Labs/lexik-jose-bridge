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

final class Configuration implements ConfigurationInterface
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
            ->children()
                ->scalarNode('server_name')->isRequired()->end()
                ->integerNode('ttl')->min(0)->defaultValue(3600)->end()
                ->scalarNode('key_set')->isRequired()->end()
                ->integerNode('key_index')->isRequired()->end()
                ->scalarNode('signature_algorithm')->isRequired()->end()
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
                        ->scalarNode('key_set')->end()
                        ->integerNode('key_index')->defaultNull()->end()
                        ->scalarNode('key_encryption_algorithm')->end()
                        ->scalarNode('content_encryption_algorithm')->end()
                    ->end()
                ->end()
            ->end();
    }

    private static function verifyEncryptionOptions()
    {
        return function ($value) {
            if (false === $value['enabled']) {
                return false;
            }

            return empty($value['key_encryption_algorithm'])
                || empty($value['content_encryption_algorithm'])
                || empty($value['key_set'])
                || null === $value['key_index']
            ;
        };
    }
}
