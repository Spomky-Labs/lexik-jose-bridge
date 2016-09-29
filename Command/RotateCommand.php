<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace SpomkyLabs\LexikJoseBundle\Command;

use Jose\Object\RotatableInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RotateCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('spomky-labs:lexik_jose:rotate')
            ->setDescription('Rotate key sets')
            ->addArgument(
                'ttl',
                InputArgument::OPTIONAL,
                '',
                '7 days'
            )
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command will rotate the key sets.

  <info>php %command.full_name%</info>
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function executeCommand(InputInterface $input, OutputInterface $output, RotatableInterface $jwkset)
    {
        $ttl = $input->getArgument('ttl');
        $date = new \DateTime();
        $interval = \DateInterval::createFromDateString($ttl);
        $date = $date->sub($interval);
        $mtime = $jwkset->getLastModificationTime();
        if (null === $mtime) {
            $jwkset->regen();
        } elseif ($mtime <= $date->getTimestamp()) {
            $jwkset->rotate();
        }

        return 0;
    }
}
