<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Checker;

use Jose\Component\Checker\HeaderChecker;
use Jose\Component\Checker\InvalidHeaderException;

/**
 * Class EncHeaderChecker.
 */
final class EncHeaderChecker implements HeaderChecker
{
    /**
     * @var string
     */
    private $algorithm;

    /**
     * AlgHeaderChecker constructor.
     *
     * @param string $algorithm
     */
    public function __construct(string $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function checkHeader($algorithm)
    {
        if (!is_string($algorithm)) {
            throw new InvalidHeaderException('The value of the header "enc" is not valid', 'enc', $algorithm);
        }

        if ($this->algorithm !== $algorithm) {
            throw new InvalidHeaderException(sprintf('The algorithm "%s" is not known.', $algorithm), 'enc', $algorithm);
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
