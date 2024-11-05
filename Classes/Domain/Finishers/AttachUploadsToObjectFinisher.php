<?php
declare(strict_types=1);

namespace WapplerSystems\WsBulletinboard\Domain\Finishers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Exception;
use TYPO3\CMS\Form\Mvc\Property\TypeConverter\PseudoFileReference;

/**
 * Scope: frontend
 */
class AttachUploadsToObjectFinisher extends AbstractFinisher
{
    /**
     * Executes this finisher
     * @throws Exception
     * @see AbstractFinisher::execute()
     */
    protected function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();

        $elementsOptions = $this->parseOption('elements');

        $elements = $formRuntime->getFormDefinition()->getRenderablesRecursively();
        foreach ($elements as $element) {
            if (!$element instanceof FileUpload) {
                continue;
            }
            $files = $formRuntime[$element->getIdentifier()];
            if (!$files) {
                continue;
            }
            if (!isset($elementsOptions[$element->getIdentifier()])) {
                continue;
            }
            $elementOptions = $elementsOptions[$element->getIdentifier()];

            if (!isset($elementOptions['table'])) {
                throw new Exception('no table defined in AttachUploadsToObjectFinisher for element ' . $element->getIdentifier());
            }

            $databaseConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($elementOptions['table']);

            if (($elementOptions['lastInsertId'] ?? false) === true) {
                $uid = $databaseConnection->lastInsertId();
            } else {
                $uid = $elementOptions['uid'] ?? null;
            }

            if ($uid === null) {
                throw new Exception('uid in AttachUploadsToObjectFinisher for element ' . $element->getIdentifier() . ' is null!');
            }

            $mapOnDatabaseColumn = $elementOptions['mapOnDatabaseColumn'] ?? null;
            if ($mapOnDatabaseColumn === null) {
                throw new Exception('mapOnDatabaseColumn in AttachUploadsToObjectFinisher for element ' . $element->getIdentifier() . ' is null!');
            }

            $contentElement = BackendUtility::getRecord(
                $elementOptions['table'],
                $uid
            );

            if (!is_array($files)) {
              $files = [$files];
            }

            // cleanup file references beforehands for edit mode
            $existingFileReferences = $databaseConnection->executeQuery(
                'SELECT * FROM sys_file_reference WHERE uid_foreign = ' . $uid . ' AND tablenames = "' .
                $elementOptions['table'] . '" AND fieldname = "' . $mapOnDatabaseColumn . '"'
            )->fetchAllAssociative();

            // delete files not needed anymore
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

            foreach ($existingFileReferences as $existingFileReference) {
                $found = false;

                foreach ($files as $file) {
                    if ($file instanceof FileReference && $file->getUid() == $existingFileReference['uid']) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $file = $resourceFactory->getFileObject($existingFileReference['uid_local']);

                    if ($file instanceof File) {
                        $folder = $file->getParentFolder();
                        $file->delete();

                        try {
                            if ($folder->getFileCount([], true) === 0) {
                                $folder->delete();
                            }
                        } catch (InsufficientFolderAccessPermissionsException $e) {
                        }
                    }
                }
            }

            $databaseConnection->executeQuery(
                'DELETE FROM sys_file_reference WHERE uid_foreign = ' . $uid . ' AND tablenames = "' .
                $elementOptions['table'] . '" AND fieldname = "' . $mapOnDatabaseColumn . '"'
            );

            if (count(array_filter($files, function ($entry) {
                return !($entry instanceof FileReference);
              })) === 0) {

                $fakeAdmin = GeneralUtility::makeInstance(BackendUserAuthentication::class);

                $fakeAdmin->start($GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals());
                $fakeAdmin->user['uid'] = 0; // fake uid to avoid php warning in DataHandler
                $fakeAdmin->groupData['tables_modify'] = 'sys_file_reference,'.$elementOptions['table'];

                // flux workaround
                if (!is_object($GLOBALS['BE_USER'])) {
                    $GLOBALS['BE_USER'] = $fakeAdmin;
                    $GLOBALS['BE_USER']->workspace = 0;
                }

                $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');

                /** @var FileReference $file */
                foreach ($files as $file) {
                    $newId = 'NEW1234';
                    $data = [];
                    $data['sys_file_reference'][$newId] = [
                        'table_local' => 'sys_file',
                        'uid_local' => $file->getOriginalResource()->getProperty('uid_local'),
                        'tablenames' => $elementOptions['table'],
                        'uid_foreign' => $contentElement['uid'],
                        'fieldname' => $mapOnDatabaseColumn,
                        'pid' => $contentElement['pid'],
                        'sys_language_uid' => 0
                    ];
                    $data[$elementOptions['table']][$contentElement['uid']] = [
                        $mapOnDatabaseColumn => $newId
                    ];

                    /** @var DataHandler $dataHandler */
                    $dataHandler = GeneralUtility::makeInstance(DataHandler::class);

                    $doktype = $dataHandler->pageInfo($contentElement['pid'], 'doktype');
                    if (!isset($GLOBALS['PAGES_TYPES'][$doktype]['allowedTables'])) {
                        $GLOBALS['PAGES_TYPES'][$doktype]['allowedTables'] = '*';
                    }
                    $dataHandler->bypassAccessCheckForRecords = true;
                    $dataHandler->bypassWorkspaceRestrictions = true;
                    $dataHandler->start($data, [], $fakeAdmin);
                    $dataHandler->process_datamap();
                    if (count($dataHandler->errorLog) > 0) {
                        throw new Exception(implode(',', $dataHandler->errorLog));
                    }

                }

            }

        }

    }

}
