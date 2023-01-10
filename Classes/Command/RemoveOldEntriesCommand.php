<?php

namespace WapplerSystems\WsBulletinboard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\WsBulletinboard\Domain\Repository\EntryRepository;

class RemoveOldEntriesCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setHelp('Deletes entries older than X days')
            ->addArgument(
                'days',
                InputArgument::REQUIRED,
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $days = (int)$input->getArgument('days');

        if ($days < 2) {
            return Command::INVALID;
        }

        $limit = time() - ($days * 24 * 60 * 60);

        $entryRepository = GeneralUtility::makeInstance(EntryRepository::class);
        $entryRepository->removeOlderThan($limit);

        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroupByTag('pages', 'ws_bulletinboard');

        return Command::SUCCESS;
    }
}
