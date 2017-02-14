<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle;

use SpomkyLabs\LexikJoseBundle\DependencyInjection\Compiler\EncryptionSupportCompilerPass;
use SpomkyLabs\LexikJoseBundle\DependencyInjection\SpomkyLabsLexikJoseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SpomkyLabsLexikJoseBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new SpomkyLabsLexikJoseExtension('lexik_jose');
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $this->checkRequirements(['SpomkyLabsJoseBundle', 'LexikJWTAuthenticationBundle'], $container);
        $container->addCompilerPass(new EncryptionSupportCompilerPass());
    }

    /**
     * @param string[]         $requiredBundles
     * @param ContainerBuilder $container
     *
     * @throws \LogicException
     */
    private function checkRequirements(array $requiredBundles, ContainerBuilder $container)
    {
        $enabledBundles = $container->getParameter('kernel.bundles');

        foreach ($requiredBundles as $requiredBundle) {
            if (!array_key_exists($requiredBundle, $enabledBundles)) {
                throw new \LogicException(sprintf('In order to use bundle "%s" you also need to enable "%s"', $this->getName(), $requiredBundle));
            }
        }
    }
}
