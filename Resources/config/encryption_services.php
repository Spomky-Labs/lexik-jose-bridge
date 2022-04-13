<?php

declare(strict_types=1);

use SpomkyLabs\LexikJoseBundle\Checker\AlgHeaderChecker;
use SpomkyLabs\LexikJoseBundle\Checker\EncHeaderChecker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container = $container->services()
        ->defaults()
        ->private()
        ->autoconfigure()
        ->autowire()
    ;

    $container->set('spomkylabs_lexik_jose_checker_key_encryption_algorithm')
        ->class(AlgHeaderChecker::class)
        ->args(['%lexik_jose_bridge.encoder.encryption.key_encryption_algorithm%'])
        ->tag('jose.checker.header', [
            'alias' => 'lexik_jose_key_encryption_algorithm',
        ])
    ;

    $container->set('spomkylabs_lexik_jose_checker_content_encryption_algorithm')
        ->class(EncHeaderChecker::class)
        ->args(['%lexik_jose_bridge.encoder.encryption.content_encryption_algorithm%'])
        ->tag('jose.checker.header', [
            'alias' => 'lexik_jose_content_encryption_algorithm',
        ])
    ;
};
