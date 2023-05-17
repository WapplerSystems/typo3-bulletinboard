<?php
namespace WapplerSystems\WsBulletinboard\Hooks;


use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileReferenceHook
{

    public function processCmdmap_preProcess($command, $table, $id, $fieldArray, $dataHandler, $pasteUpdate)
    {

        if ($table === 'tx_wsbulletinboard_domain_model_entry' && $command === 'delete') {
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
            $queryBuilder->getRestrictions()->removeAll();
            $queryBuilder
                ->select('uid_local')
                ->from('sys_file_reference')
                ->where($queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter((int)$id, Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('tx_wsbulletinboard_domain_model_entry', Connection::PARAM_STR))
                ));
            $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $file = $resourceFactory->getFileObject($row['uid_local']);
                    if ($file instanceof File) {
                        $file->delete();
                    }
                }
            }

        }
    }
}
