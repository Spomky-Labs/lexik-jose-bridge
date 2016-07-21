<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Checker;

use Assert\Assertion;
use Jose\Checker\ClaimCheckerInterface;
use Jose\Object\JWTInterface;

class IssuerChecker implements ClaimCheckerInterface
{
    /**
     * @var string
     */
    private $issuer;

    /**
     * IssuerChecker constructor.
     *
     * @param string $issuer
     */
    public function __construct($issuer)
    {
        $this->issuer = $issuer;
    }

    /**
     * {@inheritdoc}
     */
    public function checkClaim(JWTInterface $jwt)
    {
        if (!$jwt->hasClaim('iss')) {
            return [];
        }

        $issuer = $jwt->getClaim('iss');
        Assertion::eq($this->issuer, $issuer, sprintf('The issuer "%s" is not allowed.', $issuer));

        return ['iss'];
    }
}
