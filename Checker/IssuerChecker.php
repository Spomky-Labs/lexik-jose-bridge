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

namespace SpomkyLabs\LexikJoseBundle\Checker;

use Exception;
use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\HeaderChecker;
use function Safe\sprintf;

final class IssuerChecker implements ClaimChecker, HeaderChecker
{
    /**
     * @var string
     */
    private $issuer;

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
     * @throws \Safe\Exceptions\StringsException
     */
    public function checkClaim($issuer): void
    {
        if ($this->issuer !== $issuer) {
            throw new Exception(sprintf('The issuer "%s" is not allowed.', $issuer));
        }
    }

    /**
     * @param mixed $value
     *
     * @throws \Safe\Exceptions\StringsException
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
