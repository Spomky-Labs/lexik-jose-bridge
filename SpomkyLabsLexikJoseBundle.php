<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle;

use SpomkyLabs\LexikJoseBundle\DependencyInjection\Compiler\EncryptionSupportCompilerPass;
use SpomkyLabs\LexikJoseBundle\DependencyInjection\SpomkyLabsLexikJoseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SpomkyLabsLexikJoseBundle extends Bundle
{
    public function getContainerExtension(): SpomkyLabsLexikJoseExtension
    {
        return new SpomkyLabsLexikJoseExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new EncryptionSupportCompilerPass());
    }
}
