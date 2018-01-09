<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

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

        if (!array_key_exists('content-type', $header)) {
            throw new \Exception('The response header has no content-type.');
        }
        if (!in_array($content_type, $header['content-type'])) {
            throw new \Exception(sprintf('The response header content-type does not contain "%s".', $content_type));
        }
    }

    /**
     * @Then the response should contain a token
     */
    public function theResponseShouldContainAToken()
    {
        $content = json_decode($this->getSession()->getPage()->getContent(), true);

        if (!is_array($content)) {
            throw new \Exception('The response is not a JSON object.');
        }
        if (!array_key_exists('token', $content)) {
            throw new \Exception('The response does not contain a token.');
        }
    }
}
