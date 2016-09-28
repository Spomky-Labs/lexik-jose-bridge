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
use Jose\Object\StorableInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('spomky-labs:lexik_jose:delete')
            ->setDescription('Delete the key sets.')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command will delete the key sets.

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
            $result = $this->processKeySet($output, $service);
            if (0 !== $result) {
                return $result;
            }
        }
        $output->writeln('Done.');

        return 0;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $service
     *
     * @return int
     */
    private function processKeySet(OutputInterface $output, $service)
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

        if (!$service instanceof StorableInterface) {
            $output->writeln(sprintf('<error>The service "%s" is not a storable key set.</error>', $service));

            return 3;
        }
        $service->delete();

        return 0;
    }
}
