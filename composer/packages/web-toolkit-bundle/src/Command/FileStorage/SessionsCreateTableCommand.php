<?php

/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mep\WebToolkitBundle\Command\FileStorage;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
#[AsCommand(
    name: 'mwt:sessions:create-table',
    description: 'Creates the database table for sessions persistence',
)]
class SessionsCreateTableCommand extends Command
{
    public function __construct(
        private ?PdoSessionHandler $pdoSessionHandler = null,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        if ($this->pdoSessionHandler === null) {
            $symfonyStyle->error('The PDO session handler is not available as service, did you change the default configuration?');

            return Command::INVALID;
        }

        $this->pdoSessionHandler->createTable();
        $symfonyStyle->success('The sessions table has been created successfully!');

        return Command::SUCCESS;
    }
}
