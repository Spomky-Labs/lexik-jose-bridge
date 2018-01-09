<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\TestBundle\EventListener;

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

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param JWTCreatedEvent $event
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getData();
        $payload['ip'] = $request->getClientIp();

        $event->setData($payload);
    }

    /**
     * @param JWTDecodedEvent $event
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getPayload();

        if (!array_key_exists('ip', $payload) || $payload['ip'] !== $request->getClientIp()) {
            $event->markAsInvalid();
        }
    }

    /**
     * @param JWTExpiredEvent $event
     */
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        $this->expired_token_events[] = $event;
    }

    /**
     * @return JWTExpiredEvent[]
     */
    public function getExpiredTokenEvents()
    {
        $result = $this->expired_token_events;
        $this->expired_token_events = [];

        return $result;
    }

    /**
     * @param JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $this->invalid_token_events[] = $event;
    }

    /**
     * @return JWTInvalidEvent[]
     */
    public function getInvalidTokenEvents()
    {
        $result = $this->invalid_token_events;
        $this->invalid_token_events = [];

        return $result;
    }
}
