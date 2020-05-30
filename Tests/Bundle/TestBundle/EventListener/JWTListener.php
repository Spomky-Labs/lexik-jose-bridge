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

namespace SpomkyLabs\TestBundle\EventListener;

use function array_key_exists;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Symfony\Component\HttpFoundation\RequestStack;

final class JWTListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var JWTExpiredEvent[]
     */
    private $expired_token_events = [];

    /**
     * @var JWTInvalidEvent[]
     */
    private $invalid_token_events = [];

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getData();
        $payload['ip'] = $request->getClientIp();

        $event->setData($payload);
    }

    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getPayload();

        if (!array_key_exists('ip', $payload) || $payload['ip'] !== $request->getClientIp()) {
            $event->markAsInvalid();
        }
    }

    public function onJWTExpired(JWTExpiredEvent $event): void
    {
        $this->expired_token_events[] = $event;
    }

    /**
     * @return JWTExpiredEvent[]
     */
    public function getExpiredTokenEvents(): array
    {
        $result = $this->expired_token_events;
        $this->expired_token_events = [];

        return $result;
    }

    public function onJWTInvalid(JWTInvalidEvent $event): void
    {
        $this->invalid_token_events[] = $event;
    }

    /**
     * @return JWTInvalidEvent[]
     */
    public function getInvalidTokenEvents(): array
    {
        $result = $this->invalid_token_events;
        $this->invalid_token_events = [];

        return $result;
    }
}
