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
 */
#[AsCommand(
    name: 'mwt:storage:garbage-collection',
    description: 'Removes unused attachments',
)]
class GarbageCollectionCommand extends Command
{
    /**
     * @param iterable<GarbageCollectorInterface> $garbageCollectors
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileStorageManager $fileStorageManager,
        private iterable $garbageCollectors,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Prints the unused attachments without removing them')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
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

        if (count($garbageAttachmentsLog) > 0) {
            $io->table(['UUID', 'Public URL', 'Context'], $garbageAttachmentsLog);
        } else {
            $io->info('No unused attachment found.');
        }

        if ($deletedAttachments = count($garbageAttachmentsLog)) {
            $io->success($deletedAttachments . ' unused attachments ' . ($dryRun ? 'found' : 'deleted') . '!');
        }

        return Command::SUCCESS;
    }
}
