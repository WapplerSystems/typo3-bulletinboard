<?php

namespace WapplerSystems\WsBulletinboard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WapplerSystems\WsBulletinboard\Domain\Repository\EntryRepository;

class RemoveEntryCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setHelp('UID')
            ->addArgument(
                'uid',
                InputArgument::REQUIRED,
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uid = (int)$input->getArgument('uid');

        $entryRepository = GeneralUtility::makeInstance(EntryRepository::class);

        $entry = $entryRepository->findByUid($uid);
        $entryRepository->remove($entry);

        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();

        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroupByTag('pages', 'ws_bulletinboard');

        return Command::SUCCESS;
    }
}
