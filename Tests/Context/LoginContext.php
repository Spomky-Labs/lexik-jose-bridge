<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Features\Context;

/**
 * Behat context class.
 */
trait LoginContext
{
    /**
     * @var null|string
     */
    private $token = null;

    /**
     * @param string|null $name name of the session OR active session will be used
     *
     * @return \Behat\Mink\Session
     */
    abstract public function getSession($name = null);

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @Then I store the token
     */
    public function iStoreTheToken()
    {
        $content = json_decode($this->getSession()->getPage()->getContent(), true);

        $this->token = $content['token'];
    }
}
