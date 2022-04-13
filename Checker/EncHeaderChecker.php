<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Checker;

use function is_string;
use Jose\Component\Checker\HeaderChecker;
use Jose\Component\Checker\InvalidHeaderException;

final class EncHeaderChecker implements HeaderChecker
{
    /**
     * @var string
     */
    private $algorithm;

    public function __construct(string $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    public function checkHeader($algorithm): void
    {
        if (! is_string($algorithm)) {
            throw new InvalidHeaderException('The value of the header "enc" is not valid', 'enc', $algorithm);
        }

        if ($this->algorithm !== $algorithm) {
            throw new InvalidHeaderException(sprintf(
                'The algorithm "%s" is not known.',
                $algorithm
            ), 'enc', $algorithm);
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
