<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Checker;

use function is_string;
use Jose\Component\Checker\HeaderChecker;
use Jose\Component\Checker\InvalidHeaderException;

final class AlgHeaderChecker implements HeaderChecker
{
    public function __construct(private readonly string $algorithm)
    {
    }

    public function checkHeader(mixed $value): void
    {
        if (! is_string($value)) {
            throw new InvalidHeaderException('The value of the header "alg" is not valid', 'alg', $value);
        }

        if ($this->algorithm !== $value) {
            throw new InvalidHeaderException(sprintf('The algorithm "%s" is not known.', $value), 'alg', $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportedHeader(): string
    {
        return 'alg';
    }

    /**
     * {@inheritdoc}
     */
    public function protectedHeaderOnly(): bool
    {
        return true;
    }
}
