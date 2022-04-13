<?php

declare(strict_types=1);

use FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle;
use Jose\Bundle\JoseFramework\JoseFrameworkBundle;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use SpomkyLabs\LexikJoseBundle\SpomkyLabsLexikJoseBundle;
use SpomkyLabs\TestBundle\SpomkyLabsTestBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles(): array
    {
        return [
            new SensioFrameworkExtraBundle(),
            new FrameworkBundle(),
            new MonologBundle(),
            new SecurityBundle(),
            new TwigBundle(),

            new LexikJWTAuthenticationBundle(),

            new JoseFrameworkBundle(),
            new SpomkyLabsTestBundle(),
            new SpomkyLabsLexikJoseBundle(),

            new FriendsOfBehatSymfonyExtensionBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/SpomkyLabsLexikBridgeTest/cache';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/SpomkyLabsLexikBridgeTest/logs';
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
