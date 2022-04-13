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

    public function checkHeader(mixed $algorithm): void
    {
        if (! is_string($algorithm)) {
            throw new InvalidHeaderException('The value of the header "alg" is not valid', 'alg', $algorithm);
        }

        if ($this->algorithm !== $algorithm) {
            throw new InvalidHeaderException(sprintf(
                'The algorithm "%s" is not known.',
                $algorithm
            ), 'alg', $algorithm);
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
