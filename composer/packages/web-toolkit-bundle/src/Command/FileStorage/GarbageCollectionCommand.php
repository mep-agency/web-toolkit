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

use Doctrine\ORM\EntityManagerInterface;
use Mep\WebToolkitBundle\Config\CommandOption;
use Mep\WebToolkitBundle\Contract\FileStorage\GarbageCollectorInterface;
use Mep\WebToolkitBundle\FileStorage\FileStorageManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[AsCommand(name: self::NAME, description: self::DESCRIPTION)]
class GarbageCollectionCommand extends Command
{
    /**
     * @var string
     */
    final public const NAME = 'mwt:storage:garbage-collection';

    /**
     * @var string
     */
    final public const DESCRIPTION = 'Removes unused attachments';

    /**
     * @param iterable<GarbageCollectorInterface> $garbageCollectors
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileStorageManager $fileStorageManager,
        private readonly iterable $garbageCollectors,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                CommandOption::DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Prints the unused attachments without removing them',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @var bool $dryRun */
        $dryRun = $input->getOption(CommandOption::DRY_RUN);
        $garbageAttachmentsLog = [];

        foreach ($this->garbageCollectors as $garbageCollector) {
            $garbageAttachments = $garbageCollector->collect($this->entityManager, $dryRun);

            foreach ($garbageAttachments as $garbageAttachment) {
                if (! $dryRun) {
                    $this->entityManager->remove($garbageAttachment);
                }

                $garbageAttachmentsLog[] = [
                    $garbageAttachment->getId(),
                    $this->fileStorageManager->getPublicUrl($garbageAttachment),
                    $garbageAttachment->getContext(),
                ];
            }
        }

        if (! $dryRun) {
            $this->entityManager->flush();
        }

        if ([] !== $garbageAttachmentsLog) {
            $symfonyStyle->table(['UUID', 'Public URL', 'Context'], $garbageAttachmentsLog);
        } else {
            $symfonyStyle->info('No unused attachment found.');
        }

        $deletedAttachments = count($garbageAttachmentsLog);

        if ($deletedAttachments > 0) {
            $symfonyStyle->success($deletedAttachments.' unused attachments '.($dryRun ? 'found' : 'deleted').'!');
        }

        return Command::SUCCESS;
    }
}
