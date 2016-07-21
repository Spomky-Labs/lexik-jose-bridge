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
            ->children()
                ->scalarNode('server_name')->isRequired()->end()
                ->scalarNode('signature_key')->isRequired()->end()
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
                    ->validate()
                        ->ifTrue(self::verifyEncryptionOptions())
                        ->thenInvalid('The configuration options for encryption are invalid.')
                    ->end()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('encryption_key')->defaultNull()->end()
                        ->scalarNode('key_encryption_algorithm')->defaultNull()->end()
                        ->scalarNode('content_encryption_algorithm')->defaultNull()->end()
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

            return empty($value['encryption_key']) || empty($value['key_encryption_algorithm']) || empty($value['content_encryption_algorithm']);
        };
    }
}
