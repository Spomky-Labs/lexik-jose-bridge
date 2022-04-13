<?php

declare(strict_types=1);

namespace SpomkyLabs\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route(path: '/api')]
final class ApiController extends AbstractController
{
    #[Route(path: '/anonymous')]
    public function anonymousAction(TokenStorageInterface $tokenStorage) : Response
    {
        $user = $tokenStorage->getToken()?->getUser();
        if ($user === null) {
            $message = 'Hello anonymous!';
        } else {
            $message = "Hello {$user->getUserIdentifier()}!";
        }
        return new Response($message);
    }
    /**
     * @IsGranted("ROLE_USER")
     */
    #[Route(path: '/hello')]
    public function helloAction(TokenStorageInterface $tokenStorage) : Response
    {
        $user = $tokenStorage->getToken()?->getUser();
        $message = "Hello {$user->getUserIdentifier()}!";
        return new Response($message);
    }
    /**
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route(path: '/admin')]
    public function adminAction(TokenStorageInterface $tokenStorage) : Response
    {
        $user = $tokenStorage->getToken()?->getUser();
        $message = "Hello {$user->getUserIdentifier()}!";
        return new Response($message);
    }
}
