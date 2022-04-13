<?php

declare(strict_types=1);

namespace SpomkyLabs\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    /**
     * @Template
     */
    #[Route(path: '/login', name: 'login')]
    public function loginAction(AuthenticationUtils $authenticationUtils) : array
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return [
            'last_username' => $lastUsername,
            'error' => $error,
        ];
    }
}
