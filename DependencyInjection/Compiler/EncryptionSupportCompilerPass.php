<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\DependencyInjection\Compiler;

use SpomkyLabs\LexikJoseBundle\Encoder\LexikJoseEncoder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class EncryptionSupportCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(LexikJoseEncoder::class) === false || $container->getParameter(
            'lexik_jose_bridge.encoder.encryption.enabled'
        ) === false) {
            return;
        }

        $definition = $container->getDefinition(LexikJoseEncoder::class);

        $definition->addMethodCall('enableEncryptionSupport', [
            new Reference('jose.jwe_builder.lexik_jose'),
            new Reference('jose.jwe_decrypter.lexik_jose'),
            new Reference('jose.header_checker.lexik_jose_encryption'),
            new Reference('jose.key_set.lexik_jose_bridge.encryption'),
            $container->getParameter('lexik_jose_bridge.encoder.encryption.key_index'),
            $container->getParameter('lexik_jose_bridge.encoder.encryption.key_encryption_algorithm'),
            $container->getParameter('lexik_jose_bridge.encoder.encryption.content_encryption_algorithm'),
        ]);
    }
}
