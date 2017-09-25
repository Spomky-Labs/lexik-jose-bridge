<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Checker;

use Jose\Component\Checker\ClaimCheckerInterface;

final class IssuerChecker implements ClaimCheckerInterface
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
    public function __construct(string $issuer)
    {
        $this->issuer = $issuer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportedClaim(): string
    {
        return 'iss';
    }

    /**
     * {@inheritdoc}
     */
    public function checkClaim($issuer)
    {
        if ($this->issuer !== $issuer) {
            throw new \Exception(sprintf('The issuer "%s" is not allowed.', $issuer));
        }
    }
}
