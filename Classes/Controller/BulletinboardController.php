<?php

namespace WapplerSystems\WsBulletinboard\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use WapplerSystems\WsBulletinboard\Domain\Model\Entry;
use WapplerSystems\WsBulletinboard\Domain\Repository\EntryRepository;


/**
 *
 */
class BulletinboardController extends AbstractController
{


    /**
     *
     * @param int $currentPage
     * @return ResponseInterface
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function listAction(int $currentPage = 1): ResponseInterface
    {

        $this->getTypoScriptFrontendController()->addCacheTags(['ws_bulletinboard']);

        $entryRepository = GeneralUtility::makeInstance(EntryRepository::class);
        $entries = $entryRepository->findSorted($this->settings);

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

        return $this->htmlResponse();
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction(): ResponseInterface
    {

        $configurationManager = GeneralUtility::makeInstance(FrontendConfigurationManager::class);
        $this->settings['frameworkConfiguration'] = $configurationManager->getConfiguration();
        $this->settings['pageUid'] = $this->getTypoScriptFrontendController()->id;

        $this->view->assignMultiple([
            'settings' => $this->settings,
        ]);

        return $this->htmlResponse();
    }


    public function doneAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * @param string $action_key
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     */
    public function declineAction(string $action_key): ResponseInterface
    {
        $entryRepository = GeneralUtility::makeInstance(EntryRepository::class);
        $entry = $entryRepository->findOneByActionKey($action_key);

        if ($entry === null) {
            return new ForwardResponse('entryNotFound');
        }

        $entryRepository->remove($entry);
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();

        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroupByTag('pages', 'ws_bulletinboard');

        return $this->htmlResponse();
    }


    /**
     * @param string $action_key
     * @throws StopActionException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws NoSuchCacheGroupException
     */
    public function confirmAction(string $action_key): ResponseInterface
    {
        $entryRepository = GeneralUtility::makeInstance(EntryRepository::class);
        $entry = $entryRepository->findOneByActionKey($action_key);

        if ($entry === null) {
            return new ForwardResponse('entryNotFound');
        }
        $entry->setHidden(0);
        $entryRepository->update($entry);
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();

        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroupByTag('pages', 'ws_bulletinboard');

        return $this->htmlResponse();
    }


    public function entryNotFoundAction(): ResponseInterface
    {

        return $this->htmlResponse();
    }


    /**
     * @param Entry $entry
     * @return ResponseInterface
     * @throws StopActionException
     * @throws NoSuchCacheGroupException
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     */
    public function deleteEntryAction(Entry $entry): ResponseInterface
    {

        $userAspect = GeneralUtility::makeInstance(Context::class)->getAspect('frontend.user');
        if (!$userAspect->isLoggedIn() || $entry->getFeUser()->getUid() !== $userAspect->get('id')) {

            $this->addFlashMessage(
                LocalizationUtility::translate('msg.notOwner', 'ws_bulletinboard'),
                LocalizationUtility::translate('title.error', 'ws_bulletinboard'),
                AbstractMessage::ERROR,
                true
            );

            $this->redirectToUri($this->uriBuilder->setTargetPageUid($this->getTypoScriptFrontendController()->id)->buildFrontendUri());
        }

        $entryRepository = GeneralUtility::makeInstance(EntryRepository::class);
        $entryRepository->remove($entry);

        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroupByTag('pages', 'ws_bulletinboard');

        $this->addFlashMessage(
            LocalizationUtility::translate('msg.successfulDeleted', 'ws_bulletinboard'),
            LocalizationUtility::translate('title.success', 'ws_bulletinboard'),
            AbstractMessage::OK,
            true
        );

        $this->redirectToUri($this->uriBuilder->setTargetPageUid($this->getTypoScriptFrontendController()->id)->buildFrontendUri());

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
