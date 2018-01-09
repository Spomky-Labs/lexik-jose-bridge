<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\TestBundle\Checker;

use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\InvalidClaimException;

final class IpClaimChecker implements ClaimChecker
{
    /**
     * {@inheritdoc}
     */
    public function supportedClaim(): string
    {
        return 'ip';
    }

    /**
     * {@inheritdoc}
     */
    public function checkClaim($ip)
    {
        if (!is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidClaimException('The claim "ip" is not valid.', 'ip', $ip);
        }
    }
}
