<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Checker;

use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\HeaderChecker;
use function Safe\sprintf;

final class IssuerChecker implements ClaimChecker, HeaderChecker
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
     * @param mixed $issuer
     *
     * @return void
     */
    public function checkClaim($issuer): void
    {
        if ($this->issuer !== $issuer) {
            throw new \Exception(sprintf('The issuer "%s" is not allowed.', $issuer));
        }
    }

    /**
     * @param mixed $value
     *
     * @return void
     */
    public function checkHeader($value): void
    {
        $this->checkClaim($value);
    }

    /**
     * {@inheritdoc}
     */
    public function supportedHeader(): string
    {
        return 'iss';
    }

    /**
     * {@inheritdoc}
     */
    public function protectedHeaderOnly(): bool
    {
        return true;
    }
}
