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

use function is_string;
use Jose\Component\Checker\HeaderChecker;
use Jose\Component\Checker\InvalidHeaderException;
use function Safe\sprintf;

final class AlgHeaderChecker implements HeaderChecker
{
    /**
     * @var string
     */
    private $algorithm;

    public function __construct(string $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @param mixed $algorithm
     *
     * @throws InvalidHeaderException
     * @throws \Safe\Exceptions\StringsException
     */
    public function checkHeader($algorithm): void
    {
        if (!is_string($algorithm)) {
            throw new InvalidHeaderException('The value of the header "alg" is not valid', 'alg', $algorithm);
        }

        if ($this->algorithm !== $algorithm) {
            throw new InvalidHeaderException(sprintf('The algorithm "%s" is not known.', $algorithm), 'alg', $algorithm);
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
