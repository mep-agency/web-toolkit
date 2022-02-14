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

use Mep\WebToolkitBundle\Config\CommandOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
#[AsCommand(
    name: self::NAME,
    description: self::DESCRIPTION,
)]
class SessionsCreateTableCommand extends Command
{
    /**
     * @var string
     */
    final public const NAME = 'mwt:sessions:create-table';

    /**
     * @var string
     */
    final public const DESCRIPTION = 'Creates the database table for sessions persistence';

    public function __construct(
        private readonly ?PdoSessionHandler $pdoSessionHandler = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                CommandOption::IGNORE_MISSING_PDO_SESSION_HANDLER,
                'i',
                InputOption::VALUE_NONE,
                "Doesn't fail if no PdoSessionHandler is available",
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $ignoreMissingPdoSessionHandler = $input->getOption(CommandOption::IGNORE_MISSING_PDO_SESSION_HANDLER);

        if (null === $this->pdoSessionHandler) {
            if ($ignoreMissingPdoSessionHandler) {
                $symfonyStyle->warning(
                    'The PDO session handler is not available as service, but you may be running dev or testing tasks...',
                );

                return Command::SUCCESS;
            }

            $symfonyStyle->error(
                'The PDO session handler is not available as service, did you change the default configuration?',
            );

            return Command::INVALID;
        }

        $this->pdoSessionHandler->createTable();
        $symfonyStyle->success('The sessions table has been created successfully!');

        return Command::SUCCESS;
    }
}
