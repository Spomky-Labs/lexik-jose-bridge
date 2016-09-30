<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Service;

use Jose\Object\RotatableInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final class Warmer implements CacheWarmerInterface
{
    /**
     * @var \Jose\Object\RotatableInterface
     */
    private $signature_jwkset;

    /**
     * @var \Jose\Object\RotatableInterface|null
     */
    private $encryption_jwkset = null;

    /**
     * Warmer constructor.
     *
     * @param \Jose\Object\RotatableInterface      $signature_jwkset
     * @param \Jose\Object\RotatableInterface|null $encryption_jwkset
     */
    public function __construct(RotatableInterface $signature_jwkset, RotatableInterface $encryption_jwkset = null)
    {
        $this->signature_jwkset = $signature_jwkset;
        $this->encryption_jwkset = $encryption_jwkset;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->createKeySetIfNeeded($this->signature_jwkset);
        if (null !== $this->encryption_jwkset) {
            $this->createKeySetIfNeeded($this->encryption_jwkset);
        }
    }

    /**
     * @param \Jose\Object\RotatableInterface $jwkset
     */
    private function createKeySetIfNeeded(RotatableInterface $jwkset)
    {
        if (null === $jwkset->getLastModificationTime()) {
            $jwkset->regen();
        }
    }
}
