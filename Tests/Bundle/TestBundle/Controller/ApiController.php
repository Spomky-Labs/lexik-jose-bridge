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
     * @Route("/foo")
     * @Security("is_granted('AUTHENTICATED_FULLY')")
     * @Template()
     */
    public function fooAction()
    {
        return [];
    }

    /**
     * @Route("/bar")
     * @Template()
     */
    public function barAction()
    {
        return [];
    }

    /**
     * @Route("/plic")
     * @Template()
     */
    public function plicAction()
    {
        return [];
    }

    /**
     * @Route("/ploc")
     * @Template()
     */
    public function plocAction()
    {
        return [];
    }
}
