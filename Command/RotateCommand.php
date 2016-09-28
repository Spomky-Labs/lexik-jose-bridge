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

use Jose\Object\JWKSetInterface;
use Jose\Object\RotatableInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RotateCommand extends ContainerAwareCommand
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $services = ['jose.key_set.lexik_jose_signature_keyset'];
        if ($this->getContainer()->has('jose.key_set.lexik_jose_encryption_keyset')) {
            $services[] = 'jose.key_set.lexik_jose_encryption_keyset';
        }

        foreach ($services as $service) {
            $result = $this->processKeySet($input, $output, $service);
            if (0 !== $result) {
                return $result;
            }
        }
        $output->writeln('Done.');

        return 0;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    private function processKeySet(InputInterface $input, OutputInterface $output, $service)
    {
        if (!$this->getContainer()->has($service)) {
            $output->writeln(sprintf('<error>The service "%s" does not exist.</error>', $service));

            return 1;
        }
        $service = $this->getContainer()->get($service);
        if (!$service instanceof JWKSetInterface) {
            $output->writeln(sprintf('<error>The service "%s" is not a key set.</error>', $service));

            return 2;
        }

        if (!$service instanceof RotatableInterface) {
            $output->writeln(sprintf('<error>The service "%s" is not a rotatable key set.</error>', $service));

            return 3;
        }
        $this->rotateIfNeeded($input, $service);

        return 0;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Jose\Object\RotatableInterface                   $service
     */
    private function rotateIfNeeded(InputInterface $input, RotatableInterface $service)
    {
        $ttl = $input->getArgument('ttl');
        $date = new \DateTime();
        $interval = \DateInterval::createFromDateString($ttl);
        $date = $date->sub($interval);
        $mtime = $service->getLastModificationTime();
        if (null === $mtime) {
            $service->regen();
        } elseif ($mtime <= $date->getTimestamp()) {
            $service->rotate();
        }
    }
}
