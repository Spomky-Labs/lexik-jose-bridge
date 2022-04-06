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

namespace SpomkyLabs\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api")
 */
final class ApiController extends AbstractController
{
    /**
     * @Route("/anonymous")
     */
    public function anonymousAction(TokenStorageInterface $tokenStorage): Response
    {
        $user = $tokenStorage->getToken()?->getUser();
        if (null === $user) {
            $message = 'Hello anonymous!';
        } else {
            $message = "Hello {$user->getUserIdentifier()}!";
        }

        return new Response($message);
    }

    /**
     * @Route("/hello")
     * @IsGranted("ROLE_USER")
     */
    public function helloAction(TokenStorageInterface $tokenStorage): Response
    {
        $user = $tokenStorage->getToken()?->getUser();
        $message = "Hello {$user->getUserIdentifier()}!";

        return new Response($message);
    }

    /**
     * @Route("/admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function adminAction(TokenStorageInterface $tokenStorage): Response
    {
        $user = $tokenStorage->getToken()?->getUser();
        $message = "Hello {$user->getUserIdentifier()}!";

        return new Response($message);
    }
}
