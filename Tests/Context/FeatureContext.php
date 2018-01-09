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

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Behat context class.
 */
final class FeatureContext extends MinkContext implements SnippetAcceptingContext
{
    use KernelDictionary;
    use ResponseContext;
    use LoginContext;
    use RequestContext;
}
