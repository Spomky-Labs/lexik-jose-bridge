<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Session;
use function count;
use Exception;
use SpomkyLabs\TestBundle\EventListener\JWTListener;

trait RequestContext
{
    private ?RequestBuilder $request_builder = null;

    private ?Exception $exception = null;

    abstract public function getJWTListener(): JWTListener;

    abstract public function getToken(): ?string;

    /**
     * Returns Mink session.
     *
     * @param string|null $name name of the session OR active session will be used
     *
     * @return Session
     */
    abstract public function getSession($name = null);

    /**
     * @param string $uri
     *
     * @return string
     */
    abstract public function locatePath($uri);

    public function getException(): ?Exception
    {
        return $this->exception;
    }

    /**
     * @Given I add key :key with value :value in the header
     */
    public function iAddKeyWithValueInTheHeader($key, $value)
    {
        $this->getRequestBuilder()
            ->addHeader($key, $value)
        ;
    }

    /**
     * @Given I add key :key with value :value in the query parameter
     */
    public function iAddKeyWithValueInTheQueryParameter($key, $value)
    {
        $this->getRequestBuilder()
            ->addQueryParameter($key, $value)
        ;
    }

    /**
     * @Given I add user :user and password :secret in the authorization header
     */
    public function iAddUserAndPasswordInTheAuthorizationHeader($user, $secret)
    {
        $this->getRequestBuilder()
            ->addHeader('Authorization', 'Basic ' . base64_encode("{$user}:{$secret}"))
        ;
    }

    /**
     * @Given I add key :key with value :value in the body request
     */
    public function iAddKeyWithValueInTheBodyRequest($key, $value)
    {
        $this->getRequestBuilder()
            ->addRequestParameter($key, $value)
        ;
    }

    /**
     * @Given I add the token in the authorization header
     */
    public function iAddTheTokenInTheAuthorizationHeader()
    {
        $token = $this->getToken();
        if ($token === null) {
            throw new Exception('The token is not available. Are you logged in?');
        }

        $this->getRequestBuilder()
            ->addHeader('Authorization', 'Bearer ' . $token)
        ;
    }

    /**
     * @Given the request content type is :content_type
     */
    public function theContentTypeIs($content_type)
    {
        $this->getRequestBuilder()
            ->addServer('CONTENT_TYPE', $content_type)
        ;
    }

    /**
     * @Given the request is not secured
     */
    public function theRequestIsNotSecured()
    {
        $this->getRequestBuilder()
            ->addServer('HTTPS', 'off')
        ;
    }

    /**
     * @Given the request is secured
     */
    public function theRequestIsSecured()
    {
        $this->getRequestBuilder()
            ->addServer('HTTPS', 'on')
        ;
    }

    /**
     * @When I :method the request to :uri
     *
     * @param string $method
     */
    public function iTheRequestTo($method, $uri)
    {
        if (! $this->getSession()->getDriver() instanceof BrowserKitDriver) {
            throw new Exception('Unsupported driver.');
        }

        $client = $this->getSession()
            ->getDriver()
            ->getClient()
        ;
        $client->followRedirects(false);

        $this->getRequestBuilder()
            ->setUri($this->locatePath($uri))
        ;

        try {
            $client->request(
                $method,
                $this->getRequestBuilder()
                    ->getUri(),
                $this->getRequestBuilder()
                    ->getRequestParameters(),
                [],
                $this->getRequestBuilder()
                    ->getServer(),
                $this->getRequestBuilder()
                    ->getContent()
            );
        } catch (Exception $e) {
            $this->exception = $e;
        }
        $client->followRedirects(true);
    }

    /**
     * @Given I am on the page :url
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
     */
    public function iShouldReceiveAnException($message)
    {
        if (! $this->getException() instanceof Exception) {
            throw new Exception('No exception caught.');
        }
        if ($message !== $this->getException()->getMessage()) {
            throw new Exception(sprintf(
                'The exception has not the expected message: "%s". Message is "%s".',
                $message,
                $this->getException()->getMessage()
            ));
        }
    }

    /**
     * @Then the error listener should receive an expired token event
     */
    public function theErrorListenerShouldReceiveAnExpiredTokenEvent()
    {
        $events = $this->getJWTListener()
            ->getExpiredTokenEvents()
        ;
        if (count($events) !== 1) {
            throw new Exception('Expected 1 expired token event, got ' . count($events));
        }
    }

    /**
     * @Then the error listener should receive an invalid token event
     */
    public function theErrorListenerShouldReceiveAnInvalidTokenEvent()
    {
        $events = $this->getJWTListener()
            ->getInvalidTokenEvents()
        ;
        if (count($events) !== 1) {
            throw new Exception('Expected 1 invalid token event, got ' . count($events));
        }
    }

    /**
     * @Then the error listener should receive an invalid token event containing an exception with message :message
     */
    public function theErrorListenerShouldReceiveAnInvalidTokenEventContainingAnExceptionWithMessage($message)
    {
        $events = $this->getJWTListener()
            ->getInvalidTokenEvents()
        ;

        foreach ($events as $event) {
            $exception = current($events)
                ->getException()
            ;
            while ($exception !== null) {
                if ($exception->getMessage() === $message) {
                    return;
                }
                $exception = $exception->getPrevious();
            }
        }

        throw new Exception();
    }

    protected function getRequestBuilder(): RequestBuilder
    {
        if ($this->request_builder === null) {
            $this->request_builder = new RequestBuilder();
        }

        return $this->request_builder;
    }
}
