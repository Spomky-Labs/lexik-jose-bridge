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

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use Behat\Mink\Driver\BrowserKitDriver;
use function count;
use Exception;
use SpomkyLabs\TestBundle\EventListener\JWTListener;

trait RequestContext
{
    private $request_builder;
    private $exception;

    abstract public function getJWTListener(): JWTListener;

    /**
     * @return null|string
     */
    abstract public function getToken();

    /**
     * Returns Mink session.
     *
     * @param null|string $name name of the session OR active session will be used
     *
     * @return \Behat\Mink\Session
     */
    abstract public function getSession($name = null);

    /**
     * @param string $uri
     *
     * @return string
     */
    abstract public function locatePath($uri);

    /**
     * @return null|Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @Given I add key :key with value :value in the header
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function iAddKeyWithValueInTheHeader($key, $value)
    {
        $this->getRequestBuilder()->addHeader($key, $value);
    }

    /**
     * @Given I add key :key with value :value in the query parameter
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function iAddKeyWithValueInTheQueryParameter($key, $value)
    {
        $this->getRequestBuilder()->addQueryParameter($key, $value);
    }

    /**
     * @Given I add user :user and password :secret in the authorization header
     *
     * @param mixed $user
     * @param mixed $secret
     */
    public function iAddUserAndPasswordInTheAuthorizationHeader($user, $secret)
    {
        $this->getRequestBuilder()->addHeader('Authorization', 'Basic '.base64_encode("{$user}:{$secret}"));
    }

    /**
     * @Given I add key :key with value :value in the body request
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function iAddKeyWithValueInTheBodyRequest($key, $value)
    {
        $this->getRequestBuilder()->addRequestParameter($key, $value);
    }

    /**
     * @Given I add the token in the authorization header
     */
    public function iAddTheTokenInTheAuthorizationHeader()
    {
        $token = $this->getToken();
        if (null === $token) {
            throw new Exception('The token is not available. Are you logged in?');
        }

        $this->getRequestBuilder()->addHeader('Authorization', 'Bearer '.$token);
    }

    /**
     * @Given the request content type is :content_type
     *
     * @param mixed $content_type
     */
    public function theContentTypeIs($content_type)
    {
        $this->getRequestBuilder()->addServer('CONTENT_TYPE', $content_type);
    }

    /**
     * @Given the request is not secured
     */
    public function theRequestIsNotSecured()
    {
        $this->getRequestBuilder()->addServer('HTTPS', 'off');
    }

    /**
     * @Given the request is secured
     */
    public function theRequestIsSecured()
    {
        $this->getRequestBuilder()->addServer('HTTPS', 'on');
    }

    /**
     * @When I :method the request to :uri
     *
     * @param string $method
     * @param mixed  $uri
     */
    public function iTheRequestTo($method, $uri)
    {
        if (!$this->getSession()->getDriver() instanceof BrowserKitDriver) {
            throw new Exception('Unsupported driver.');
        }

        $client = $this->getSession()->getDriver()->getClient();
        $client->followRedirects(false);

        $this->getRequestBuilder()->setUri($this->locatePath($uri));

        try {
            $client->request(
                $method,
                $this->getRequestBuilder()->getUri(),
                $this->getRequestBuilder()->getRequestParameters(),
                [],
                $this->getRequestBuilder()->getServer(),
                $this->getRequestBuilder()->getContent()
            );
        } catch (Exception $e) {
            $this->exception = $e;
        }
        $client->followRedirects(true);
    }

    /**
     * @Given I am on the page :url
     *
     * @param mixed $url
     */
    public function iAmOnThePage($url)
    {
        $this->iTheRequestTo('GET', $url);
    }

    /**
     * @Then I should not receive an exception
     */
    public function iShouldNotReceiveAnException()
    {
        if ($this->getException() instanceof Exception) {
            throw $this->getException();
        }
    }

    /**
     * @Then I should receive an exception :message
     *
     * @param mixed $message
     */
    public function iShouldReceiveAnException($message)
    {
        if (!$this->getException() instanceof Exception) {
            throw new Exception('No exception caught.');
        }
        if ($message !== $this->getException()->getMessage()) {
            throw new Exception(sprintf('The exception has not the expected message: "%s". Message is "%s".', $message, $this->getException()->getMessage()));
        }
    }

    /**
     * @Then the error listener should receive an expired token event
     */
    public function theErrorListenerShouldReceiveAnExpiredTokenEvent()
    {
        $events = $this->getJWTListener()->getExpiredTokenEvents();
        if (1 !== count($events)) {
            throw new Exception('Expected 1 expired token event, got '.count($events));
        }
    }

    /**
     * @Then the error listener should receive an invalid token event
     */
    public function theErrorListenerShouldReceiveAnInvalidTokenEvent()
    {
        $events = $this->getJWTListener()->getInvalidTokenEvents();
        if (1 !== count($events)) {
            throw new Exception('Expected 1 invalid token event, got '.count($events));
        }
    }

    /**
     * @Then the error listener should receive an invalid token event containing an exception with message :message
     *
     * @param mixed $message
     */
    public function theErrorListenerShouldReceiveAnInvalidTokenEventContainingAnExceptionWithMessage($message)
    {
        $events = $this->getJWTListener()->getInvalidTokenEvents();

        foreach ($events as $event) {
            $exception = current($events)->getException();
            do {
                if ($exception->getMessage() === $message) {
                    return;
                }
            } while ($exception = $exception->getPrevious());
        }

        throw new Exception();
    }

    /**
     * @return \SpomkyLabs\LexikJoseBundle\Features\Context\RequestBuilder
     */
    protected function getRequestBuilder()
    {
        if (null === $this->request_builder) {
            $this->request_builder = new RequestBuilder();
        }

        return $this->request_builder;
    }
}
