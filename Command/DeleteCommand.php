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

use Jose\Object\StorableInterface;

class DeleteCommand extends AbstractCommand
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
    protected function executeCommand(RotatableInterface $jwkset)
    {
        $jwkset->delete();

        return 0;
    }
}
