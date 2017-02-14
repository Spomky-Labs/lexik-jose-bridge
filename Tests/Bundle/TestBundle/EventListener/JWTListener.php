<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
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

class JWTListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var \Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent[]
     */
    private $expired_token_events = [];

    /**
     * @var \Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent[]
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
     * @param \Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent $event
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getData();
        $payload['ip'] = $request->getClientIp();

        $event->setData($payload);
    }

    /**
     * @param \Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent $event
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
     * @param \Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent $event
     */
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        $this->expired_token_events[] = $event;
    }

    /**
     * @return \Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent[]
     */
    public function getExpiredTokenEvents()
    {
        $result = $this->expired_token_events;
        $this->expired_token_events = [];

        return $result;
    }

    /**
     * @param \Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $this->invalid_token_events[] = $event;
    }

    /**
     * @return \Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent[]
     */
    public function getInvalidTokenEvents()
    {
        $result = $this->invalid_token_events;
        $this->invalid_token_events = [];

        return $result;
    }
}
