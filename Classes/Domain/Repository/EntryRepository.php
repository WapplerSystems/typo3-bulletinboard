<?php

namespace WapplerSystems\WsBulletinboard\Domain\Repository;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_wsbulletinboard_domain_model_entry');
        $affectedRows = $queryBuilder
            ->delete('tx_wsbulletinboard_domain_model_entry')
            ->where(
                $queryBuilder->expr()->lte('tstamp', $queryBuilder->createNamedParameter($timestamp))
            )
            ->executeStatement();


    }

}
