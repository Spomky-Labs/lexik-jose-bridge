<?php

declare(strict_types=1);

namespace SpomkyLabs\TestBundle\Checker;

use const FILTER_VALIDATE_IP;
use function is_string;
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
    public function checkClaim(mixed $ip): void
    {
        if (! is_string($ip) || ! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidClaimException('The claim "ip" is not valid.', 'ip', $ip);
        }
    }
}
