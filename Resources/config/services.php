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

use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\IssuerChecker;
use SpomkyLabs\LexikJoseBundle\Checker\AlgHeaderChecker;
use SpomkyLabs\LexikJoseBundle\Encoder\LexikJoseEncoder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $container): void {
    $container = $container->services()->defaults()
        ->private()
        ->autoconfigure()
        ->autowire()
    ;

    $container->set(LexikJoseEncoder::class)
        ->args([
            ref('jose.jws_builder.lexik_jose'),
            ref('jose.jws_verifier.lexik_jose'),
            ref('jose.claim_checker.lexik_jose'),
            ref('jose.header_checker.lexik_jose_signature'),
            ref('jose.key_set.lexik_jose_bridge.signature'),
            '%lexik_jose_bridge.encoder.key_index%',
            '%lexik_jose_bridge.encoder.signature_algorithm%',
            '%lexik_jose_bridge.encoder.issuer%',
            '%lexik_jose_bridge.encoder.audience%',
            '%lexik_jose_bridge.encoder.ttl%',
            '%lexik_jose_bridge.encoder.mandatory_claims%',
        ])
    ;

    $container->set('spomkylabs_lexik_jose_checker_audience')
        ->class(AudienceChecker::class)
        ->args([
            '%lexik_jose_bridge.encoder.audience%',
        ])
        ->tag('jose.checker.claim', ['alias' => 'lexik_jose_audience'])
        ->tag('jose.checker.header', ['alias' => 'lexik_jose_audience'])
    ;

    $container->set('spomkylabs_lexik_jose_checker_issuer')
        ->class(IssuerChecker::class)
        ->args([
            ['%lexik_jose_bridge.encoder.issuer%'],
        ])
        ->tag('jose.checker.claim', ['alias' => 'lexik_jose_issuer'])
        ->tag('jose.checker.header', ['alias' => 'lexik_jose_issuer'])
    ;
    $container->set('spomkylabs_lexik_jose_checker_signature_algorithm')
        ->class(AlgHeaderChecker::class)
        ->args([
            '%lexik_jose_bridge.encoder.signature_algorithm%',
        ])
        ->tag('jose.checker.header', ['alias' => 'lexik_jose_signature_algorithm'])
    ;
};
