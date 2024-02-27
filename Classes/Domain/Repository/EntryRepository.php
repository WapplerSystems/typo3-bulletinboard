<?php

namespace WapplerSystems\WsBulletinboard\Domain\Repository;


use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use WapplerSystems\WsBulletinboard\Domain\Model\Entry;

/**
 *
 */
class EntryRepository extends Repository
{
    public function findSorted(array $settings)
    {
        $query = $this->createQuery();
        if ($settings['sorting'] === 'DESC') {
            $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        } else {
            $query->setOrderings(['crdate' => QueryInterface::ORDER_ASCENDING]);
        }
        if ((int)($settings['itemsLimit'] ?? 0) > 0) {
            $query->setLimit((int)($settings['itemsLimit']));
        }
        return $query->execute();
    }

    /**
     * @param string $actionKey
     * @return Entry|null
     */
    public function findOneByActionKey(string $actionKey) {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->matching($query->equals('action_key', $actionKey));
        return $query->execute()->getFirst();
    }

    public function removeOlderThan($timestamp) {

        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching($query->lessThan('tstamp', $timestamp));
        $entries = $query->execute()->toArray();
        foreach ($entries as $entry) {
            $this->remove($entry);
        }

    }


    /**
     * @param Entry $object
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function remove($object)
    {

        $images = $object->getImages();
        $folder = null;
        if ($images) {
            foreach ($images as $image) {
                /** @var FileReference $image */
                $file = $image->getOriginalResource()->getOriginalFile();
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

        parent::remove($object);
    }


}
