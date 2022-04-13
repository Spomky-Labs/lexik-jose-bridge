<?php

declare(strict_types=1);

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use function array_key_exists;
use Behat\Mink\Session;
use Exception;
use function in_array;
use function is_array;

/**
 * Behat context class.
 */
trait ResponseContext
{
    /**
     * @param string|null $name name of the session OR active session will be used
     *
     * @return Session
     */
    abstract public function getSession($name = null);

    /**
     * @Then the response content-type should be :content_type
     */
    public function theResponseContentTypeShouldBe($content_type)
    {
        $header = $this->getSession()
            ->getResponseHeaders()
        ;

        if (! array_key_exists('content-type', $header)) {
            throw new Exception('The response header has no content-type.');
        }
        if (! in_array($content_type, $header['content-type'], true)) {
            throw new Exception(sprintf('The response header content-type does not contain "%s".', $content_type));
        }
    }

    /**
     * @Then the response should contain a token
     */
    public function theResponseShouldContainAToken()
    {
        $content = json_decode($this->getSession()->getPage()->getContent(), true);

        if (! is_array($content)) {
            throw new Exception('The response is not a JSON object.');
        }
        if (! array_key_exists('token', $content)) {
            throw new Exception('The response does not contain a token.');
        }
    }
}
