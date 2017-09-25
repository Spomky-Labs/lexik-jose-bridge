<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Checker;

use Jose\Component\Checker\ClaimCheckerInterface;

/**
 * Class AudienceChecker.
 */
final class AudienceChecker implements ClaimCheckerInterface
{
    /**
     * @var string
     */
    private $audience;

    /**
     * AudienceChecker constructor.
     *
     * @param string $audience
     */
    public function __construct(string $audience)
    {
        $this->audience = $audience;
    }

    /**
     * {@inheritdoc}
     */
    public function supportedClaim(): string
    {
        return 'aud';
    }

    /**
     * {@inheritdoc}
     */
    public function checkClaim($audience)
    {
        if (is_string($audience)) {
            if ($audience !== $this->audience) {
                throw new \Exception(sprintf('The audience "%s" is not known.', $audience));
            }
        } elseif (is_array($audience)) {
            if (!in_array($this->audience, $audience)) {
                throw new \Exception(sprintf('The audience "%s" is not known.', $audience));
            }
        } else {
            throw new \InvalidArgumentException('The claim "aud" has a bad format');
        }
    }
}
