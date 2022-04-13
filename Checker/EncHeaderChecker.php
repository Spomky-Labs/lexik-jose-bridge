<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Checker;

use function is_string;
use Jose\Component\Checker\HeaderChecker;
use Jose\Component\Checker\InvalidHeaderException;

final class EncHeaderChecker implements HeaderChecker
{
    public function __construct(private readonly string $algorithm)
    {
    }

    public function checkHeader(mixed $value): void
    {
        if (! is_string($value)) {
            throw new InvalidHeaderException('The value of the header "enc" is not valid', 'enc', $value);
        }

        if ($this->algorithm !== $value) {
            throw new InvalidHeaderException(sprintf('The algorithm "%s" is not known.', $value), 'enc', $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportedHeader(): string
    {
        return 'enc';
    }

    /**
     * {@inheritdoc}
     */
    public function protectedHeaderOnly(): bool
    {
        return true;
    }
}
