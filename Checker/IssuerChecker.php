<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Checker;

use Exception;
use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\HeaderChecker;

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

    public function checkClaim($issuer): void
    {
        if ($this->issuer !== $issuer) {
            throw new Exception(sprintf('The issuer "%s" is not allowed.', $issuer));
        }
    }

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
