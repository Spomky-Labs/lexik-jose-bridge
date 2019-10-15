<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
final class ApiController extends AbstractController
{
    /**
     * @Route("/anonymous")
     */
    public function anonymousAction(): Response
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
    public function helloAction(): Response
    {
        $user = $this->getUser();
        $message = "Hello {$user->getUsername()}!";

        return new Response($message);
    }

    /**
     * @Route("/admin")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function adminAction(): Response
    {
        $user = $this->getUser();
        $message = "Hello {$user->getUsername()}!";

        return new Response($message);
    }
}
