<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
final class ApiController extends Controller
{
    /**
     * @Route("/anonymous")
     */
    public function anonymousAction()
    {
        $user = $this->getUser();
        if (null === $user) {
            $message = 'Hello anonymous!';
        } else {
            $message = "Hello {$user->getUsername()}!";
        }

        return new Response($message);
    }

    /**
     * @Route("/hello")
     * @Security("is_granted('ROLE_USER')")
     */
    public function helloAction()
    {
        $user = $this->getUser();
        $message = "Hello {$user->getUsername()}!";

        return new Response($message);
    }

    /**
     * @Route("/admin")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function adminAction()
    {
        $user = $this->getUser();
        $message = "Hello {$user->getUsername()}!";

        return new Response($message);
    }
}
