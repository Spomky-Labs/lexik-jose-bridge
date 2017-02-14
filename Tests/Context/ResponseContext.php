<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

use Assert\Assertion;

/**
 * Behat context class.
 */
trait ResponseContext
{
    /**
     * @param string|null $name name of the session OR active session will be used
     *
     * @return \Behat\Mink\Session
     */
    abstract public function getSession($name = null);

    /**
     * @Then the response content-type should be :content_type
     */
    public function theResponseContentTypeShouldBe($content_type)
    {
        $header = $this->getSession()->getResponseHeaders();

        Assertion::keyExists($header, 'content-type', 'The response header has no content-type.');
        Assertion::inArray($content_type, $header['content-type'], sprintf('The response header content-type does not contain "%s".', $content_type));
    }

    /**
     * @Then the response should contain a token
     */
    public function theResponseShouldContainAToken()
    {
        $content = json_decode($this->getSession()->getPage()->getContent(), true);

        Assertion::notNull($content, 'The response is not a JSON object.');
        Assertion::keyExists($content, 'token', 'The response does not contain a token.');
    }
}
