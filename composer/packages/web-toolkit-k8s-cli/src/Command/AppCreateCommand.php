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

namespace Mep\MepWebToolkitK8sCli\Command;

use Mep\MepWebToolkitK8sCli\Config\Argument;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[AsCommand(
    name: 'app:create',
    description: 'Creates a configuration folder for a new app',
)]
class AppCreateCommand extends Command
{
    public function __construct(
        private string $cwdPath,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(Argument::APP_NAME, InputArgument::REQUIRED, 'The app name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();
        /** @var string $appName */
        $appName = $input->getArgument(Argument::APP_NAME);
        $targetDirectory = $this->cwdPath.'/apps/'.$appName;
        $domain = 'my-app.dev';
        $stagingDomain = 'staging.my-app.dev';
        $imageName = 'my-org/my-app';

        if ($filesystem->exists($targetDirectory)) {
            $symfonyStyle->error('The target directory for "'.$appName.'" already exists!');

            return Command::INVALID;
        }

        // Configuration wizard
        if ($input->isInteractive() && $symfonyStyle->confirm('Would you like to run the wizard?', false)) {
            $domain = $symfonyStyle->ask('Domain (without "www.")', $domain, function ($value) {
                if (null === $value) {
                    throw new RuntimeException('Domain name cannot be empty.');
                }

                return $value;
            });

            $stagingDomain = $symfonyStyle->ask('Staging domain (without "www.")', $stagingDomain, function ($value) {
                if (null === $value) {
                    throw new RuntimeException('Domain name cannot be empty.');
                }

                return $value;
            });

            $imageName = $symfonyStyle->ask('Container image name (without tag)', $imageName, function ($value) {
                if (null === $value) {
                    throw new RuntimeException('Domain name cannot be empty.');
                }

                return $value;
            });
        }

        // Replace placeholders
        $configFilesFinder = (new Finder())
            ->in(__DIR__.'/../../resources/templates/app')
            ->files()
            ->name(['*.yaml', '*.yml'])
        ;

        $placeholders = [
            '%app_name%' => $appName,
            '%app_domain%' => $domain,
            '%app_staging_domain%' => $stagingDomain,
            '%image_name%' => $imageName,
        ];

        foreach ($configFilesFinder->getIterator() as $configFile) {
            $filePath = $configFile->getRealPath();

            if (false === $filePath) {
                throw new RuntimeException('Unexpected value: the file path cannot be "false".');
            }

            $fileContent = file_get_contents($filePath);

            if (false === $fileContent) {
                throw new RuntimeException('Unexpected value: the file content cannot be "false".');
            }

            /**
             * @var string $value
             */
            foreach ($placeholders as $placeholder => $value) {
                $fileContent = str_replace($placeholder, $value, $fileContent);
            }

            $targetBasename = str_replace('app', $appName, $configFile->getBasename());

            $filesystem->dumpFile($targetDirectory.'/'.$targetBasename, $fileContent);
        }

        $symfonyStyle->success('New configuration files created successfully!');

        return Command::SUCCESS;
    }
}
