<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Checker;

use Exception;
use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\HeaderChecker;

final class IssuerChecker implements ClaimChecker, HeaderChecker
{
    public function __construct(private readonly string $issuer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supportedClaim(): string
    {
        return 'iss';
    }

    public function checkClaim(mixed $value): void
    {
        if ($this->issuer !== $value) {
            throw new Exception(sprintf('The issuer "%s" is not allowed.', $value));
        }
    }

    public function checkHeader(mixed $value): void
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
