<?php

namespace WapplerSystems\WsBulletinboard\Controller;

use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use WapplerSystems\WsBulletinboard\Domain\Repository\EntryRepository;


/**
 *
 */
class BulletinboardController extends AbstractController
{

    /**
     *
     * @var EntryRepository
     */
    protected $entryRepository;


    /**
     * @param EntryRepository $entryRepository
     * @internal
     */
    public function injectEntryRepository(EntryRepository $entryRepository): void
    {
        $this->entryRepository = $entryRepository;
    }


    /**
     *
     * @param int $currentPage
     * @return void
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function listAction(int $currentPage = 1): void
    {
        $entries = $this->entryRepository->findSorted($this->settings);

        $assignedValues = [
            'settings' => $this->settings
        ];

        if ((int)($this->settings['hidePagination'] ?? 0) === 1) {
            $assignedValues['entries'] = $entries->toArray();
        } else {

            $paginator = new QueryResultPaginator($entries, $currentPage, (int)($this->settings['paginate']['itemsPerPage'] ?? 10));

            $pagination = new SimplePagination($paginator);
            $assignedValues = array_merge($assignedValues, [
                'paginator' => $paginator,
                'pagination' => $pagination,
                'entries' => $paginator->getPaginatedItems(),
            ]);
        }

        $assignedValues = $this->emitActionSignal(self::class, __FUNCTION__, $assignedValues);

        $this->view->assignMultiple($assignedValues);
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction(): void
    {

        $configurationManager = GeneralUtility::makeInstance(FrontendConfigurationManager::class);
        $this->settings['frameworkConfiguration'] = $configurationManager->getConfiguration();
        $this->settings['pageUid'] = $this->getTypoScriptFrontendController()->id;

        $this->view->assignMultiple([
            'settings' => $this->settings,
        ]);
    }


    public function doneAction(): void
    {

    }

    /**
     * @param string $action_key
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     */
    public function declineAction(string $action_key): void
    {
        $entry = $this->entryRepository->findOneByActionKey($action_key);

        if ($entry === null) {
            $this->forward('entryNotFound');
        } else {

            $this->entryRepository->remove($entry);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        }
    }


    /**
     * @param string $action_key
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function confirmAction(string $action_key): void
    {
        $entry = $this->entryRepository->findOneByActionKey($action_key);

        if ($entry === null) {
            $this->forward('entryNotFound');
        } else {
            $entry->setHidden(0);
            $this->entryRepository->update($entry);
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $persistenceManager->persistAll();
        }

    }


    public function entryNotFoundAction(): void
    {

    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }


    /**
     * Emits signal for various actions
     *
     * @param string $class the class name
     * @param string $signalName name of the signal slot
     * @param array $signalArguments arguments for the signal slot
     *
     * @return array
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function emitActionSignal(string $class, string $signalName, array $signalArguments): array
    {
        $signalArguments['extendedVariables'] = [];
        return $this->signalSlotDispatcher->dispatch($class, $signalName, $signalArguments);
    }

}
