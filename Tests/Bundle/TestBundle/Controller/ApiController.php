<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\TestBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/anonymous")
     * @Template()
     */
    public function anonymousAction()
    {
        return [
            'user' => $this->getUser(),
        ];
    }

    /**
     * @Route("/hello")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function helloAction()
    {
        return [
            'user' => $this->getUser(),
        ];
    }

    /**
     * @Route("/admin")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function adminAction()
    {
        return [
            'user' => $this->getUser(),
        ];
    }
}
